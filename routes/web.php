<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ShurjopayPaymentController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes (no authentication required)
Route::get('/', [PublicController::class, 'index'])->name('public.home');

// Protected public routes (authentication required)
Route::middleware('auth')->group(function () {
    Route::get('/report-disaster', [PublicController::class, 'reportDisaster'])->name('public.report-disaster');
    Route::post('/report-disaster', [PublicController::class, 'storeDisasterReport'])->name('public.report-disaster.store');
    Route::get('/request-help', [PublicController::class, 'requestHelp'])->name('public.request-help');
    Route::post('/request-help', [PublicController::class, 'storeHelpRequest'])->name('public.request-help.store');
    Route::get('/alerts', [PublicController::class, 'viewAlerts'])->name('public.alerts');
    Route::get('/disasters', [PublicController::class, 'viewDisasters'])->name('public.disasters');
    Route::get('/donate', [PublicController::class, 'donate'])->name('public.donate');
});

// Shurjopay Payment Gateway Routes (requires authentication)
Route::middleware('auth')->prefix('/payment/shurjopay')->group(function () {
    // Specific routes first (more specific before generic)
    Route::post('/create', [ShurjopayPaymentController::class, 'createPaymentSession'])->name('payment.shurjopay-create');
    Route::match(['get', 'post'], '/verify', [ShurjopayPaymentController::class, 'verifyPayment'])->name('payment.shurjopay-verify');
    Route::post('/ipn', [ShurjopayPaymentController::class, 'handleIPN'])->name('payment.shurjopay-ipn');
    Route::match(['get', 'post'], '/status/{orderId?}', [ShurjopayPaymentController::class, 'checkPaymentStatus'])->name('payment.shurjopay-status');
    Route::get('/confirmation/{orderId}', [ShurjopayPaymentController::class, 'showConfirmation'])->name('payment.confirmation');
    // Generic route last (catch-all)
    Route::get('/{campaignId}', [ShurjopayPaymentController::class, 'showPaymentForm'])->name('payment.shurjopay-form');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if (auth()->user()->role === 'official') {
        return redirect()->route('official.dashboard');
    }

    if (auth()->user()->role === 'volunteer') {
        return redirect()->route('volunteer.dashboard');
    }

    return view('dashboard');
})->name('dashboard')->middleware('auth');

