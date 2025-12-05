<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('export')->group(function () {
        Route::post('/users', [UserController::class, 'export']);
        Route::post('/tickets', [TicketController::class, 'export']);
    });
});


Route::get('media/download/{media}', [MediaController::class, 'download']);

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

    Route::get('dashboard/statistics', [TicketController::class, 'statistics']);
    Route::post('tickets/{ticket}/review', [TicketController::class, 'review'])->name('tickets.review');
    Route::get('tickets/review-overview', [TicketController::class, 'overview']);
    Route::get('settings/logo', [SettingsController::class, 'getCompanyLogo']);

    Route::middleware('auth:sanctum', 'verified')->group(function () {
        Route::get('me', [AuthController::class, 'getAuthUser']);
        Route::get('logout', [AuthController::class, 'logout']);

        // Users
        Route::post('user/{users}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::apiResource('users', UserController::class);

        // Roles & Permissions
        Route::get('roles', [RolePermissionController::class, 'getRole']);
        Route::post('roles', [RolePermissionController::class, 'createOrUpdateRole']);
        Route::delete('roles/{id}', [RolePermissionController::class, 'deleteRole']);
        Route::get('permissions', [RolePermissionController::class, 'getPermission']);
        Route::post('permissions', [RolePermissionController::class, 'createPermission']);
        Route::delete('permissions/{id}', [RolePermissionController::class, 'deletePermission']);

        Route::apiResource('categories', CategoryController::class);

        Route::put('tickets/{ticket}/resolved', [TicketController::class, 'resolved']);
        Route::apiResource('tickets', TicketController::class);

        Route::get('new-tickets', [NotificationController::class, 'newTickets']);

        Route::get('tickets/{ticket}/replies', [TicketController::class, 'show']);
        Route::get('tickets/{ticket}/read', [TicketReplyController::class, 'read']);
        Route::post('tickets/{ticket}/replies', [TicketReplyController::class, 'store']);


        Route::get("activity-log", [ActivityLogController::class, 'index']);

        // Database Management (Admin Only)
        Route::prefix('database')->group(function () {
            Route::get('info', [DatabaseController::class, 'info']);
            Route::get('download', [DatabaseController::class, 'download']);
        });

        Route::post('settings/logo', [SettingsController::class, 'updateLogo']);

        // Delete Media ...
        Route::delete('media/{media}', [MediaController::class, 'destroy']);

        // route for employee import AND API resourse
        Route::post('employees/import', [EmployeeController::class, 'import']);
        Route::apiResource('employees', EmployeeController::class);
    });
});
