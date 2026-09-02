<?php

use App\Http\Controllers\HomeController;
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
Route::get('/insights', [HomeController::class, 'insightsIndex'])->name('insights.index');
Route::get('/insights/{slug}', [HomeController::class, 'insight'])->name('insights.show');
Route::get('/start-a-project', [HomeController::class, 'contact'])->name('contact');
