<?php

use App\Http\Controllers\CatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpeciesController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/{user}', [ProfileController::class, 'showUser'])->name('profile.user');

    Route::get('/catches/create', [CatchController::class, 'create'])->name('catches.create');
    Route::post('/catches', [CatchController::class, 'store'])->name('catches.store');
    Route::get('/catches/{catch}', [CatchController::class, 'show'])->name('catches.show');

    Route::get('/species/{species:slug}', [SpeciesController::class, 'show'])->name('species.show');

    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
});

require __DIR__.'/settings.php';
