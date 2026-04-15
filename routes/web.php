<?php

use App\Http\Controllers\AuthController;
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

    if (auth()->user()->role === 'volunteer') {
        return redirect()->route('volunteer.dashboard');
    }

    return view('dashboard');
})->name('dashboard');

Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');

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

Route::get('/volunteer/dashboard', [VolunteerController::class, 'dashboard'])->name('volunteer.dashboard');
Route::get('/volunteer/assigned-tasks', [VolunteerController::class, 'tasks'])->name('volunteer.tasks');
Route::patch('/volunteer/assigned-tasks/{assignmentId}', [VolunteerController::class, 'updateTaskHours'])->name('volunteer.tasks.update-hours');
Route::get('/volunteer/profile', [VolunteerController::class, 'profile'])->name('volunteer.profile');
Route::patch('/volunteer/profile', [VolunteerController::class, 'updateProfile'])->name('volunteer.profile.update');
Route::get('/volunteer/aid-request', [VolunteerController::class, 'aidRequests'])->name('volunteer.aid-requests');
Route::post('/volunteer/aid-request', [VolunteerController::class, 'storeAidRequest'])->name('volunteer.aid-requests.store');
Route::get('/volunteer/disaster-data', [VolunteerController::class, 'disasterData'])->name('volunteer.disaster-data');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
