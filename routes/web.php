<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
|
| The public experience is a single continuous document. Chapters are
| addressable by fragment so navigation, deep links and the skip-link all
| resolve to the same page without a route explosion.
|
| When the CMS lands, HomeController's ContentRepository dependency is the
| only thing that changes — see App\Content\ContentRepository.
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/work', [HomeController::class, 'work'])->name('work.index');
Route::get('/work/{slug}', [HomeController::class, 'project'])->name('projects.show');
Route::get('/services', [HomeController::class, 'services'])->name('services.index');
Route::get('/studio', [HomeController::class, 'studio'])->name('studio');
Route::get('/insights', [HomeController::class, 'insightsIndex'])->name('insights.index');
Route::get('/insights/{slug}', [HomeController::class, 'insight'])->name('insights.show');
Route::get('/start-a-project', [HomeController::class, 'contact'])->name('contact');

Route::prefix('admin')->name('admin.')->group(function (): void {
	Route::middleware('admin.guest')->group(function (): void {
		Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
		Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
		Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('forgot-password');
		Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('forgot-password.store');
		Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('reset-password');
		Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('reset-password.store');
	});

	Route::middleware(['admin.auth', 'active'])->group(function (): void {
		Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
		Route::get('/', DashboardController::class)->name('dashboard');
		Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
		Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
		Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
	});
});
