<?php

namespace App\Http\Controllers;

use App\Models\People;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OfficialController extends Controller
{
    private function officialUser(): User
    {
        abort_unless(Auth::check(), 403);

        $user = Auth::user();

        abort_unless($user->role === 'official', 403);

        return $user;
    }

    private function officialPersonId(): int
    {
        return (int) $this->officialUser()->person_id;
    }

    private function dashboardStats(): array
    {
        return [
            'approved_disasters' => DB::table('disasters')->whereIn('status', ['in_progress', 'resolved'])->count(),
            'assigned_volunteers' => DB::table('volunteer_assignments')->count(),
            'pending_resource_requests' => DB::table('resource_requests')->where('status', 'pending')->count(),
            'pending_supports' => DB::table('beneficiaries')->where('support_status', 'pending')->count(),
            'policies' => DB::table('policies')->count(),
            'available_resources' => (int) DB::table('resources')->sum('quantity'),
            'total_transactions' => DB::table('transactions')->where('status', 'completed')->count(),
            'total_transaction_amount' => (float) DB::table('transactions')->where('status', 'completed')->sum('amount'),
        ];
    }

    private function layoutData(string $activePage): array
    {
        return [
            'activePage' => $activePage,
            'stats' => $this->dashboardStats(),
        ];
    }

    private function commonWorkspaceData(): array
    {
        $approvedDisasters = DB::table('disasters as d')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->whereIn('d.status', ['in_progress', 'resolved'])
            ->orderByDesc('d.created_at')
            ->select('d.id', 'd.type', 'd.status', 'd.disaster_date', 'd.affected_population', 'l.city', 'l.district', 'l.country')
            ->get();

        $volunteerAssignments = DB::table('volunteer_assignments as va')
            ->join('people as p', 'va.person_id', '=', 'p.id')
            ->join('disasters as d', 'va.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->leftJoin('volunteers as v', 'va.person_id', '=', 'v.person_id')
            ->orderByDesc('va.assigned_date')
            ->select(
                'va.id as assignment_id',
                'va.person_id',
                'va.disaster_id',
                'va.hours_worked',
                'va.assigned_date',
                'p.name as volunteer_name',
                'p.email as volunteer_email',
                'd.type as disaster_type',
                'd.status as disaster_status',
                'l.city',
                'l.district',
                'v.availability',
                'v.skills'
            )
            ->get();

        $resources = DB::table('resources')
            ->orderByDesc('created_at')
            ->get();

        $resourceRequests = DB::table('resource_requests as rr')
            ->join('disasters as d', 'rr.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->leftJoin('people as p', 'rr.requested_by_person_id', '=', 'p.id')
            ->orderByDesc('rr.created_at')
            ->select(
                'rr.id',
                'rr.resource_name',
                'rr.quantity_requested',
                'rr.notes',
                'rr.status',
                'rr.created_at',
                'd.type as disaster_type',
                'l.city',
                'l.district',
                'p.name as requested_by'
            )
            ->get();

        $resourceUsage = DB::table('resource_usage_logs as rul')
            ->join('disasters as d', 'rul.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->leftJoin('resources as r', 'rul.resource_id', '=', 'r.id')
            ->leftJoin('people as p', 'rul.recorded_by_person_id', '=', 'p.id')
            ->orderByDesc('rul.created_at')
            ->select(
                'rul.id',
                'rul.resource_name',
                'rul.quantity_used',
                'rul.notes',
                'rul.created_at',
                'd.type as disaster_type',
                'l.city',
                'l.district',
                'r.name as resource_stock_name',
                'p.name as recorded_by'
            )
            ->get();

        $communitySupports = DB::table('beneficiaries as b')
            ->join('people as p', 'b.person_id', '=', 'p.id')
            ->join('disasters as d', 'b.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'b.location_id', '=', 'l.id')
            ->orderByDesc('b.created_at')
            ->select(
                'b.id as beneficiary_id',
                'b.person_id',
                'b.location_id',
                'b.disaster_id',
                'p.name',
                'p.email',
                'p.phone',
                'b.family_size',
                'b.aid_received',
                'b.support_status',
                'b.support_notes',
                'b.created_at',
                'd.type as disaster_type',
                'l.city',
                'l.district'
            )
            ->get();

        $policies = DB::table('policies')
            ->orderByDesc('created_at')
            ->get();

        $disasters = DB::table('disasters')
            ->leftJoin('locations as l', 'disasters.location_id', '=', 'l.id')
            ->orderBy('disasters.type')
            ->select('disasters.id', 'disasters.type', 'disasters.status', 'l.city', 'l.district')
            ->get();

        $locations = DB::table('locations')->orderBy('city')->get();

        $volunteers = DB::table('volunteers as v')
            ->join('people as p', 'v.person_id', '=', 'p.id')
            ->orderBy('p.name')
            ->select('v.person_id', 'p.name', 'p.email', 'v.skills', 'v.availability')
            ->get();

        $alerts = DB::table('alerts')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return compact(
            'approvedDisasters',
            'volunteerAssignments',
            'resources',
            'resourceRequests',
            'resourceUsage',
            'communitySupports',
            'policies',
            'disasters',
            'locations',
            'volunteers',
            'alerts'
        );
    }

    public function dashboard()
    {
        $data = $this->commonWorkspaceData();

        $transactions = DB::table('transactions as t')
            ->leftJoin('fundraising as f', 't.campaign_id', '=', 'f.id')
            ->where('t.status', 'completed')
            ->orderByDesc('t.created_at')
            ->select('t.order_id', 't.donor_name', 't.donor_email', 't.amount', 't.payment_method', 't.status', 't.created_at', 'f.title as campaign_title')
            ->limit(10)
            ->get();

        return view('official.dashboard', array_merge($this->layoutData('dashboard'), [
            'approvedDisasters' => $data['approvedDisasters']->take(4),
            'volunteerAssignments' => $data['volunteerAssignments']->take(4),
            'resources' => $data['resources']->take(4),
            'resourceRequests' => $data['resourceRequests']->take(4),
            'resourceUsage' => $data['resourceUsage']->take(4),
            'communitySupports' => $data['communitySupports']->take(4),
            'policies' => $data['policies']->take(4),
            'disasters' => $data['disasters'],
            'locations' => $data['locations'],
            'volunteers' => $data['volunteers']->take(4),
            'alerts' => $data['alerts'],
            'recentTransactions' => $transactions,
        ]));
    }

    public function disasters()
    {
        $data = $this->commonWorkspaceData();

        return view('official.disasters', array_merge($this->layoutData('disasters'), [
            'approvedDisasters' => $data['approvedDisasters'],
            'disasters' => $data['disasters'],
        ]));
    }

    public function volunteers()
    {
        $data = $this->commonWorkspaceData();

        return view('official.volunteers', array_merge($this->layoutData('volunteers'), [
            'volunteerAssignments' => $data['volunteerAssignments'],
            'volunteers' => $data['volunteers'],
            'disasters' => $data['disasters'],
        ]));
    }

    public function resources()
    {
        $data = $this->commonWorkspaceData();

        return view('official.resources', array_merge($this->layoutData('resources'), [
            'resources' => $data['resources'],
            'resourceRequests' => $data['resourceRequests'],
            'resourceUsage' => $data['resourceUsage'],
            'disasters' => $data['disasters'],
        ]));
    }

    public function communitySupports()
    {
        $data = $this->commonWorkspaceData();

        return view('official.community-supports', array_merge($this->layoutData('community-supports'), [
            'communitySupports' => $data['communitySupports'],
            'disasters' => $data['disasters'],
            'locations' => $data['locations'],
        ]));
    }

    public function policies()
    {
        $data = $this->commonWorkspaceData();

        return view('official.policies', array_merge($this->layoutData('policies'), [
            'policies' => $data['policies'],
            'alerts' => $data['alerts'],
        ]));
    }

    public function updateDisasterStatus(Request $request, int $disasterId)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'resolved'])],
        ]);

        $updated = DB::table('disasters')
            ->where('id', $disasterId)
            ->update([
                'status' => $validated['status'],
            ]);

        return redirect()
            ->route('official.dashboard')
            ->with('status', $updated ? 'Disaster status updated.' : 'No changes were made.');
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

            $person = DB::table('people')->where('id', $validated['person_id'])->first();
            $disaster = DB::table('disasters')->where('id', $validated['disaster_id'])->first();

            if ($person && $disaster) {
                DB::table('alerts')->insert([
                    'title' => 'Volunteer Assignment Updated',
                    'message' => $person->name . ' has been assigned to ' . $disaster->type . '. Check your volunteer dashboard for details.',
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('official.dashboard')->with('status', 'Volunteer assignment saved.');
    }

    public function updateVolunteerAssignment(Request $request, int $assignmentId)
    {
        $validated = $request->validate([
            'hours_worked' => ['required', 'integer', 'min:0'],
            'assigned_date' => ['required', 'date'],
        ]);

        $updated = DB::table('volunteer_assignments')
            ->where('id', $assignmentId)
            ->update([
                'hours_worked' => $validated['hours_worked'],
                'assigned_date' => $validated['assigned_date'],
            ]);

        return redirect()
            ->route('official.dashboard')
            ->with('status', $updated ? 'Volunteer activity updated.' : 'No changes were made.');
    }

    public function storeResourceRequest(Request $request)
    {
        $validated = $request->validate([
            'disaster_id' => ['required', 'exists:disasters,id'],
            'resource_name' => ['required', 'string', 'max:100'],
            'quantity_requested' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('resource_requests')->insert([
            'disaster_id' => $validated['disaster_id'],
            'requested_by_person_id' => $this->officialPersonId(),
            'resource_name' => $validated['resource_name'],
            'quantity_requested' => $validated['quantity_requested'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return redirect()->route('official.dashboard')->with('status', 'Resource request submitted.');
    }

    public function updateResourceRequest(Request $request, int $requestId)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'fulfilled', 'rejected'])],
        ]);

        $updated = DB::table('resource_requests')
            ->where('id', $requestId)
            ->update([
                'status' => $validated['status'],
            ]);

        return redirect()->route('official.dashboard')->with('status', $updated ? 'Resource request updated.' : 'No changes were made.');
    }

    public function storeResourceUsage(Request $request)
    {
        $validated = $request->validate([
            'disaster_id' => ['required', 'exists:disasters,id'],
            'resource_id' => ['nullable', 'exists:resources,id'],
            'resource_name' => ['required', 'string', 'max:100'],
            'quantity_used' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated) {
            DB::table('resource_usage_logs')->insert([
                'disaster_id' => $validated['disaster_id'],
                'resource_id' => $validated['resource_id'] ?? null,
                'resource_name' => $validated['resource_name'],
                'quantity_used' => $validated['quantity_used'],
                'notes' => $validated['notes'] ?? null,
                'recorded_by_person_id' => $this->officialPersonId(),
                'created_at' => now(),
            ]);

            if (!empty($validated['resource_id'])) {
                $resource = DB::table('resources')->where('id', $validated['resource_id'])->lockForUpdate()->first();

                if ($resource) {
                    DB::table('resources')
                        ->where('id', $resource->id)
                        ->update([
                            'quantity' => max(0, (int) $resource->quantity - (int) $validated['quantity_used']),
                        ]);
                }
            }
        });

        return redirect()->route('official.dashboard')->with('status', 'Resource usage recorded.');
    }

    public function storeCommunitySupport(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_id' => ['required', 'exists:locations,id'],
            'disaster_id' => ['required', 'exists:disasters,id'],
            'family_size' => ['nullable', 'integer', 'min:1'],
            'aid_received' => ['nullable', 'string', 'max:255'],
            'support_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'support_notes' => ['nullable', 'string', 'max:2000'],
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
                'support_status' => $validated['support_status'],
                'support_notes' => $validated['support_notes'] ?? null,
                'created_at' => now(),
            ]);
        });

        return redirect()->route('official.dashboard')->with('status', 'Community support record added.');
    }

    public function updateCommunitySupport(Request $request, int $beneficiaryId)
    {
        $beneficiary = DB::table('beneficiaries')->where('id', $beneficiaryId)->first();

        abort_if(!$beneficiary, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_id' => ['required', 'exists:locations,id'],
            'disaster_id' => ['required', 'exists:disasters,id'],
            'family_size' => ['nullable', 'integer', 'min:1'],
            'aid_received' => ['nullable', 'string', 'max:255'],
            'support_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'support_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($beneficiary, $validated) {
            People::query()
                ->where('id', $beneficiary->person_id)
                ->update([
                    'name' => $validated['name'],
                ]);

            DB::table('beneficiaries')
                ->where('id', $beneficiary->id)
                ->update([
                    'family_size' => $validated['family_size'] ?? 1,
                    'location_id' => $validated['location_id'],
                    'disaster_id' => $validated['disaster_id'],
                    'aid_received' => $validated['aid_received'] ?? null,
                    'support_status' => $validated['support_status'],
                    'support_notes' => $validated['support_notes'] ?? null,
                ]);
        });

        return redirect()->route('official.dashboard')->with('status', 'Community support updated.');
    }

    public function storePolicy(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($validated) {
            DB::table('policies')->insert([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'created_at' => now(),
            ]);

            DB::table('alerts')->insert([
                'title' => 'New Policy for Volunteers',
                'message' => $validated['title'] . ': ' . $validated['description'],
                'created_at' => now(),
            ]);
        });

        return redirect()->route('official.dashboard')->with('status', 'Policy created and broadcast to alerts.');
    }

    public function publicDisasterReports()
    {
        $reports = DB::table('incidents as i')
            ->join('disasters as d', 'i.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->where('i.status', '!=', 'resolved')
            ->orderByDesc('i.created_at')
            ->select(
                'i.id',
                'i.title',
                'i.description',
                'i.severity',
                'i.status',
                'i.created_at',
                'd.type as disaster_type',
                'l.city',
                'l.district'
            )
            ->paginate(20);

        return view('official.public-disaster-reports', array_merge($this->layoutData('public-disaster-reports'), [
            'reports' => $reports,
        ]));
    }

    public function reviewDisasterReport($reportId)
    {
        $report = DB::table('incidents as i')
            ->join('disasters as d', 'i.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->where('i.id', $reportId)
            ->select(
                'i.id',
                'i.title',
                'i.description',
                'i.severity',
                'i.status',
                'i.created_at',
                'i.image_path',
                'd.type as disaster_type',
                'l.city',
                'l.district'
            )
            ->first();

        if (!$report) {
            abort(404, 'Disaster report not found.');
        }

        return view('official.disaster-report-detail', array_merge($this->layoutData('public-disaster-reports'), [
            'report' => $report,
        ]));
    }

    public function publicHelpRequests()
    {
        $requests = DB::table('aid_requests as ar')
            ->join('people as p', 'ar.person_id', '=', 'p.id')
            ->leftJoin('locations as l', 'ar.location_id', '=', 'l.id')
            ->where('ar.status', '!=', 'completed')
            ->orderByDesc('ar.created_at')
            ->select(
                'ar.id',
                'ar.aid_type_id',
                'ar.description',
                'ar.status',
                'ar.created_at',
                'p.name',
                'p.email',
                'p.phone',
                'l.city',
                'l.district'
            )
            ->paginate(20);

        return view('official.public-help-requests', array_merge($this->layoutData('public-help-requests'), [
            'requests' => $requests,
        ]));
    }

    public function approveHelpRequest(Request $request, $requestId)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,completed'],
        ]);

        DB::table('aid_requests')
            ->where('id', $requestId)
            ->update([
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('official.public-help-requests')
            ->with('status', 'Help request status updated successfully.');
    }

    public function volunteerSubmissions()
    {
        $volunteerSubmissions = DB::table('volunteer_disaster_submissions as vds')
            ->join('people as p', 'vds.person_id', '=', 'p.id')
            ->join('disasters as d', 'vds.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->orderByDesc('vds.created_at')
            ->select(
                'vds.id',
                'vds.person_id',
                'vds.disaster_id',
                'vds.title',
                'vds.description',
                'vds.submission_type',
                'vds.status',
                'vds.admin_notes',
                'vds.created_at',
                'vds.updated_at',
                'p.name as volunteer_name',
                'p.email as volunteer_email',
                'p.phone as volunteer_phone',
                'd.type as disaster_type',
                'd.disaster_date',
                'l.city',
                'l.district'
            )
            ->get()
            ->map(function ($submission) {
                $submission->created_at = Carbon::parse($submission->created_at);
                $submission->updated_at = Carbon::parse($submission->updated_at);
                return $submission;
            });

        return view('official.volunteer-submissions', array_merge($this->layoutData('volunteer-submissions'), [
            'volunteerSubmissions' => $volunteerSubmissions,
        ]));
    }

    public function showVolunteerSubmission(int $submissionId)
    {
        $submission = DB::table('volunteer_disaster_submissions as vds')
            ->join('people as p', 'vds.person_id', '=', 'p.id')
            ->join('disasters as d', 'vds.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->where('vds.id', $submissionId)
            ->select(
                'vds.id',
                'vds.person_id',
                'vds.disaster_id',
                'vds.title',
                'vds.description',
                'vds.submission_type',
                'vds.status',
                'vds.admin_notes',
                'vds.created_at',
                'vds.updated_at',
                'p.name as volunteer_name',
                'p.email as volunteer_email',
                'p.phone as volunteer_phone',
                'd.type as disaster_type',
                'd.disaster_date',
                'l.city',
                'l.district'
            )
            ->first();

        abort_if(!$submission, 404);

        $submission->created_at = Carbon::parse($submission->created_at);
        $submission->updated_at = Carbon::parse($submission->updated_at);

        return view('official.volunteer-submission-detail', array_merge($this->layoutData('volunteer-submissions'), [
            'submission' => $submission,
        ]));
    }

    public function updateVolunteerSubmission(Request $request, int $submissionId)
    {
        $submission = DB::table('volunteer_disaster_submissions')->where('id', $submissionId)->first();

        abort_if(!$submission, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::table('volunteer_disaster_submissions')
            ->where('id', $submissionId)
            ->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('official.volunteer-submissions')
            ->with('status', 'Submission reviewed and updated successfully.');
    }

    public function donations()
    {
        $donations = DB::table('fundraising as f')
            ->leftJoin('people as p', 'f.person_id', '=', 'p.id')
            ->leftJoin('disasters as d', 'f.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->orderByDesc('f.created_at')
            ->select(
                'f.id',
                'f.person_id',
                'f.disaster_id',
                'f.title',
                'f.amount',
                'f.role',
                'f.status',
                'f.created_at',
                'p.name as person_name',
                'd.type as disaster_type',
                'l.city',
                'l.district'
            )
            ->get();

        $disasters = DB::table('disasters')->orderByDesc('created_at')->get();
        $people = DB::table('people')->orderBy('name')->get();

        return view('official.donations', array_merge($this->layoutData('donations'), [
            'donations' => $donations,
            'disasters' => $disasters,
            'people' => $people,
        ]));
    }

    public function storeDonation(Request $request)
    {
        $validated = $request->validate([
            'person_id' => ['required', 'exists:people,id'],
            'disaster_id' => ['required', 'exists:disasters,id'],
            'title' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0'],
            'role' => ['required', 'in:donor,organizer'],
            'status' => ['required', 'in:active,completed'],
        ]);

        DB::table('fundraising')->insert([
            'person_id' => $validated['person_id'],
            'disaster_id' => $validated['disaster_id'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'created_at' => now(),
        ]);

        return redirect()->route('official.donations')->with('status', 'Donation record created successfully.');
    }

    public function updateDonation(Request $request, int $donationId)
    {
        $donation = DB::table('fundraising')->where('id', $donationId)->first();
        if (!$donation) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0'],
            'role' => ['required', 'in:donor,organizer'],
            'status' => ['required', 'in:active,completed'],
        ]);

        DB::table('fundraising')
            ->where('id', $donationId)
            ->update([
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'role' => $validated['role'],
                'status' => $validated['status'],
            ]);

        return redirect()->route('official.donations')->with('status', 'Donation record updated successfully.');
    }

    public function destroyDonation(int $donationId)
    {
        DB::table('fundraising')->where('id', $donationId)->delete();

        return redirect()->route('official.donations')->with('status', 'Donation record deleted successfully.');
    }

    public function transactions(Request $request)
    {
        $query = DB::table('transactions as t')
            ->leftJoin('fundraising as f', 't.campaign_id', '=', 'f.id')
            ->orderByDesc('t.created_at')
            ->select(
                't.id',
                't.order_id',
                't.donor_name',
                't.donor_email',
                't.donor_phone',
                't.amount',
                't.payment_method',
                't.status',
                't.created_at',
                't.updated_at',
                'f.id as campaign_id',
                'f.title as campaign_title'
            );

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('t.status', $request->get('status'));
        }

        // Filter by payment method if provided
        if ($request->filled('method')) {
            $query->where('t.payment_method', $request->get('method'));
        }

        // Search by donor name or email
        if ($request->filled('search')) {
            $search = '%' . $request->get('search') . '%';
            $query->where(function($q) use ($search) {
                $q->where('t.donor_name', 'like', $search)
                  ->orWhere('t.donor_email', 'like', $search);
            });
        }

        $transactions = $query->paginate(25);

        return view('official.transactions', array_merge($this->layoutData('transactions'), [
            'transactions' => $transactions,
        ]));
    }
}