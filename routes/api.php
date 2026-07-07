<?php

use App\Http\Controllers\Analytics\DailyPostAnalyticsController;
use App\Http\Controllers\Analytics\PostAnalyticsSummaryController;
use App\Http\Controllers\Auth\CurrentUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Posts\PostController;
use App\Http\Controllers\Posts\TopViewedPostsController;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class)->middleware('throttle:10,1');
Route::post('/login', LoginController::class)->middleware('throttle:10,1');

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/top-viewed', TopViewedPostsController::class);
Route::get('/posts/{post}', [PostController::class, 'show'])->middleware('throttle:120,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', CurrentUserController::class);
    Route::post('/logout', LogoutController::class);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{post}/analytics/daily', DailyPostAnalyticsController::class);
    Route::get('/posts/{post}/analytics/summary', PostAnalyticsSummaryController::class);
});
