<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('login', [AuthController::class, 'login'])->name('login');

Route::prefix('password')->group(function () {
    Route::post('forgot', [AuthController::class, 'forgotPassword']);
    Route::post('token/verify', [AuthController::class, 'verifyForgotPasswordToken']);
    Route::post('reset', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('auth-user', [AuthController::class, 'getAuthUser']);
    Route::get('logout', [AuthController::class, 'logout']);

    // Users
    Route::apiResource('users', UserController::class);


    Route::get("activity-log", [ActivityLogController::class, 'index']);

    // Delete Media ...
    Route::delete('media/{media}', [MediaController::class, 'destroy']);
});
