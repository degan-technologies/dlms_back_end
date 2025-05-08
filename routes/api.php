<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LibraryBranchController;
use App\Http\Controllers\API\LibraryController;
use Illuminate\Support\Facades\Route;

// 🔓 Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// 🔐 Protected routes
Route::middleware(['auth:api'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

// 📚 both Super Admin and admin - full access
    Route::middleware('role:super-admin|admin')->group(function () {
        Route::Resource('/libraries', LibraryController::class);

    });

    // 📚 Super Admin - full access
    Route::middleware('role:super-admin')->group(function () {
        Route::Resource('/branches', LibraryBranchController::class);

    });

    // 📖 Admin-only access
    Route::middleware('role:admin')->group(function () {

    });

    // 👩‍💼 Librarian
    Route::middleware('role:librarian')->group(function () {
        // librarian routes
    });

    // 👨‍🏫 Staff
    Route::middleware('role:staff')->group(function () {
        // staff routes
    });

    // 🎓 Student
    Route::middleware('role:student')->group(function () {
        // student routes
    });
});
