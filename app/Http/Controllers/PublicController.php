<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function index()
    {
        $alerts = DB::table('alerts')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $disasters = DB::table('disasters as d')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->where('d.status', '!=', 'pending')
            ->orderByDesc('d.created_at')
            ->select(
                'd.id',
                'd.type',
                'd.disaster_date',
                'd.affected_population',
                'd.status',
                'l.city',
                'l.district',
                'l.country'
            )
            ->limit(5)
            ->get();

        return view('public.home', [
            'alerts' => $alerts,
            'disasters' => $disasters,
        ]);
    }

    public function reportDisaster()
    {
        $locations = DB::table('locations')->orderBy('city')->get();
        $disasters = DB::table('disasters')->select('id', 'type')->orderBy('type')->get();

        return view('public.report-disaster', [
            'locations' => $locations,
            'disasters' => $disasters,
        ]);
    }

    public function storeDisasterReport(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'location_id' => ['required', 'exists:locations,id'],
            'disaster_id' => ['required', 'exists:disasters,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'severity' => ['required', 'in:low,medium,high'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:20480'],
        ]);

        // Create or find person
        $personId = DB::table('people')->insertGetId([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'created_at' => now(),
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('disaster-reports', 'public');
        }

        // Create incident report
        DB::table('incidents')->insert([
            'disaster_id' => $validated['disaster_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'status' => 'reported',
            'image_path' => $imagePath,
            'created_at' => now(),
        ]);

        return redirect()->route('public.home')->with('status', 'Disaster report submitted successfully. Thank you for reporting!');
    }

    public function requestHelp()
    {
        $locations = DB::table('locations')->orderBy('city')->get();
        $aidTypes = DB::table('aid_types')->distinct('id')->orderBy('name')->get();

        return view('public.request-help', [
            'locations' => $locations,
            'aidTypes' => $aidTypes,
        ]);
    }

    public function storeHelpRequest(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'location_id' => ['required', 'string', 'max:255'],
            'aid_type_ids' => ['required', 'array', 'min:1'],
            'aid_type_ids.*' => ['exists:aid_types,id'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        // Create or find person by email
        $person = DB::table('people')->where('email', $validated['email'])->first();
        
        if ($person) {
            $personId = $person->id;
        } else {
            $personId = DB::table('people')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'created_at' => now(),
            ]);
        }

        // Find or create location by city name
        $location = DB::table('locations')->where('city', $validated['location_id'])->first();
        
        if ($location) {
            $locationId = $location->id;
        } else {
            // Create new location if it doesn't exist
            $locationId = DB::table('locations')->insertGetId([
                'city' => $validated['location_id'],
                'district' => '',
                'country' => 'Bangladesh',
            ]);
        }

        // Create aid request
        DB::table('aid_requests')->insert([
            'person_id' => $personId,
            'location_id' => $locationId,
            'aid_type_id' => implode(',', $validated['aid_type_ids']),
            'description' => $validated['description'],
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return redirect()->route('public.home')->with('status', 'Help request submitted successfully. We will review and respond soon!');
    }

    public function viewAlerts()
    {
        $alerts = DB::table('alerts')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('public.view-alerts', [
            'alerts' => $alerts,
        ]);
    }

    public function viewDisasters()
    {
        $disasters = DB::table('disasters as d')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->where('d.status', '!=', 'pending')
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
            ->paginate(20);

        return view('public.view-disasters', [
            'disasters' => $disasters,
        ]);
    }

    public function donate()
    {
        // Get distinct active disasters with fundraising campaigns
        $disasterIds = DB::table('fundraising')
            ->where('status', 'active')
            ->where('role', 'organizer')
            ->distinct()
            ->pluck('disaster_id');

        // Get campaigns for these disasters
        $campaigns = DB::table('fundraising as f')
            ->leftJoin('disasters as d', 'f.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->whereIn('f.disaster_id', $disasterIds)
            ->where('f.status', 'active')
            ->where('f.role', 'organizer')
            ->select(
                'f.id',
                'f.title',
                'f.disaster_id',
                'd.type as disaster_type',
                'l.city',
                'l.district'
            )
            ->distinct('f.disaster_id')
            ->get();

        // For each campaign, calculate stats from transactions table
        $campaignStats = [];
        foreach ($campaigns as $campaign) {
            // Get raised amount from completed transactions for this specific campaign
            $totalRaised = DB::table('transactions')
                ->where('campaign_id', $campaign->id)
                ->where('status', 'completed')
                ->sum('amount');

            // Get distinct donor count from completed transactions for this campaign
            $donorCount = DB::table('transactions')
                ->where('campaign_id', $campaign->id)
                ->where('status', 'completed')
                ->distinct('donor_email')
                ->count();

            // Set a target amount (can be customized per campaign)
            $targetAmount = 100000;

            $campaignStats[] = [
                'id' => $campaign->id,
                'title' => $campaign->title,
                'description' => 'Support for ' . $campaign->disaster_type . ' in ' . $campaign->city . ', ' . $campaign->district,
                'disaster_id' => $campaign->disaster_id,
                'target_amount' => $targetAmount,
                'current_amount' => $totalRaised,
                'donors_count' => $donorCount,
            ];
        }

        return view('public.donate', [
            'campaigns' => $campaignStats,
        ]);
    }
}
