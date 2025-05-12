<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LibraryBranchController;
use App\Http\Controllers\API\LibraryController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\API\SectionController;
use App\Http\Controllers\Api\V1\BookItemController;
use Illuminate\Support\Facades\Route;
// 🔓 Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// 🔐 Protected routes
Route::middleware(['auth:api'])->group(function () {
     Route::get('/book/item', [BookItemController::class, 'index']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User bookmarks routes
    Route::apiResource('bookmarks', App\Http\Controllers\API\V1\BookmarkController::class);
    
    // User notes routes
    Route::apiResource('notes', App\Http\Controllers\API\V1\NoteController::class);
    
    // User reading lists routes
    Route::apiResource('reading-lists', App\Http\Controllers\API\V1\ReadingListController::class);
    Route::post('reading-lists/{readingList}/add-book', [App\Http\Controllers\API\V1\ReadingListController::class, 'addBookItem']);
    Route::delete('reading-lists/{readingList}/remove-book', [App\Http\Controllers\API\V1\ReadingListController::class, 'removeBookItem']);
    
    // Recently viewed resources routes
    Route::get('recently-viewed', [App\Http\Controllers\API\V1\RecentlyViewedController::class, 'index']);
    Route::post('recently-viewed/track', [App\Http\Controllers\API\V1\RecentlyViewedController::class, 'trackView']);
    Route::delete('recently-viewed/clear', [App\Http\Controllers\API\V1\RecentlyViewedController::class, 'clearAll']);
    Route::delete('recently-viewed/{recentlyViewed}', [App\Http\Controllers\API\V1\RecentlyViewedController::class, 'destroy']);

// 📚 both Super Admin and admin - full access
    Route::middleware('role:super-admin|admin')->group(function () {
        Route::Resource('/libraries', LibraryController::class);
        Route::apiResource('/sections', SectionController::class);
        Route::resource('staff', StaffController::class);
        Route::post('staff/bulk', [StaffController::class, 'storeBulk']);
        Route::resource('students', StudentController::class);
        Route::post('/students/batch', [StudentController::class, 'batchStore']);
        Route::get('/users', [AuthController::class, 'allUsers']);
        Route::put('/user', [AuthController::class, 'updateUser']);
        Route::post('/user', [AuthController::class, 'changePassword']);


          });

    // 📚 Super Admin - full access
    Route::middleware('role:superadmin')->group(function () {
        Route::resource('/branches', LibraryBranchController::class);
         Route::resource('admins', AdminController::class);
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
