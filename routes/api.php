<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('export')->group(function () {
        Route::post('/users', [UserController::class, 'export']);
        Route::post('/tickets', [TicketController::class, 'export']);
    });
});

//email=fohonof998%40polatrix.com

Route::group(['middleware' => ['cors', 'json',]], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('send-verification-email', [AuthController::class, 'sendVerificationEmail']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email');

    Route::prefix('password')->group(function () {
        Route::post('forgot', [AuthController::class, 'forgotPassword']);
        Route::post('token/verify', [AuthController::class, 'verifyForgotPasswordToken']);
        Route::post('reset', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum', 'verified')->group(function () {
        Route::get('me', [AuthController::class, 'getAuthUser']);
        Route::get('logout', [AuthController::class, 'logout']);

        // Users
        Route::post('user/{users}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::apiResource('users', UserController::class);


        Route::put('tickets/{ticket}/resolved', [TicketController::class, 'resolved']);
        Route::apiResource('tickets', TicketController::class);

        Route::get('tickets/{ticket}/replies', [TicketController::class, 'show']);
        Route::post('tickets/{ticket}/replies', [TicketReplyController::class, 'store']);


        Route::get("activity-log", [ActivityLogController::class, 'index']);

        // Delete Media ...
        Route::delete('media/{media}', [MediaController::class, 'destroy']);
    });
});
