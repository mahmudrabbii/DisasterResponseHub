<?php

namespace App\Http\Controllers;

use App\Models\People;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VolunteerController extends Controller
{
    private function volunteerUser(): User
    {
        abort_unless(Auth::check(), 403);

        $user = Auth::user();

        abort_unless($user->role === 'volunteer', 403);

        return $user;
    }

    private function volunteerPersonId(): int
    {
        $user = $this->volunteerUser();

        abort_unless($user->person, 404);

        return (int) $user->person->id;
    }

    private function volunteerRecord(int $personId)
    {
        return DB::table('volunteers')->where('person_id', $personId)->first();
    }

    private function dashboardData(int $personId): array
    {
        $person = People::query()->findOrFail($personId);
        $volunteer = $this->volunteerRecord($personId);

        if (!$volunteer) {
            DB::table('volunteers')->insert([
                'person_id' => $personId,
                'skills' => null,
                'availability' => 'available',
                'created_at' => now(),
            ]);

            $volunteer = $this->volunteerRecord($personId);
        }

        $tasks = DB::table('volunteer_assignments as va')
            ->join('disasters as d', 'va.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->where('va.person_id', $personId)
            ->orderByDesc('va.assigned_date')
            ->select(
                'va.id as assignment_id',
                'va.disaster_id',
                'va.hours_worked',
                'va.assigned_date',
                'd.type as disaster_type',
                'd.status as disaster_status',
                'd.disaster_date',
                'd.affected_population',
                'l.city',
                'l.district',
                'l.country'
            )
            ->get();

        $taskDisasterIds = $tasks->pluck('disaster_id')->unique()->values();

        $taskIncidents = $taskDisasterIds->isEmpty()
            ? collect()
            : DB::table('incidents')
                ->whereIn('disaster_id', $taskDisasterIds)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('disaster_id');

        $aidRequests = DB::table('aid_requests as ar')
            ->leftJoin('aid_types as at', 'ar.aid_type_id', '=', 'at.id')
            ->leftJoin('locations as l', 'ar.location_id', '=', 'l.id')
            ->where('ar.person_id', $personId)
            ->orderByDesc('ar.created_at')
            ->select(
                'ar.id',
                'ar.description',
                'ar.status',
                'ar.created_at',
                'at.name as aid_type',
                'l.city',
                'l.district'
            )
            ->get();

        $disasters = DB::table('disasters as d')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->orderByDesc('d.created_at')
            ->select(
                'd.id',
                'd.type',
                'd.disaster_date',
                'd.affected_population',
                'd.status',
                'd.created_at',
                'l.city',
                'l.district',
                'l.country'
            )
            ->get();

        $disasterIncidentCounts = DB::table('incidents')
            ->select('disaster_id', DB::raw('COUNT(*) as incident_count'))
            ->groupBy('disaster_id')
            ->pluck('incident_count', 'disaster_id');

        $incidentFeed = $disasters->isEmpty()
            ? collect()
            : DB::table('incidents')
                ->whereIn('disaster_id', $disasters->pluck('id'))
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('disaster_id');

        $aidTypes = DB::table('aid_types')->orderBy('name')->get();
        $locations = DB::table('locations')->orderBy('city')->get();

        $alerts = DB::table('alerts')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $stats = [
            'assigned_tasks' => $tasks->count(),
            'unique_disasters' => $tasks->pluck('disaster_id')->unique()->count(),
            'hours_worked' => (int) $tasks->sum('hours_worked'),
            'pending_requests' => $aidRequests->where('status', 'pending')->count(),
            'active_disasters' => $disasters->where('status', 'in_progress')->count(),
        ];

        return compact(
            'person',
            'volunteer',
            'tasks',
            'taskIncidents',
            'aidRequests',
            'disasters',
            'disasterIncidentCounts',
            'incidentFeed',
            'aidTypes',
            'locations',
            'alerts',
            'stats'
        );
    }

    public function dashboard()
    {
        $personId = $this->volunteerPersonId();

        return view('volunteer ui.dashboard', array_merge([
            'activePage' => 'dashboard',
        ], $this->dashboardData($personId)));
    }

    public function tasks()
    {
        $personId = $this->volunteerPersonId();

        return view('volunteer ui.tasks', array_merge([
            'activePage' => 'tasks',
        ], $this->dashboardData($personId)));
    }

    public function updateTaskHours(Request $request, int $assignmentId)
    {
        $personId = $this->volunteerPersonId();

        $validated = $request->validate([
            'hours_worked' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $updated = DB::table('volunteer_assignments')
            ->where('id', $assignmentId)
            ->where('person_id', $personId)
            ->update([
                'hours_worked' => $validated['hours_worked'],
            ]);

        abort_unless($updated, 404);

        return redirect()
            ->route('volunteer.tasks')
            ->with('status', 'Task hours updated.');
    }

    public function profile()
    {
        $personId = $this->volunteerPersonId();

        return view('volunteer ui.profile', array_merge([
            'activePage' => 'profile',
        ], $this->dashboardData($personId)));
    }

    public function updateProfile(Request $request)
    {
        $personId = $this->volunteerPersonId();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('people', 'email')->ignore($personId)],
            'phone' => ['required', 'string', 'max:20'],
            'skills' => ['nullable', 'string', 'max:150'],
            'availability' => ['required', Rule::in(['available', 'busy', 'on_call', 'offline'])],
        ]);

        DB::table('people')
            ->where('id', $personId)
            ->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

        DB::table('volunteers')->updateOrInsert(
            ['person_id' => $personId],
            [
                'skills' => $validated['skills'],
                'availability' => $validated['availability'],
                'created_at' => now(),
            ]
        );

        return redirect()
            ->route('volunteer.profile')
            ->with('status', 'Volunteer profile updated.');
    }

    public function aidRequests()
    {
        $personId = $this->volunteerPersonId();

        return view('volunteer ui.aid-request', array_merge([
            'activePage' => 'aid-request',
        ], $this->dashboardData($personId)));
    }

    public function storeAidRequest(Request $request)
    {
        $personId = $this->volunteerPersonId();

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'aid_type_id' => ['required', 'exists:aid_types,id'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        DB::table('aid_requests')->insert([
            'person_id' => $personId,
            'location_id' => $validated['location_id'],
            'aid_type_id' => $validated['aid_type_id'],
            'description' => $validated['description'],
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return redirect()
            ->route('volunteer.aid-requests')
            ->with('status', 'Aid request submitted.');
    }

    public function disasterData(Request $request)
    {
        $personId = $this->volunteerPersonId();
        $data = $this->dashboardData($personId);

        $filter = $request->query('status', 'all');
        $allowedFilters = ['all', 'pending', 'in_progress', 'resolved'];

        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $filteredDisasters = $filter === 'all'
            ? $data['disasters']
            : $data['disasters']->where('status', $filter)->values();

        return view('volunteer ui.disaster-data', [
            'activePage' => 'disaster-data',
            'filter' => $filter,
            'disasters' => $filteredDisasters,
            'disasterIncidentCounts' => $data['disasterIncidentCounts'],
            'incidentFeed' => $data['incidentFeed'],
            'stats' => $data['stats'],
            'person' => $data['person'],
            'volunteer' => $data['volunteer'],
        ]);
    }
}