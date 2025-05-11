<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\LibraryBranchController;

// Public (no auth)
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    // Common to all authenticated users
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Staff-only routes
    Route::middleware('role:staff')->group(function () {
        // e.g. staff dashboard, reports...
    });

    // Student-only routes
    Route::middleware('role:student')->group(function () {
        // e.g. /loans, /fines, /categories
        Route::resource('loans',     LoanController::class);
        Route::resource('fines',     FineController::class);

    });

    // Super‑admin or admin — full library management
    Route::middleware('role:super-admin|admin')->group(function () {
        Route::resource('libraries', LibraryController::class);
    });

    // Super‑admin only — branch management
    Route::middleware('role:super-admin')->group(function () {
        Route::resource('branches',  LibraryBranchController::class);
    });

    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        // e.g. admin analytics, system settings...
    });

    // Librarian-only routes
    Route::middleware('role:librarian')->group(function () {
        Route::resource('categories',CategoryController::class);
        Route::post('/categories/delete-multiple', [CategoryController::class, 'destroyMultiple']);
        // e.g. check‑in/check‑out, shelf management...
    });
});
