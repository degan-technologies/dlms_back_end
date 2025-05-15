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
Route::get('/home-page-data', [App\Http\Controllers\Api\V1\HomePageController::class, 'getHomePageData']);

// 🔐 Protected routes
Route::middleware(['auth:api'])->group(function () {
    
     Route::get('/book/item', [BookItemController::class, 'index']);
     Route::get('/book/item/{bookItem}/related', [BookItemController::class, 'related']);
     Route::get('/book-items/new-arrivals', [BookItemController::class, 'newArrivals']);
     Route::get('/book-items/{bookItem}', [BookItemController::class, 'show']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User bookmarks routes
    Route::apiResource('bookmarks', App\Http\Controllers\API\V1\BookmarkController::class);
    
    // User notes routes
    Route::apiResource('notes', App\Http\Controllers\API\V1\NoteController::class);
    Route::get('notes-search', [App\Http\Controllers\API\V1\NoteController::class, 'search']);
    
    // User reading lists routes
    Route::apiResource('reading-lists', App\Http\Controllers\API\V1\ReadingListController::class);
    Route::post('reading-lists/{readingList}/add-book', [App\Http\Controllers\API\V1\ReadingListController::class, 'addBookItem']);
    Route::delete('reading-lists/{readingList}/remove-book', [App\Http\Controllers\API\V1\ReadingListController::class, 'removeBookItem']);
    
    // Recently viewed resources routes
    Route::get('recently-viewed', [App\Http\Controllers\API\V1\RecentlyViewedController::class, 'index']);
    Route::post('recently-viewed/track', [App\Http\Controllers\API\V1\RecentlyViewedController::class, 'trackView']);
    Route::delete('recently-viewed/clear', [App\Http\Controllers\API\V1\RecentlyViewedController::class, 'clearAll']);
    Route::delete('recently-viewed/{recentlyViewed}', [App\Http\Controllers\API\V1\RecentlyViewedController::class, 'destroy']);
    
    // AI Chat with books routes
    Route::get('book-items/{bookItem}/chat-messages', [App\Http\Controllers\API\V1\ChatMessageController::class, 'index']);
    Route::post('book-items/{bookItem}/chat-messages', [App\Http\Controllers\API\V1\ChatMessageController::class, 'store']);
    Route::get('chat-messages/{chatMessage}', [App\Http\Controllers\API\V1\ChatMessageController::class, 'show']);
    Route::delete('chat-messages/{chatMessage}', [App\Http\Controllers\API\V1\ChatMessageController::class, 'destroy']);

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
        // Resource management routes
        Route::apiResource('books', App\Http\Controllers\API\V1\BookController::class);
        Route::apiResource('book-items', App\Http\Controllers\API\V1\BookItemController::class);
        Route::apiResource('ebooks', App\Http\Controllers\API\V1\EBookController::class);
        Route::apiResource('publishers', App\Http\Controllers\API\V1\PublisherController::class);
        Route::apiResource('asset-types', App\Http\Controllers\API\V1\AssetTypeController::class);
        Route::apiResource('shelves', App\Http\Controllers\API\V1\ShelfController::class);
        Route::apiResource('tags', App\Http\Controllers\API\V1\TagController::class);
        Route::apiResource('grades', App\Http\Controllers\API\V1\GradeController::class);
        Route::apiResource('languages', App\Http\Controllers\API\V1\LanguageController::class);
        Route::apiResource('categories', App\Http\Controllers\API\V1\CategoryController::class);
        Route::apiResource('other-assets', App\Http\Controllers\API\V1\OtherAssetController::class);
    });

    // 👩‍💼 Librarian
    Route::middleware('role:librarian')->group(function () {
        // librarian routes
        Route::apiResource('books', App\Http\Controllers\API\V1\BookController::class)->only(['index', 'show']);
        Route::apiResource('book-items', App\Http\Controllers\API\V1\BookItemController::class)->only(['index', 'show']);
        Route::apiResource('ebooks', App\Http\Controllers\API\V1\EBookController::class)->only(['index', 'show']);
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
