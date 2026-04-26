<?php

namespace App\Http\Controllers;

use App\Models\People;
use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private function adminUser(): User
    {
        abort_unless(Auth::check(), 403);

        $user = Auth::user();

        abort_unless($user->role === 'admin', 403);

        return $user;
    }

    private function adminPersonId(): int
    {
        return (int) $this->adminUser()->person_id;
    }

    private function dashboardStats(): array
    {
        return [
            'total_users' => DB::table('users')->count(),
            'total_admins' => DB::table('users')->where('role', 'admin')->count(),
            'total_officials' => DB::table('users')->where('role', 'official')->count(),
            'total_volunteers' => DB::table('volunteers')->count(),
            'total_disasters' => DB::table('disasters')->count(),
            'total_resources' => (int) DB::table('resources')->sum('quantity'),
            'total_donations' => (float) DB::table('fundraising')->sum('amount'),
            'total_affected_people' => DB::table('beneficiaries')->count(),
        ];
    }

    private function locationLabel(?object $location): string
    {
        if (!$location) {
            return 'Unknown location';
        }

        return trim(($location->city ?? '') . ', ' . ($location->district ?? ''));
    }

    private function adminLayoutData(string $activePage): array
    {
        return [
            'activePage' => $activePage,
            'stats' => $this->dashboardStats(),
        ];
    }

    private function dashboardWeatherQuery(): string
    {
        $location = DB::table('disasters as d')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->orderByDesc('d.created_at')
            ->select('l.city', 'l.district', 'l.country')
            ->first();

        return trim(collect([
            $location->city ?? null,
            $location->district ?? null,
            $location->country ?? null,
        ])->filter()->implode(', '));
    }

    private function dashboardWeather(): ?array
    {
        $weatherService = app(WeatherService::class);

        return $weatherService->fetchByQuery($this->dashboardWeatherQuery());
    }

    public function dashboard()
    {
        $users = DB::table('users as u')
            ->join('people as p', 'u.person_id', '=', 'p.id')
            ->orderByDesc('u.created_at')
            ->select('u.id as user_id', 'p.name', 'p.email', 'u.role', 'u.created_at')
            ->limit(5)
            ->get();

        $disasters = DB::table('disasters as d')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->orderByDesc('d.created_at')
            ->select('d.id', 'd.type', 'd.status', 'd.disaster_date', 'l.city', 'l.district')
            ->limit(5)
            ->get();

        $resources = DB::table('resources')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $alerts = DB::table('alerts')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $policies = DB::table('policies')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('admin.dashboard', array_merge($this->adminLayoutData('dashboard'), [
            'recentUsers' => $users,
            'recentDisasters' => $disasters,
            'recentResources' => $resources,
            'alerts' => $alerts,
            'policies' => $policies,
            'weather' => $this->dashboardWeather(),
            'weatherQuery' => $this->dashboardWeatherQuery(),
        ]));
    }

    public function weather(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
        ]);

        $query = trim($validated['query']);
        $weather = app(WeatherService::class)->fetchByQuery($query);

        return response()->json([
            'query' => $query,
            'weather' => $weather,
            'message' => $weather ? 'Weather loaded successfully.' : 'Weather data is unavailable for that location.',
        ]);
    }

    public function users()
    {
        $users = DB::table('users as u')
            ->join('people as p', 'u.person_id', '=', 'p.id')
            ->orderByDesc('u.created_at')
            ->select('u.id as user_id', 'p.id as person_id', 'p.name', 'p.email', 'p.phone', 'u.role', 'u.created_at')
            ->get();

        return view('admin.users', array_merge($this->adminLayoutData('users'), [
            'users' => $users,
        ]));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:people,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['admin', 'official', 'volunteer'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($validated) {
            DB::table('people')->insert([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'created_at' => now(),
            ]);

            $personId = (int) DB::table('people')->where('email', $validated['email'])->value('id');

            DB::table('users')->insert([
                'person_id' => $personId,
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
                'created_at' => now(),
            ]);

            if ($validated['role'] === 'volunteer') {
                DB::table('volunteers')->updateOrInsert(
                    ['person_id' => $personId],
                    [
                        'skills' => null,
                        'availability' => 'available',
                        'created_at' => now(),
                    ]
                );
            }
        });

        return redirect()->route('admin.users')->with('status', 'User added successfully.');
    }

    public function updateUser(Request $request, int $userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            abort(404);
        }

        $person = People::query()->findOrFail($user->person_id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('people', 'email')->ignore($person->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(['admin', 'official', 'volunteer'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($user, $person, $validated) {
            DB::table('people')
                ->where('id', $person->id)
                ->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ]);

            $updatePayload = [
                'role' => $validated['role'],
            ];

            if (!empty($validated['password'])) {
                $updatePayload['password'] = Hash::make($validated['password']);
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update($updatePayload);

            if ($validated['role'] === 'volunteer') {
                DB::table('volunteers')->updateOrInsert(
                    ['person_id' => $person->id],
                    [
                        'skills' => DB::table('volunteers')->where('person_id', $person->id)->value('skills'),
                        'availability' => DB::table('volunteers')->where('person_id', $person->id)->value('availability') ?? 'available',
                        'created_at' => now(),
                    ]
                );
            }
        });

        return redirect()->route('admin.users')->with('status', 'User updated successfully.');
    }

    public function destroyUser(int $userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            abort(404);
        }

        DB::transaction(function () use ($user) {
            DB::table('volunteer_assignments')->where('person_id', $user->person_id)->delete();
            DB::table('volunteers')->where('person_id', $user->person_id)->delete();
            DB::table('fundraising')->where('person_id', $user->person_id)->delete();
            DB::table('beneficiary_aid')
                ->whereIn('beneficiary_id', DB::table('beneficiaries')->where('person_id', $user->person_id)->pluck('id'))
                ->delete();
            DB::table('beneficiaries')->where('person_id', $user->person_id)->delete();
            DB::table('aid_requests')->where('person_id', $user->person_id)->delete();
            DB::table('sos_requests')->where('person_id', $user->person_id)->delete();
            DB::table('users')->where('id', $user->id)->delete();
            DB::table('people')->where('id', $user->person_id)->delete();
        });

        return redirect()->route('admin.users')->with('status', 'User deleted successfully.');
    }

    public function disasters()
    {
        $disasters = DB::table('disasters as d')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->orderByDesc('d.created_at')
            ->select('d.id', 'd.type', 'd.location_id', 'd.disaster_date', 'd.affected_population', 'd.status', 'l.city', 'l.district', 'l.country')
            ->get();

        $locations = DB::table('locations')->orderBy('city')->get();

        return view('admin.disasters', array_merge($this->adminLayoutData('disasters'), [
            'disasters' => $disasters,
            'locations' => $locations,
        ]));
    }

    public function storeDisaster(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'disaster_date' => ['required', 'date'],
            'affected_population' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'resolved'])],
        ]);

        DB::transaction(function () use ($validated) {
            $locationId = DB::table('locations')->insertGetId([
                'city' => $validated['city'],
                'district' => $validated['district'],
                'country' => $validated['country'] ?? 'Bangladesh',
            ]);

            DB::table('disasters')->insert([
                'type' => $validated['type'],
                'location_id' => $locationId,
                'disaster_date' => $validated['disaster_date'],
                'affected_population' => $validated['affected_population'],
                'status' => $validated['status'],
                'created_at' => now(),
            ]);

            DB::table('alerts')->insert([
                'title' => 'New Disaster Reported',
                'message' => 'A new ' . $validated['type'] . ' disaster has been reported in ' . $validated['city'] . ', ' . $validated['district'] . '. Please review the volunteer dashboard for updates.',
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.disasters')->with('status', 'Disaster added and volunteer alert published successfully.');
    }

    public function updateDisaster(Request $request, int $disasterId)
    {
        $disaster = DB::table('disasters')->where('id', $disasterId)->first();
        if (!$disaster) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'disaster_date' => ['required', 'date'],
            'affected_population' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'resolved'])],
        ]);

        DB::transaction(function () use ($disaster, $validated) {
            DB::table('locations')
                ->where('id', $disaster->location_id)
                ->update([
                    'city' => $validated['city'],
                    'district' => $validated['district'],
                    'country' => $validated['country'] ?? 'Bangladesh',
                ]);

            DB::table('disasters')
                ->where('id', $disaster->id)
                ->update([
                    'type' => $validated['type'],
                    'disaster_date' => $validated['disaster_date'],
                    'affected_population' => $validated['affected_population'],
                    'status' => $validated['status'],
                ]);
        });

        return redirect()->route('admin.disasters')->with('status', 'Disaster updated successfully.');
    }

    public function destroyDisaster(int $disasterId)
    {
        $disaster = DB::table('disasters')->where('id', $disasterId)->first();
        if (!$disaster) {
            abort(404);
        }

        DB::transaction(function () use ($disaster) {
            DB::table('incidents')->where('disaster_id', $disaster->id)->delete();
            DB::table('volunteer_assignments')->where('disaster_id', $disaster->id)->delete();
            DB::table('fundraising')->where('disaster_id', $disaster->id)->delete();
            DB::table('beneficiary_aid')
                ->whereIn('beneficiary_id', DB::table('beneficiaries')->where('disaster_id', $disaster->id)->pluck('id'))
                ->delete();
            DB::table('beneficiaries')->where('disaster_id', $disaster->id)->delete();
            DB::table('aid_requests')->where('location_id', $disaster->location_id)->delete();
            DB::table('sos_requests')->where('location_id', $disaster->location_id)->delete();
            DB::table('disasters')->where('id', $disaster->id)->delete();
            DB::table('locations')->where('id', $disaster->location_id)->delete();
        });

        return redirect()->route('admin.disasters')->with('status', 'Disaster deleted successfully.');
    }

    public function volunteers()
    {
        $volunteers = DB::table('volunteers as v')
            ->join('people as p', 'v.person_id', '=', 'p.id')
            ->leftJoin('volunteer_assignments as va', 'v.person_id', '=', 'va.person_id')
            ->select(
                'v.id as volunteer_id',
                'v.person_id',
                'p.name',
                'p.email',
                'p.phone',
                'v.skills',
                'v.availability',
                DB::raw('COUNT(va.id) as assignment_count')
            )
            ->groupBy('v.id', 'v.person_id', 'p.name', 'p.email', 'p.phone', 'v.skills', 'v.availability')
            ->orderBy('p.name')
            ->get();

        $disasters = DB::table('disasters')->orderByDesc('created_at')->get();

        $assignments = DB::table('volunteer_assignments as va')
            ->join('people as p', 'va.person_id', '=', 'p.id')
            ->join('disasters as d', 'va.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->orderByDesc('va.assigned_date')
            ->select('va.id as assignment_id', 'p.name as volunteer_name', 'd.type as disaster_name', 'l.city', 'l.district', 'va.hours_worked', 'va.assigned_date')
            ->get();

        return view('admin.volunteers', array_merge($this->adminLayoutData('volunteers'), [
            'volunteers' => $volunteers,
            'disasters' => $disasters,
            'assignments' => $assignments,
        ]));
    }

    public function updateVolunteer(Request $request, int $volunteerId)
    {
        $volunteer = DB::table('volunteers')->where('id', $volunteerId)->first();
        if (!$volunteer) {
            abort(404);
        }

        $validated = $request->validate([
            'skills' => ['nullable', 'string', 'max:150'],
            'availability' => ['required', Rule::in(['available', 'busy', 'on_call', 'offline'])],
        ]);

        DB::table('volunteers')
            ->where('id', $volunteerId)
            ->update([
                'skills' => $validated['skills'],
                'availability' => $validated['availability'],
            ]);

        return redirect()->route('admin.volunteers')->with('status', 'Volunteer updated successfully.');
    }

    public function storeVolunteerAssignment(Request $request)
    {
        $validated = $request->validate([
            'person_id' => ['required', 'exists:volunteers,person_id'],
            'disaster_id' => ['required', 'exists:disasters,id'],
            'hours_worked' => ['nullable', 'integer', 'min:0'],
            'assigned_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated) {
            DB::table('volunteer_assignments')->updateOrInsert(
                [
                    'person_id' => $validated['person_id'],
                    'disaster_id' => $validated['disaster_id'],
                ],
                [
                    'hours_worked' => $validated['hours_worked'] ?? 0,
                    'assigned_date' => $validated['assigned_date'],
                ]
            );

            // Fetch volunteer and disaster info for notification
            $volunteer = DB::table('volunteers')->where('person_id', $validated['person_id'])->first();
            $person = DB::table('people')->where('id', $validated['person_id'])->first();
            $disaster = DB::table('disasters')->where('id', $validated['disaster_id'])->first();

            if ($person && $disaster) {
                // Create notification alert for the volunteer
                DB::table('alerts')->insert([
                    'title' => 'New Task Assignment',
                    'message' => $person->name . ' has been assigned to ' . $disaster->type . ' disaster. Please check your tasks page for details.',
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.volunteers')->with('status', 'Volunteer assigned to disaster and notification created.');
    }

    public function destroyVolunteerAssignment(int $assignmentId)
    {
        DB::table('volunteer_assignments')->where('id', $assignmentId)->delete();

        return redirect()->route('admin.volunteers')->with('status', 'Volunteer assignment removed.');
    }

    public function resources()
    {
        $resources = DB::table('resources')->orderByDesc('created_at')->get();

        return view('admin.resources', array_merge($this->adminLayoutData('resources'), [
            'resources' => $resources,
        ]));
    }

    public function storeResource(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        DB::table('resources')->insert([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'quantity' => $validated['quantity'],
            'expiry_date' => $validated['expiry_date'] ?? null,
            'created_at' => now(),
        ]);

        return redirect()->route('admin.resources')->with('status', 'Resource added successfully.');
    }

    public function updateResource(Request $request, int $resourceId)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        DB::table('resources')
            ->where('id', $resourceId)
            ->update([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'expiry_date' => $validated['expiry_date'] ?? null,
            ]);

        return redirect()->route('admin.resources')->with('status', 'Resource updated successfully.');
    }

    public function destroyResource(int $resourceId)
    {
        DB::table('resources')->where('id', $resourceId)->delete();

        return redirect()->route('admin.resources')->with('status', 'Resource deleted successfully.');
    }

    public function affectedPeople()
    {
        $affectedPeople = DB::table('beneficiaries as b')
            ->join('people as p', 'b.person_id', '=', 'p.id')
            ->leftJoin('locations as l', 'b.location_id', '=', 'l.id')
            ->leftJoin('disasters as d', 'b.disaster_id', '=', 'd.id')
            ->orderByDesc('b.created_at')
            ->select('b.id as beneficiary_id', 'p.id as person_id', 'p.name', 'l.city', 'l.district', 'd.type as disaster_name', 'b.family_size', 'b.aid_received', 'b.created_at')
            ->get();

        $disasters = DB::table('disasters')->orderByDesc('created_at')->get();
        $locations = DB::table('locations')->orderBy('city')->get();

        return view('admin.affected', array_merge($this->adminLayoutData('affected-people'), [
            'affectedPeople' => $affectedPeople,
            'disasters' => $disasters,
            'locations' => $locations,
        ]));
    }

    public function storeAffectedPerson(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_id' => ['required', 'exists:locations,id'],
            'disaster_id' => ['required', 'exists:disasters,id'],
            'family_size' => ['nullable', 'integer', 'min:1'],
            'aid_received' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $personId = DB::table('people')->insertGetId([
                'name' => $validated['name'],
                'email' => null,
                'phone' => null,
                'created_at' => now(),
            ]);

            DB::table('beneficiaries')->insert([
                'person_id' => $personId,
                'family_size' => $validated['family_size'] ?? 1,
                'location_id' => $validated['location_id'],
                'disaster_id' => $validated['disaster_id'],
                'aid_received' => $validated['aid_received'] ?? null,
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.affected-people')->with('status', 'Affected person added successfully.');
    }

    public function updateAffectedPerson(Request $request, int $beneficiaryId)
    {
        $beneficiary = DB::table('beneficiaries')->where('id', $beneficiaryId)->first();
        if (!$beneficiary) {
            abort(404);
        }

        $person = People::query()->findOrFail($beneficiary->person_id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_id' => ['required', 'exists:locations,id'],
            'disaster_id' => ['required', 'exists:disasters,id'],
            'family_size' => ['nullable', 'integer', 'min:1'],
            'aid_received' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($person, $beneficiary, $validated) {
            DB::table('people')
                ->where('id', $person->id)
                ->update([
                    'name' => $validated['name'],
                ]);

            DB::table('beneficiaries')
                ->where('id', $beneficiary->id)
                ->update([
                    'location_id' => $validated['location_id'],
                    'disaster_id' => $validated['disaster_id'],
                    'family_size' => $validated['family_size'] ?? 1,
                    'aid_received' => $validated['aid_received'] ?? null,
                ]);
        });

        return redirect()->route('admin.affected-people')->with('status', 'Affected person updated successfully.');
    }

    public function destroyAffectedPerson(int $beneficiaryId)
    {
        $beneficiary = DB::table('beneficiaries')->where('id', $beneficiaryId)->first();
        if (!$beneficiary) {
            abort(404);
        }

        DB::table('beneficiary_aid')->where('beneficiary_id', $beneficiary->id)->delete();
        DB::table('beneficiaries')->where('id', $beneficiary->id)->delete();
        DB::table('people')->where('id', $beneficiary->person_id)->delete();

        return redirect()->route('admin.affected-people')->with('status', 'Affected person deleted successfully.');
    }

    public function aidRequests()
    {
        $aidRequests = DB::table('aid_requests as ar')
            ->leftJoin('people as p', 'ar.person_id', '=', 'p.id')
            ->leftJoin('locations as l', 'ar.location_id', '=', 'l.id')
            ->orderByDesc('ar.created_at')
            ->select(
                'ar.id',
                'ar.aid_type_id',
                'ar.description',
                'ar.status',
                'ar.created_at',
                'p.name as person_name',
                'l.city',
                'l.district'
            )
            ->get()
            ->map(function ($request) {
                $aidTypeIds = array_filter(array_map('intval', explode(',', $request->aid_type_id)));
                $aidTypes = DB::table('aid_types')
                    ->whereIn('id', $aidTypeIds)
                    ->pluck('name')
                    ->implode(', ');
                $request->aid_type = $aidTypes ?: 'N/A';
                return $request;
            });

        $people = DB::table('people')->orderBy('name')->get();
        $aidTypes = DB::table('aid_types')->orderBy('name')->get();
        $locations = DB::table('locations')->orderBy('city')->get();

        return view('admin.aid-requests', array_merge([
            'activePage' => 'aid-requests',
            'aidRequests' => $aidRequests,
            'people' => $people,
            'aidTypes' => $aidTypes,
            'locations' => $locations,
        ], $this->adminLayoutData('aid-requests')));
    }

    public function storeAidRequest(Request $request)
    {
        $validated = $request->validate([
            'person_id' => ['required', 'exists:people,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'aid_type_id' => ['required', 'exists:aid_types,id'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        DB::table('aid_requests')->insert([
            'person_id' => $validated['person_id'],
            'location_id' => $validated['location_id'],
            'aid_type_id' => $validated['aid_type_id'],
            'description' => $validated['description'],
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return redirect()->route('admin.aid-requests')->with('status', 'Aid request created successfully.');
    }

    public function updateAidRequest(Request $request, int $requestId)
    {
        $aidRequest = DB::table('aid_requests')->where('id', $requestId)->first();
        if (!$aidRequest) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'completed'])],
        ]);

        DB::table('aid_requests')
            ->where('id', $requestId)
            ->update([
                'status' => $validated['status'],
            ]);

        return redirect()->route('admin.aid-requests')->with('status', 'Aid request status updated successfully.');
    }

    public function destroyAidRequest(int $requestId)
    {
        $aidRequest = DB::table('aid_requests')->where('id', $requestId)->first();
        if (!$aidRequest) {
            abort(404);
        }

        DB::table('aid_requests')->where('id', $requestId)->delete();

        return redirect()->route('admin.aid-requests')->with('status', 'Aid request deleted successfully.');
    }

    public function disasterSubmissions(Request $request)
    {
        $filter = $request->query('status', 'all');
        $allowedFilters = ['all', 'pending', 'approved', 'rejected'];

        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $query = DB::table('volunteer_disaster_submissions as vds')
            ->join('people as p', 'vds.person_id', '=', 'p.id')
            ->join('disasters as d', 'vds.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->orderByDesc('vds.created_at');

        if ($filter !== 'all') {
            $query->where('vds.status', $filter);
        }

        $submissions = $query->select(
            'vds.id',
            'vds.title',
            'vds.description',
            'vds.submission_type',
            'vds.status',
            'vds.created_at',
            'vds.updated_at',
            'vds.person_id',
            'vds.disaster_id',
            'p.name as volunteer_name',
            'd.type as disaster_type',
            'l.city',
            'l.district'
        )->get();

        // Load relationships and convert timestamps
        $submissions = $submissions->map(function ($submission) {
            $submission->person = DB::table('people')->where('id', $submission->person_id)->first();
            $submission->disaster = DB::table('disasters')
                ->leftJoin('locations as l', 'disasters.location_id', '=', 'l.id')
                ->where('disasters.id', $submission->disaster_id)
                ->select('disasters.*', 'l.city', 'l.district')
                ->first();
            
            // Convert timestamps to Carbon objects
            $submission->created_at = \Carbon\Carbon::parse($submission->created_at);
            $submission->updated_at = \Carbon\Carbon::parse($submission->updated_at);
            
            return $submission;
        });

        return view('admin.disaster-submissions', [
            'activePage' => 'disaster-submissions',
            'filter' => $filter,
            'submissions' => $submissions,
            'stats' => $this->dashboardStats(),
        ]);
    }

    public function showDisasterSubmissionReview(int $submissionId)
    {
        $submission = DB::table('volunteer_disaster_submissions')
            ->where('id', $submissionId)
            ->first();

        abort_if(!$submission, 404);

        $submission->person = DB::table('people')->where('id', $submission->person_id)->first();
        $submission->disaster = DB::table('disasters')
            ->leftJoin('locations as l', 'disasters.location_id', '=', 'l.id')
            ->where('disasters.id', $submission->disaster_id)
            ->select('disasters.*', 'l.city', 'l.district')
            ->first();

        // Convert timestamps to Carbon objects
        $submission->created_at = \Carbon\Carbon::parse($submission->created_at);
        $submission->updated_at = \Carbon\Carbon::parse($submission->updated_at);

        return view('admin.disaster-submission-review', [
            'activePage' => 'disaster-submissions',
            'submission' => $submission,
            'stats' => $this->dashboardStats(),
        ]);
    }

    public function updateDisasterSubmission(Request $request, int $submissionId)
    {
        $submission = DB::table('volunteer_disaster_submissions')
            ->where('id', $submissionId)
            ->first();

        abort_if(!$submission, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('volunteer_disaster_submissions')
            ->where('id', $submissionId)
            ->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.disaster-submissions')
            ->with('success', 'Submission reviewed and updated successfully.');
    }
}