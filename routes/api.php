<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LibraryBranchController;
use App\Http\Controllers\API\LibraryController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\API\SectionController;
use App\Http\Controllers\Api\V1\BookItemController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\CategoryController;


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

    // Staff-only routes
    Route::middleware('role:staff')->group(function () {
        // e.g. staff dashboard, reports...
    });

    Route::middleware('role:student|librarian')->group(function () {
        Route::resource('loans', LoanController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('fines', FineController::class);
    });

    // Super‑admin or admin — full library management
    Route::middleware('role:super-admin|admin')->group(function () {
        Route::resource('libraries', LibraryController::class);
    });

    // Student-only routes (if any extra)
    Route::middleware('role:student')->group(function () {
        // Add student-exclusive routes here (if needed)
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
        Route::get('loans/{id}', [LoanController::class, 'show']);
        Route::resource('categories', CategoryController::class);
        Route::post('/categories/delete-multiple', [CategoryController::class, 'destroyMultiple']);
    });
});
