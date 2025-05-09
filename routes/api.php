<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LibraryBranchController;
use App\Http\Controllers\API\LibraryController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\API\SectionController;
use Illuminate\Support\Facades\Route;

// 🔓 Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// 🔐 Protected routes
Route::middleware(['auth:api'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

// 📚 both Super Admin and admin - full access
    Route::middleware('role:super-admin|admin')->group(function () {
        Route::Resource('/libraries', LibraryController::class);
        Route::apiResource('/sections', SectionController::class);

    });

    // 📚 Super Admin - full access
    Route::middleware('role:super-admin')->group(function () {
        Route::resource('/branches', LibraryBranchController::class);
        Route::resource('staff', StaffController::class);
        Route::resource('students', StudentController::class);

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
        // Route::resource('staff', StaffController::class);
    });

    // 🎓 Student
    Route::middleware('role:student')->group(function () {
        // Route::resource('students', StudentController::class);
    });
});
