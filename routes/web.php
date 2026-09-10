<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InsightController;
use App\Http\Controllers\Admin\IntakeOptionController;
use App\Http\Controllers\Admin\ProcessStageController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
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
| HomeController depends on ContentRepository and nothing else. Which
| implementation answers — the static files or the CMS database — is a
| config switch, not a change to any route or template below.
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

/*
|--------------------------------------------------------------------------
| Admin — the FORMIVA workspace
|--------------------------------------------------------------------------
|
| Authorisation is not expressed here. Every controller action asks its
| policy, so a route is reachable exactly when the signed-in user's role
| allows the action behind it — one place to read the rules rather than two
| that can disagree. The middleware below establishes who is asking; the
| policies decide what they may do.
|
*/

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

		// ---- Content ---------------------------------------------------

		Route::post('/projects/{project}/move', [ProjectController::class, 'move'])->name('projects.move');
		Route::post('/projects/{project}/featured', [ProjectController::class, 'toggleFeatured'])->name('projects.featured');
		Route::post('/projects/publish/{project}', [ProjectController::class, 'publish'])->name('projects.publish');
		Route::get('/projects/{project}/preview', [ProjectController::class, 'preview'])->name('projects.preview');
		Route::resource('projects', ProjectController::class)->except('show');

		Route::post('/services/{service}/move', [ServiceController::class, 'move'])->name('services.move');
		Route::post('/services/publish/{service}', [ServiceController::class, 'publish'])->name('services.publish');
		Route::put('/services/update/{service}', [ServiceController::class, 'update'])->name('services.update');
		Route::resource('services', ServiceController::class)->except(['show', 'update']);

		Route::post('/insights/{insight}/publish', [InsightController::class, 'publish'])->name('insights.publish');
		Route::resource('insights', InsightController::class)->except('show');

		/*
		 | ---- About -------------------------------------------------
		 |
		 | Everything that explains who FORMIVA is, under one prefix: the
		 | studio story and positions, the people, the process, the proof
		 | and the numbers. The tables behind them are unchanged and each
		 | keeps its own route names — this is one area in the interface,
		 | not one table in the database.
		 */
		Route::prefix('about')->group(function (): void {
			Route::get('/', [AboutController::class, 'overview'])->name('about.overview');
			Route::put('/', [AboutController::class, 'updateOverview'])->name('about.overview.update');
			Route::get('/stats', [AboutController::class, 'stats'])->name('about.stats');
			Route::put('/stats', [AboutController::class, 'updateStats'])->name('about.stats.update');

			Route::post('/testimonials/{testimonial}/move', [TestimonialController::class, 'move'])->name('testimonials.move');
			Route::post('/testimonials/{testimonial}/toggle', [TestimonialController::class, 'toggle'])->name('testimonials.toggle');
			Route::resource('testimonials', TestimonialController::class)->except('show');

			Route::post('/team/{team_member}/move', [TeamMemberController::class, 'move'])->name('team.move');
			Route::post('/team/{team_member}/toggle', [TeamMemberController::class, 'toggle'])->name('team.toggle');
			Route::resource('team', TeamMemberController::class)->parameters(['team' => 'team_member'])->except('show');

			Route::put('/process/framing', [ProcessStageController::class, 'framing'])->name('process.framing');
			Route::post('/process/{stage}/move', [ProcessStageController::class, 'move'])->name('process.move');
			Route::resource('process', ProcessStageController::class)->parameters(['process' => 'stage'])->except('show');
		});

		// ---- Configuration ---------------------------------------------

		Route::post('/categories/{category}/move', [CategoryController::class, 'move'])->name('categories.move');
		Route::resource('categories', CategoryController::class)->except('show');

		Route::post('/intake/{intake_option}/move', [IntakeOptionController::class, 'move'])->name('intake.move');
		Route::post('/intake/{intake_option}/toggle', [IntakeOptionController::class, 'toggle'])->name('intake.toggle');
		Route::resource('intake', IntakeOptionController::class)->parameters(['intake' => 'intake_option'])->except('show');

		Route::get('/settings', [SiteSettingsController::class, 'edit'])->name('settings.edit');
		Route::put('/settings', [SiteSettingsController::class, 'update'])->name('settings.update');

		// ---- System ----------------------------------------------------

		Route::resource('users', UserController::class)->except('show');
	});
});
