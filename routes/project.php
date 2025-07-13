<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\ProfileIsComplete;

Route::middleware('auth')->group(function () {
    Route::middleware(ProfileIsComplete::class)->group(function () {
        // settings
        Route::get('invite/project-members',[ProjectController::class, 'inviteStartUpProjectMember'])->name('startup.project.invite');
        Route::post('invite/project-members',[ProjectController::class, 'inviteStartUpProjectMemberSave'])->name('startup.project.invite.store');
    });
});