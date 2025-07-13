<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\ProfileIsComplete;

Route::middleware('auth')->group(function () {
    Route::get('settings/profile-complete', [ProfileController::class, 'complete'])->name('profile.complete');
    Route::post('settings/profile-complete', [ProfileController::class, 'completeProfile'])->name('profile.complete.store');
    Route::middleware(ProfileIsComplete::class)->group(function () {
        // settings
        Route::redirect('settings', 'settings/profile');
        Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // password
        Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

        Route::get('settings/appearance', function () {
            return Inertia::render('settings/appearance');
        })->name('appearance');
    });
});
