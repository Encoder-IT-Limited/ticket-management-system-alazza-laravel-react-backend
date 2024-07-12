<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('export')->group(function () {
        Route::get('/user', [UserController::class, 'export']);
        Route::get('/tickets', [TicketController::class, 'export']);
    });
});

Route::group(['middleware' => ['cors', 'json',]], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::prefix('password')->group(function () {
        Route::post('forgot', [AuthController::class, 'forgotPassword']);
        Route::post('token/verify', [AuthController::class, 'verifyForgotPasswordToken']);
        Route::post('reset', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'getAuthUser']);
        Route::get('logout', [AuthController::class, 'logout']);

        // Users
        Route::apiResource('users', UserController::class);
        Route::put('ticket/{ticket}/resolved', [TicketController::class, 'resolved']);
        Route::apiResource('tickets', TicketController::class);


        Route::get("activity-log", [ActivityLogController::class, 'index']);

        // Delete Media ...
        Route::delete('media/{media}', [MediaController::class, 'destroy']);
    });
});
