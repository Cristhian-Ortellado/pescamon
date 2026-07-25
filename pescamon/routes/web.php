<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\CatchController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpeciesController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');

    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/{user}', [ProfileController::class, 'showUser'])->name('profile.user');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    Route::get('/catches/create', [CatchController::class, 'create'])->name('catches.create');
    Route::post('/catches', [CatchController::class, 'store'])->name('catches.store');
    Route::get('/catches/{catch}', [CatchController::class, 'show'])->name('catches.show');
    Route::post('/catches/{catch}/like', [LikeController::class, 'toggle'])->name('catches.like');
    Route::post('/catches/{catch}/comments', [CommentController::class, 'store'])->name('catches.comments.store');

    Route::get('/species/{species:slug}', [SpeciesController::class, 'show'])->name('species.show');

    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.readOne');
});

require __DIR__.'/settings.php';
