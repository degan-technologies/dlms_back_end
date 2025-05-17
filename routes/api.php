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
use App\Http\Controllers\API\V1\BookController;
use App\Http\Controllers\API\V1\EBookController;
use App\Http\Controllers\API\V1\PublisherController;
use App\Http\Controllers\API\V1\AssetTypeController;
use App\Http\Controllers\API\V1\GradeController;
use App\Http\Controllers\API\V1\LanguageController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\OtherAssetController;
use App\Http\Controllers\API\V1\ChatMessageController;
use App\Http\Controllers\API\V1\LoanController;
use App\Http\Controllers\API\V1\BookmarkController;
use App\Http\Controllers\API\V1\NoteController;
use App\Http\Controllers\API\V1\ReadingListController;
use App\Http\Controllers\API\V1\RecentlyViewedController;
use App\Http\Controllers\API\V1\HomePageController;
// 🔓 Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/home-page-data', [HomePageController::class, 'getHomePageData']);

// 🔐 Protected routes
Route::middleware(['auth:api'])->group(function () {

    Route::get('/book/item', [BookItemController::class, 'index']);
    Route::get('/book/item/{bookItem}/related', [BookItemController::class, 'related']);
    Route::get('/book-items/new-arrivals', [BookItemController::class, 'newArrivals']);
    Route::get('/book-items/{bookItem}', [BookItemController::class, 'show']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // User bookmarks routes
    Route::apiResource('bookmarks', BookmarkController::class);

    // User notes routes
    Route::apiResource('notes', NoteController::class);
    Route::get('notes-search', [NoteController::class, 'search']);

    // User reading lists routes
    Route::apiResource('reading-lists', ReadingListController::class);
    Route::post('reading-lists/{readingList}/add-book', [ReadingListController::class, 'addBookItem']);
    Route::delete('reading-lists/{readingList}/remove-book', [ReadingListController::class, 'removeBookItem']);

    // Recently viewed resources routes
    Route::get('recently-viewed', [RecentlyViewedController::class, 'index']);
    Route::post('recently-viewed/track', [RecentlyViewedController::class, 'trackView']);
    Route::delete('recently-viewed/clear', [RecentlyViewedController::class, 'clearAll']);
    Route::delete('recently-viewed/{recentlyViewed}', [RecentlyViewedController::class, 'destroy']);

    // AI Chat with books routes
    Route::get('book-items/{bookItem}/chat-messages', [ChatMessageController::class, 'index']);
    Route::post('book-items/{bookItem}/chat-messages', [ChatMessageController::class, 'store']);
    Route::get('chat-messages/{chatMessage}', [ChatMessageController::class, 'show']);
    Route::delete('chat-messages/{chatMessage}', [ChatMessageController::class, 'destroy']);

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
        // AI Chat with books routes
        Route::get('book-items/{bookItem}/chat-messages', [ChatMessageController::class, 'index']);
        Route::post('book-items/{bookItem}/chat-messages', [ChatMessageController::class, 'store']);
        Route::get('chat-messages/{chatMessage}', [ChatMessageController::class, 'show']);
        Route::delete('chat-messages/{chatMessage}', [ChatMessageController::class, 'destroy']);
    });

    // 📚 Super Admin - full access
    Route::middleware('role:superadmin')->group(function () {
        Route::resource('/branches', LibraryBranchController::class);
        Route::resource('admins', AdminController::class);
    });

    // Admin-only routes
    Route::middleware('role:admin|librarian')->group(function () {
        // Resource management routes
        Route::apiResource('books', BookController::class);
        Route::apiResource('book-items', BookItemController::class);
        Route::apiResource('ebooks', EBookController::class);
        Route::apiResource('publishers', PublisherController::class);
        Route::apiResource('asset-types', AssetTypeController::class);
        Route::apiResource('grades', GradeController::class);
        Route::apiResource('languages', LanguageController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('other-assets', OtherAssetController::class);
    });

    // Librarian-only routes
    Route::middleware('role:librarian')->group(function () {
        // librarian routes
        Route::apiResource('books', BookController::class)->only(['index', 'show']);
        Route::apiResource('book-items', BookItemController::class)->only(['index', 'show']);
        Route::apiResource('ebooks', EBookController::class)->only(['index', 'show']);
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
