<?php

use App\Http\Controllers\GoalController;
use App\Http\Controllers\JWTAuthController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [JWTAuthController::class, 'register'])->name('auth.register');
    Route::post('login', [JWTAuthController::class, 'login'])->name('auth.login');
});

// Protected routes
Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::get('profile', [UserController::class, 'showProfile'])->name('auth.profile');
    Route::post('logout', [JWTAuthController::class, 'logout'])->name('auth.logout');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('auth.profile.update');
    Route::post('profile/upload-image', [UserController::class, 'uploadImage'])->name('auth.profile.upload.image');
});

// Goals routes (protected)
Route::middleware('auth:api')->group(function () {
    Route::get('goals/all', [GoalController::class, 'getAllGoals'])->name('goals.all');

    // Goals
    Route::apiResource('goals', GoalController::class);

    // Milestones
    Route::get('goals/{goal_id}/milestones', [MilestoneController::class, 'index']);
    Route::post('goals/{goal_id}/milestones', [MilestoneController::class, 'store']);
    Route::get('goals/{goal_id}/milestones/{milestone_id}', [MilestoneController::class, 'show']);
    Route::put('goals/{goal_id}/milestones/{milestone_id}', [MilestoneController::class, 'update']);
    Route::delete('goals/{goal_id}/milestones/{milestone_id}', [MilestoneController::class, 'destroy']);
});
