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

        abort_unless(!empty($user->person_id), 404);

        return (int) $user->person_id;
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

        $policies = DB::table('policies')
            ->orderByDesc('created_at')
            ->limit(6)
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
            'policies',
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
        $acceptedAssignments = collect(session('accepted_assignments', []))->map(fn ($id) => (int) $id)->all();

        return view('volunteer ui.tasks', array_merge([
            'activePage' => 'tasks',
            'acceptedAssignments' => $acceptedAssignments,
        ], $this->dashboardData($personId)));
    }

    public function acceptTask(int $assignmentId)
    {
        $personId = $this->volunteerPersonId();

        $assignment = DB::table('volunteer_assignments')
            ->where('id', $assignmentId)
            ->where('person_id', $personId)
            ->first();

        abort_if(!$assignment, 404);

        $acceptedAssignments = collect(session('accepted_assignments', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (!in_array((int) $assignmentId, $acceptedAssignments, true)) {
            $acceptedAssignments[] = (int) $assignmentId;
            session(['accepted_assignments' => $acceptedAssignments]);
            $message = 'Work accepted successfully.';
        } else {
            $message = 'This work is already accepted.';
        }

        return redirect()
            ->route('volunteer.tasks')
            ->with('status', $message);
    }

    public function updateTaskHours(Request $request, int $assignmentId)
    {
        $personId = $this->volunteerPersonId();

        $validated = $request->validate([
            'hours_worked' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $assignment = DB::table('volunteer_assignments')
            ->where('id', $assignmentId)
            ->where('person_id', $personId)
            ->first();

        abort_if(!$assignment, 404);

        $updated = DB::table('volunteer_assignments')
            ->where('id', $assignmentId)
            ->where('person_id', $personId)
            ->update([
                'hours_worked' => $validated['hours_worked'],
            ]);

        return redirect()
            ->route('volunteer.tasks')
            ->with('status', $updated ? 'Task hours updated.' : 'No changes were made to task hours.');
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
        $dashboardData = $this->dashboardData($personId);

        $assignedDisasters = DB::table('volunteer_assignments as va')
            ->join('disasters as d', 'va.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->where('va.person_id', $personId)
            ->orderByDesc('va.assigned_date')
            ->select('d.id', 'd.type', 'd.disaster_date', 'd.status', 'l.city', 'l.district')
            ->distinct()
            ->get();

        $myAidRequests = DB::table('aid_requests as ar')
            ->leftJoin('locations as l', 'ar.location_id', '=', 'l.id')
            ->where('ar.person_id', $personId)
            ->orderByDesc('ar.created_at')
            ->select(
                'ar.id',
                'ar.person_id',
                'ar.aid_type_id',
                'ar.description',
                'ar.status',
                'ar.created_at',
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

        return view('volunteer ui.aid-request', array_merge($dashboardData, [
            'activePage' => 'aid-request',
            'assignedDisasters' => $assignedDisasters,
            'aidRequests' => $myAidRequests,
            'myAidRequests' => $myAidRequests,
        ]));
    }


    public function storeAidRequest(Request $request)
    {
        $personId = $this->volunteerPersonId();

        $validated = $request->validate([
            'disaster_id' => [
                'required',
                Rule::exists('volunteer_assignments', 'disaster_id')->where(function ($query) use ($personId) {
                    $query->where('person_id', $personId);
                }),
            ],
            'aid_type_ids' => ['required', 'array', 'min:1'],
            'aid_type_ids.*' => ['required', 'distinct', 'exists:aid_types,id'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $disaster = DB::table('disasters')
            ->where('id', $validated['disaster_id'])
            ->select('location_id')
            ->first();

        if (!$disaster || !$disaster->location_id) {
            return redirect()
                ->route('volunteer.aid-requests')
                ->withErrors(['disaster_id' => 'Selected disaster has no valid location assigned.'])
                ->withInput();
        }

        DB::transaction(function () use ($personId, $disaster, $validated) {
            $aidTypeIds = implode(',', $validated['aid_type_ids']);

            DB::table('aid_requests')->insert([
                'person_id' => $personId,
                'location_id' => $disaster->location_id,
                'aid_type_id' => $aidTypeIds,
                'description' => $validated['description'],
                'status' => 'pending',
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('volunteer.aid-requests')
            ->with('status', 'Aid request submitted successfully.');
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

    public function showCreateSubmissionForm()
    {
        $personId = $this->volunteerPersonId();
        $data = $this->dashboardData($personId);

        return view('volunteer ui.submit-disaster-data', array_merge([
            'activePage' => 'disaster-submissions',
            'disasters' => $data['disasters'],
        ]));
    }

    public function storeDisasterSubmission(Request $request)
    {
        $personId = $this->volunteerPersonId();

        $validated = $request->validate([
            'disaster_id' => ['required', 'exists:disasters,id'],
            'submission_type' => ['required', 'in:incident_report,damage_assessment,resource_need,population_data,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        DB::table('volunteer_disaster_submissions')->insert([
            'person_id' => $personId,
            'disaster_id' => $validated['disaster_id'],
            'submission_type' => $validated['submission_type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('volunteer.disaster-submissions')
            ->with('success', 'Your disaster report has been submitted for review.');
    }

    public function showDisasterSubmissions(Request $request)
    {
        $personId = $this->volunteerPersonId();

        $filter = $request->query('status', 'all');
        $allowedFilters = ['all', 'pending', 'approved', 'rejected'];

        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }

        $query = DB::table('volunteer_disaster_submissions as vds')
            ->join('people as p', 'vds.person_id', '=', 'p.id')
            ->join('disasters as d', 'vds.disaster_id', '=', 'd.id')
            ->where('vds.person_id', $personId)
            ->orderByDesc('vds.created_at');

        if ($filter !== 'all') {
            $query->where('vds.status', $filter);
        }

        $submissions = $query->select('vds.*')->get();

        // Load disasters relationship and convert timestamps for each submission
        $submissions = $submissions->map(function ($submission) {
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

        return view('volunteer ui.disaster-submissions', [
            'activePage' => 'disaster-submissions',
            'filter' => $filter,
            'submissions' => $submissions,
        ]);
    }

    public function showDisasterSubmissionDetail(int $submissionId)
    {
        $personId = $this->volunteerPersonId();

        $submission = DB::table('volunteer_disaster_submissions')
            ->where('id', $submissionId)
            ->where('person_id', $personId)
            ->first();

        abort_if(!$submission, 404);

        $submission->disaster = DB::table('disasters')
            ->leftJoin('locations as l', 'disasters.location_id', '=', 'l.id')
            ->where('disasters.id', $submission->disaster_id)
            ->select('disasters.*', 'l.city', 'l.district')
            ->first();

        // Convert timestamps to Carbon objects
        $submission->created_at = \Carbon\Carbon::parse($submission->created_at);
        $submission->updated_at = \Carbon\Carbon::parse($submission->updated_at);

        return view('volunteer ui.disaster-submission-detail', [
            'activePage' => 'disaster-submissions',
            'submission' => $submission,
        ]);
    }
}