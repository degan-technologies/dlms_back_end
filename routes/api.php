<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bookmark\BookmarkController;
use App\Http\Controllers\Note\NoteController;
use App\Http\Controllers\ChatMessage\ChatMessageController;
use App\Http\Controllers\RecentlyViewedController;
use App\Http\Controllers\Collection\CollectionController;
use App\Http\Controllers\BookItem\BookItemController;
use App\Http\Controllers\Book\BookController;
use App\Http\Controllers\EBook\EBookController;
use App\Http\Controllers\Constant\ConstantController;


// 1. Public Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Public book item routes
Route::get('new-arrivals', [BookItemController::class, 'newArrivals']);
Route::get('featured-books', [BookItemController::class, 'featured']);
Route::get('physical-books', [BookItemController::class, 'physicalBooks']);
Route::get('physical-books/{book_item}', [BookItemController::class, 'showPhysicalBook']);
Route::get('ebooks', [BookItemController::class, 'ebooks']);
Route::get('ebooks/{book_item}', [BookItemController::class, 'showEbook']);

// Constants & filters for frontend
Route::prefix('constants')->group(function() {
    Route::get('all', [ConstantController::class, 'getAllFilters']);
    Route::get('categories', [ConstantController::class, 'categories']);
    Route::get('languages', [ConstantController::class, 'languages']);
    Route::get('subjects', [ConstantController::class, 'subjects']);
    Route::get('ebook-types', [ConstantController::class, 'ebookTypes']);
    Route::get('grades',[ConstantController::class, 'grades']);
});

// 2. Authenticated User Routes
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('book-items', [BookItemController::class, 'index']);
    // Bookmarks
    Route::apiResource('bookmarks', BookmarkController::class);
    // Notes
    Route::apiResource('notes', NoteController::class);
    // Chat Messages
    Route::apiResource('chat-messages', ChatMessageController::class);
    // Recently Viewed
    Route::get('recently-viewed', [RecentlyViewedController::class, 'index']);
    Route::post('recently-viewed', [RecentlyViewedController::class, 'store']);

    // 3. Student Role
    Route::middleware('role:student')->group(function () {
        // Collections (own only)
        Route::apiResource('collections', CollectionController::class);
        Route::post('collections/{collection}/add-ebook', [CollectionController::class, 'addEbook']);
        Route::post('collections/{collection}/remove-ebook', [CollectionController::class, 'removeEbook']);
        // Read-only access to book items, books, ebooks

        Route::get('book-items/{book_item}', [BookItemController::class, 'show']);
        Route::get('books', [BookController::class, 'index']);
        Route::get('books/{book}', [BookController::class, 'show']);
        Route::get('ebooks', [EBookController::class, 'index']);
        Route::get('ebooks/{ebook}', [EBookController::class, 'show']);
        // Reading lists (legacy)
        // Route::get('reading-lists', ...);
    });

    // 4. Teacher Role
    Route::middleware('role:teacher')->group(function () {
        // CRUD collections
        Route::apiResource('collections', CollectionController::class);
        Route::post('collections/{collection}/add-ebook', [CollectionController::class, 'addEbook']);
        Route::post('collections/{collection}/remove-ebook', [CollectionController::class, 'removeEbook']);
        // CRUD ebooks
        Route::apiResource('ebooks', EBookController::class);
        // Read-only access to book items, books

        Route::get('book-items/{book_item}', [BookItemController::class, 'show']);
        Route::get('books', [BookController::class, 'index']);
        Route::get('books/{book}', [BookController::class, 'show']);
    });

    // 5. Librarian Role
    Route::middleware('role:librarian')->group(function () {
        // CRUD books, ebooks, book items
        Route::apiResource('books', BookController::class);
        Route::apiResource('ebooks', EBookController::class);
        // Route::apiResource('book-items', BookItemController::class);
        // CRUD collections
        Route::apiResource('collections', CollectionController::class);
    });

    // 6. Admin Role
    Route::middleware('role:admin')->group(function () {
        // Full access to all resources
        Route::apiResource('books', BookController::class);
        Route::apiResource('ebooks', EBookController::class);
        // Route::apiResource('book-items', BookItemController::class);
        Route::apiResource('collections', CollectionController::class);
        // Libraries, sections, users, publishers, asset types, shelves, etc.
        // Route::apiResource('libraries', LibraryController::class);
        // Route::apiResource('sections', SectionController::class);
        // Route::apiResource('users', UserController::class);
        // ...add other admin resources
    });

    // 7. Superadmin Role
    Route::middleware('role:superadmin')->group(function () {
        // Branch management, admin user management
        // Route::apiResource('branches', BranchController::class);
        // Route::apiResource('admins', AdminController::class);
        // ...inherits all admin privileges
    });
});
