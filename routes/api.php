<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::middleware('role:student')->group(function () {

    });
    Route::middleware('role:staff')->group(function () {

    });
    Route::middleware('role:liberarian')->group(function () {

    });

    Route::middleware('role:admin')->group(function () {

    });

});

// public routes

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
