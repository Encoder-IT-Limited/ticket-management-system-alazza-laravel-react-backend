<?php

use App\Http\Controllers\AuthController;
use App\Models\Services\TicketService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    echo Hash::make('12345678');
    echo "<h1>404 Not Found.</h1>";
    //    return view('welcome');
});

Route::get('/export', function () {
    $start = now()->startOfMonth()->subMonth()->toDateString();
    $end = now()->subMonth()->endOfMonth()->toDateString();
    $fileInfo = (new TicketService())->processReport($start, $end, false);
    $subject = "Monthly Report: {$start} to {$end}";
    $message = "Please find attached the monthly report for the period from {$start} to {$end}.";

    $users = User::whereHas('permissions', function ($q) {
        $q->where('slug', 'monthly-reports');
    })->select('id', 'name', 'email')->get();

    return response()->json([
        'start' => $start,
        'end' => $end,
        'fileInfo' => $fileInfo,
        'subject' => $subject,
        'message' => $message,
        'users' => $users,
    ]);
});
