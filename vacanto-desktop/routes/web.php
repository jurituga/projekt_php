<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserReviewController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Company\ApplicationActionController;
use App\Http\Controllers\Company\ApplicationController as CompanyApplicationController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Company\JobPostingController as CompanyJobPostingController;
use App\Http\Controllers\Company\ProfileController as CompanyProfileController;
use App\Http\Controllers\CvDownloadController;
use App\Http\Controllers\Freelancer\DashboardController as FreelancerDashboardController;
use App\Http\Controllers\Freelancer\EarningsController as FreelancerEarningsController;
use App\Http\Controllers\Freelancer\ProfileController as FreelancerProfileController;
use App\Http\Controllers\Freelancer\ServiceController as FreelancerServiceController;
use App\Http\Controllers\Freelancer\ServiceRequestActionController;
use App\Http\Controllers\Freelancer\ServiceRequestController as FreelancerServiceRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\User\ApplicationController as UserApplicationController;
use App\Http\Controllers\User\CvController as UserCvController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\ServiceRequestController as UserServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/jobs', [JobPostingController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobPostingController::class, 'show'])->name('jobs.show');
Route::post('/jobs/{job}/apply', [JobPostingController::class, 'apply'])
    ->middleware(['auth', 'active', 'role:user'])
    ->name('jobs.apply');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/services/{service}/request', [ServiceController::class, 'request'])
    ->middleware(['auth', 'active', 'role:user,company'])
    ->name('services.request');

Route::get('/media/rating/{filename}', [MediaController::class, 'ratingImage'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('media.rating');

Route::get('/cvs/{cv}/download', CvDownloadController::class)
    ->middleware(['auth', 'active'])
    ->name('cvs.download');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware(['auth'])
    ->name('logout');

Route::middleware(['auth', 'active', 'role:user,company'])->group(function () {
    Route::get('/pay/{serviceRequest}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('/pay/{serviceRequest}/intent', [PaymentController::class, 'createIntent'])->name('payments.intent');
    Route::post('/pay/{serviceRequest}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::get('/rate/{serviceRequest}', [RatingController::class, 'create'])->name('ratings.create');
    Route::post('/rate/{serviceRequest}', [RatingController::class, 'store'])->name('ratings.store');
});

Route::middleware(['auth', 'active'])->prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [MessageController::class, 'inbox'])->name('inbox');
    Route::get('/poll', [MessageController::class, 'poll'])->name('poll');
    Route::get('/{user}', [MessageController::class, 'chat'])->name('chat');
    Route::post('/{user}', [MessageController::class, 'store'])->name('store');
});

Route::middleware(['auth', 'active'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/poll', [NotificationController::class, 'poll'])->name('poll');
    Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::get('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
});

Route::middleware(['auth', 'active', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::get('/cvs', [UserCvController::class, 'index'])->name('cvs.index');
    Route::post('/cvs', [UserCvController::class, 'store'])->name('cvs.store');
    Route::patch('/cvs/{cv}/default', [UserCvController::class, 'setDefault'])->name('cvs.default');
    Route::delete('/cvs/{cv}', [UserCvController::class, 'destroy'])->name('cvs.destroy');
    Route::get('/applications', [UserApplicationController::class, 'index'])->name('applications.index');
    Route::get('/service-requests', [UserServiceRequestController::class, 'index'])->name('service-requests.index');
});

Route::middleware(['auth', 'active', 'role:freelancer'])->prefix('freelancer')->name('freelancer.')->group(function () {
    Route::get('/dashboard', [FreelancerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [FreelancerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [FreelancerProfileController::class, 'update'])->name('profile.update');
    Route::get('/services', [FreelancerServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [FreelancerServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [FreelancerServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [FreelancerServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [FreelancerServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [FreelancerServiceController::class, 'destroy'])->name('services.destroy');
    Route::post('/services/{service}/slots', [FreelancerServiceController::class, 'addSlots'])->name('services.slots.store');
    Route::delete('/services/{service}/slots/{slot}', [FreelancerServiceController::class, 'removeSlot'])->name('services.slots.destroy');
    Route::get('/requests', [FreelancerServiceRequestController::class, 'index'])->name('requests.index');
    Route::get('/earnings', [FreelancerEarningsController::class, 'index'])->name('earnings.index');
    Route::post('/requests/action', ServiceRequestActionController::class)->name('requests.action');
});

Route::middleware(['auth', 'active', 'role:company'])->prefix('company')->name('company.')->group(function () {
    Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [CompanyProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [CompanyProfileController::class, 'update'])->name('profile.update');
    Route::get('/jobs', [CompanyJobPostingController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/create', [CompanyJobPostingController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [CompanyJobPostingController::class, 'store'])->name('jobs.store');
    Route::get('/jobs/{job}/edit', [CompanyJobPostingController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [CompanyJobPostingController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{job}', [CompanyJobPostingController::class, 'destroy'])->name('jobs.destroy');
    Route::get('/jobs/{job}/applications', [CompanyApplicationController::class, 'forJob'])->name('jobs.applications');
    Route::get('/applications', [CompanyApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications/action', ApplicationActionController::class)->name('applications.action');
});

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/review', [UserReviewController::class, 'show'])->name('users.review');
    Route::post('/users/action', [AdminUserController::class, 'action'])->name('users.action');
    Route::get('/documents/{type}/{filename}', AdminDocumentController::class)
        ->where('type', 'gov|cert')
        ->where('filename', '[A-Za-z0-9._-]+')
        ->name('documents.show');
    Route::get('/jobs', [AdminJobController::class, 'index'])->name('jobs.index');
    Route::delete('/jobs', [AdminJobController::class, 'destroy'])->name('jobs.destroy');
    Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
    Route::delete('/services', [AdminServiceController::class, 'destroy'])->name('services.destroy');
});
