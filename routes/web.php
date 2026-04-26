<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\VolunteerController;
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

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

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
})->name('dashboard');

Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/weather', [App\Http\Controllers\AdminController::class, 'weather'])->name('admin.weather');

Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
Route::post('/admin/users', [App\Http\Controllers\AdminController::class, 'storeUser'])->name('admin.users.store');
Route::patch('/admin/users/{userId}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
Route::delete('/admin/users/{userId}', [App\Http\Controllers\AdminController::class, 'destroyUser'])->name('admin.users.destroy');

Route::get('/admin/disasters', [App\Http\Controllers\AdminController::class, 'disasters'])->name('admin.disasters');
Route::post('/admin/disasters', [App\Http\Controllers\AdminController::class, 'storeDisaster'])->name('admin.disasters.store');
Route::patch('/admin/disasters/{disasterId}', [App\Http\Controllers\AdminController::class, 'updateDisaster'])->name('admin.disasters.update');
Route::delete('/admin/disasters/{disasterId}', [App\Http\Controllers\AdminController::class, 'destroyDisaster'])->name('admin.disasters.destroy');

Route::get('/admin/volunteers', [App\Http\Controllers\AdminController::class, 'volunteers'])->name('admin.volunteers');
Route::patch('/admin/volunteers/{volunteerId}', [App\Http\Controllers\AdminController::class, 'updateVolunteer'])->name('admin.volunteers.update');
Route::post('/admin/volunteer-assignments', [App\Http\Controllers\AdminController::class, 'storeVolunteerAssignment'])->name('admin.volunteer-assignments.store');
Route::delete('/admin/volunteer-assignments/{assignmentId}', [App\Http\Controllers\AdminController::class, 'destroyVolunteerAssignment'])->name('admin.volunteer-assignments.destroy');

Route::get('/admin/resources', [App\Http\Controllers\AdminController::class, 'resources'])->name('admin.resources');
Route::post('/admin/resources', [App\Http\Controllers\AdminController::class, 'storeResource'])->name('admin.resources.store');
Route::patch('/admin/resources/{resourceId}', [App\Http\Controllers\AdminController::class, 'updateResource'])->name('admin.resources.update');
Route::delete('/admin/resources/{resourceId}', [App\Http\Controllers\AdminController::class, 'destroyResource'])->name('admin.resources.destroy');

Route::get('/admin/affected-people', [App\Http\Controllers\AdminController::class, 'affectedPeople'])->name('admin.affected-people');
Route::post('/admin/affected-people', [App\Http\Controllers\AdminController::class, 'storeAffectedPerson'])->name('admin.affected-people.store');
Route::patch('/admin/affected-people/{beneficiaryId}', [App\Http\Controllers\AdminController::class, 'updateAffectedPerson'])->name('admin.affected-people.update');
Route::delete('/admin/affected-people/{beneficiaryId}', [App\Http\Controllers\AdminController::class, 'destroyAffectedPerson'])->name('admin.affected-people.destroy');

Route::get('/admin/aid-requests', [App\Http\Controllers\AdminController::class, 'aidRequests'])->name('admin.aid-requests');
Route::post('/admin/aid-requests', [App\Http\Controllers\AdminController::class, 'storeAidRequest'])->name('admin.aid-requests.store');
Route::patch('/admin/aid-requests/{requestId}', [App\Http\Controllers\AdminController::class, 'updateAidRequest'])->name('admin.aid-requests.update');
Route::delete('/admin/aid-requests/{requestId}', [App\Http\Controllers\AdminController::class, 'destroyAidRequest'])->name('admin.aid-requests.destroy');

Route::get('/admin/disaster-submissions', [App\Http\Controllers\AdminController::class, 'disasterSubmissions'])->name('admin.disaster-submissions');
Route::get('/admin/disaster-submissions/{submissionId}', [App\Http\Controllers\AdminController::class, 'showDisasterSubmissionReview'])->name('admin.disaster-submissions.show');
Route::patch('/admin/disaster-submissions/{submissionId}', [App\Http\Controllers\AdminController::class, 'updateDisasterSubmission'])->name('admin.disaster-submissions.update');

Route::get('/official/dashboard', [OfficialController::class, 'dashboard'])->name('official.dashboard');
Route::get('/official/disasters', [OfficialController::class, 'disasters'])->name('official.disasters');
Route::get('/official/volunteers', [OfficialController::class, 'volunteers'])->name('official.volunteers');
Route::get('/official/resources', [OfficialController::class, 'resources'])->name('official.resources');
Route::get('/official/community-supports', [OfficialController::class, 'communitySupports'])->name('official.community-supports');
Route::get('/official/policies', [OfficialController::class, 'policies'])->name('official.policies');
Route::patch('/official/disasters/{disasterId}/status', [OfficialController::class, 'updateDisasterStatus'])->name('official.disasters.update-status');
Route::post('/official/volunteer-assignments', [OfficialController::class, 'storeVolunteerAssignment'])->name('official.volunteer-assignments.store');
Route::patch('/official/volunteer-assignments/{assignmentId}', [OfficialController::class, 'updateVolunteerAssignment'])->name('official.volunteer-assignments.update');
Route::post('/official/resource-requests', [OfficialController::class, 'storeResourceRequest'])->name('official.resource-requests.store');
Route::patch('/official/resource-requests/{requestId}', [OfficialController::class, 'updateResourceRequest'])->name('official.resource-requests.update');
Route::post('/official/resource-usage', [OfficialController::class, 'storeResourceUsage'])->name('official.resource-usage.store');
Route::post('/official/community-supports', [OfficialController::class, 'storeCommunitySupport'])->name('official.community-supports.store');
Route::patch('/official/community-supports/{beneficiaryId}', [OfficialController::class, 'updateCommunitySupport'])->name('official.community-supports.update');
Route::post('/official/policies', [OfficialController::class, 'storePolicy'])->name('official.policies.store');

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
