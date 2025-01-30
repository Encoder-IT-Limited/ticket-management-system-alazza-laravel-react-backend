<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    echo Hash::make('12345678');
    echo "<h1>404 Not Found.</h1>";
//    return view('welcome');
});