// Admin Routes (requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/weather', [AdminController::class, 'weather'])->name('admin.weather');

Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
Route::patch('/admin/users/{userId}', [AdminController::class, 'updateUser'])->name('admin.users.update');
Route::delete('/admin/users/{userId}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

Route::get('/admin/disasters', [AdminController::class, 'disasters'])->name('admin.disasters');
Route::post('/admin/disasters', [AdminController::class, 'storeDisaster'])->name('admin.disasters.store');
Route::patch('/admin/disasters/{disasterId}', [AdminController::class, 'updateDisaster'])->name('admin.disasters.update');
Route::delete('/admin/disasters/{disasterId}', [AdminController::class, 'destroyDisaster'])->name('admin.disasters.destroy');

Route::get('/admin/volunteers', [AdminController::class, 'volunteers'])->name('admin.volunteers');
Route::patch('/admin/volunteers/{volunteerId}', [AdminController::class, 'updateVolunteer'])->name('admin.volunteers.update');
Route::post('/admin/volunteer-assignments', [AdminController::class, 'storeVolunteerAssignment'])->name('admin.volunteer-assignments.store');
Route::delete('/admin/volunteer-assignments/{assignmentId}', [AdminController::class, 'destroyVolunteerAssignment'])->name('admin.volunteer-assignments.destroy');

Route::get('/admin/resources', [AdminController::class, 'resources'])->name('admin.resources');
Route::post('/admin/resources', [AdminController::class, 'storeResource'])->name('admin.resources.store');
Route::patch('/admin/resources/{resourceId}', [AdminController::class, 'updateResource'])->name('admin.resources.update');
Route::delete('/admin/resources/{resourceId}', [AdminController::class, 'destroyResource'])->name('admin.resources.destroy');

Route::get('/admin/affected-people', [AdminController::class, 'affectedPeople'])->name('admin.affected-people');
Route::post('/admin/affected-people', [AdminController::class, 'storeAffectedPerson'])->name('admin.affected-people.store');
Route::patch('/admin/affected-people/{beneficiaryId}', [AdminController::class, 'updateAffectedPerson'])->name('admin.affected-people.update');
Route::delete('/admin/affected-people/{beneficiaryId}', [AdminController::class, 'destroyAffectedPerson'])->name('admin.affected-people.destroy');

Route::get('/admin/aid-requests', [AdminController::class, 'aidRequests'])->name('admin.aid-requests');
Route::post('/admin/aid-requests', [AdminController::class, 'storeAidRequest'])->name('admin.aid-requests.store');
Route::patch('/admin/aid-requests/{requestId}', [AdminController::class, 'updateAidRequest'])->name('admin.aid-requests.update');
Route::delete('/admin/aid-requests/{requestId}', [AdminController::class, 'destroyAidRequest'])->name('admin.aid-requests.destroy');

Route::get('/admin/donations', [AdminController::class, 'donations'])->name('admin.donations');
Route::post('/admin/donations', [AdminController::class, 'storeDonation'])->name('admin.donations.store');
Route::patch('/admin/donations/{donationId}', [AdminController::class, 'updateDonation'])->name('admin.donations.update');
Route::delete('/admin/donations/{donationId}', [AdminController::class, 'destroyDonation'])->name('admin.donations.destroy');
Route::get('/admin/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');

Route::get('/admin/disaster-submissions', [AdminController::class, 'disasterSubmissions'])->name('admin.disaster-submissions');
Route::get('/admin/disaster-submissions/{submissionId}', [AdminController::class, 'showDisasterSubmissionReview'])->name('admin.disaster-submissions.show');
Route::patch('/admin/disaster-submissions/{submissionId}', [AdminController::class, 'updateDisasterSubmission'])->name('admin.disaster-submissions.update');

Route::get('/admin/public-disaster-reports', [AdminController::class, 'publicDisasterReports'])->name('admin.public-disaster-reports');
Route::get('/admin/public-disaster-reports/{reportId}', [AdminController::class, 'reviewDisasterReport'])->name('admin.public-disaster-reports.show');
Route::patch('/admin/public-disaster-reports/{reportId}', [AdminController::class, 'updateDisasterReportStatus'])->name('admin.public-disaster-reports.update');

Route::get('/admin/public-help-requests', [AdminController::class, 'publicHelpRequests'])->name('admin.public-help-requests');
Route::get('/admin/public-help-requests/{requestId}', [AdminController::class, 'reviewHelpRequest'])->name('admin.public-help-requests.show');
Route::patch('/admin/public-help-requests/{requestId}', [AdminController::class, 'updateHelpRequestStatus'])->name('admin.public-help-requests.update');

Route::get('/official/dashboard', [OfficialController::class, 'dashboard'])->name('official.dashboard');
Route::get('/official/disasters', [OfficialController::class, 'disasters'])->name('official.disasters');
Route::get('/official/volunteers', [OfficialController::class, 'volunteers'])->name('official.volunteers');
Route::get('/official/resources', [OfficialController::class, 'resources'])->name('official.resources');
Route::get('/official/community-supports', [OfficialController::class, 'communitySupports'])->name('official.community-supports');
Route::get('/official/policies', [OfficialController::class, 'policies'])->name('official.policies');
Route::get('/official/volunteer-submissions', [OfficialController::class, 'volunteerSubmissions'])->name('official.volunteer-submissions');
Route::get('/official/volunteer-submissions/{submissionId}', [OfficialController::class, 'showVolunteerSubmission'])->name('official.volunteer-submissions.show');
Route::patch('/official/volunteer-submissions/{submissionId}', [OfficialController::class, 'updateVolunteerSubmission'])->name('official.volunteer-submissions.update');
Route::patch('/official/disasters/{disasterId}/status', [OfficialController::class, 'updateDisasterStatus'])->name('official.disasters.update-status');
Route::post('/official/volunteer-assignments', [OfficialController::class, 'storeVolunteerAssignment'])->name('official.volunteer-assignments.store');
Route::patch('/official/volunteer-assignments/{assignmentId}', [OfficialController::class, 'updateVolunteerAssignment'])->name('official.volunteer-assignments.update');
Route::post('/official/resource-requests', [OfficialController::class, 'storeResourceRequest'])->name('official.resource-requests.store');
Route::patch('/official/resource-requests/{requestId}', [OfficialController::class, 'updateResourceRequest'])->name('official.resource-requests.update');
Route::post('/official/resource-usage', [OfficialController::class, 'storeResourceUsage'])->name('official.resource-usage.store');
Route::post('/official/community-supports', [OfficialController::class, 'storeCommunitySupport'])->name('official.community-supports.store');
Route::patch('/official/community-supports/{beneficiaryId}', [OfficialController::class, 'updateCommunitySupport'])->name('official.community-supports.update');
Route::post('/official/policies', [OfficialController::class, 'storePolicy'])->name('official.policies.store');

Route::get('/official/donations', [OfficialController::class, 'donations'])->name('official.donations');
Route::post('/official/donations', [OfficialController::class, 'storeDonation'])->name('official.donations.store');
Route::patch('/official/donations/{donationId}', [OfficialController::class, 'updateDonation'])->name('official.donations.update');
Route::delete('/official/donations/{donationId}', [OfficialController::class, 'destroyDonation'])->name('official.donations.destroy');

Route::get('/official/transactions', [OfficialController::class, 'transactions'])->name('official.transactions');

Route::get('/official/public-disaster-reports', [OfficialController::class, 'publicDisasterReports'])->name('official.public-disaster-reports');
Route::get('/official/public-disaster-reports/{reportId}', [OfficialController::class, 'reviewDisasterReport'])->name('official.public-disaster-reports.show');
Route::get('/official/public-help-requests', [OfficialController::class, 'publicHelpRequests'])->name('official.public-help-requests');
Route::patch('/official/public-help-requests/{requestId}', [OfficialController::class, 'approveHelpRequest'])->name('official.public-help-requests.update');

Route::get('/volunteer/dashboard', [VolunteerController::class, 'dashboard'])->name('volunteer.dashboard');
Route::get('/volunteer/assigned-tasks', [VolunteerController::class, 'tasks'])->name('volunteer.tasks');
Route::patch('/volunteer/assigned-tasks/{assignmentId}', [VolunteerController::class, 'updateTaskHours'])->name('volunteer.tasks.update-hours');
Route::post('/volunteer/assigned-tasks/{assignmentId}/accept', [VolunteerController::class, 'acceptTask'])->name('volunteer.tasks.accept');
Route::get('/volunteer/profile', [VolunteerController::class, 'profile'])->name('volunteer.profile');
Route::patch('/volunteer/profile', [VolunteerController::class, 'updateProfile'])->name('volunteer.profile.update');
Route::get('/volunteer/aid-request', [VolunteerController::class, 'aidRequests'])->name('volunteer.aid-requests');
Route::post('/volunteer/aid-request', [VolunteerController::class, 'storeAidRequest'])->name('volunteer.aid-requests.store');
Route::get('/volunteer/disaster-data', [VolunteerController::class, 'disasterData'])->name('volunteer.disaster-data');
Route::get('/volunteer/disaster-submissions', [VolunteerController::class, 'showDisasterSubmissions'])->name('volunteer.disaster-submissions');
Route::get('/volunteer/disaster-submissions/create', [VolunteerController::class, 'showCreateSubmissionForm'])->name('volunteer.disaster-submissions.create');
Route::post('/volunteer/disaster-submissions', [VolunteerController::class, 'storeDisasterSubmission'])->name('volunteer.disaster-submissions.store');
Route::get('/volunteer/disaster-submissions/{submissionId}', [VolunteerController::class, 'showDisasterSubmissionDetail'])->name('volunteer.disaster-submissions.show');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});