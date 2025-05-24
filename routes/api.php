<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bookmark\BookmarkController;
use App\Http\Controllers\Note\NoteController;
use App\Http\Controllers\ChatMessage\ChatMessageController;
use App\Http\Controllers\RecentlyViewed\RecentlyViewedController;
use App\Http\Controllers\Collection\CollectionController;
use App\Http\Controllers\BookItem\BookItemController;
use App\Http\Controllers\Book\BookController;
use App\Http\Controllers\EBook\EBookController;
use App\Http\Controllers\Constant\ConstantController;
use App\Http\Controllers\Language\LanguageController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReservationController;


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
Route::prefix('constants')->group(function () {
    Route::get('all', [ConstantController::class, 'getAllFilters']);
    Route::get('categories', [ConstantController::class, 'categories']);
    Route::post('/categories', [ConstantController::class, 'createCategory']);
    Route::put('/categories/{id}', [ConstantController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [ConstantController::class, 'deleteCategory']);
    Route::post('/categories/delete-multiple', [ConstantController::class, 'deleteMultipleCategories']);

    // Language endpoints
    Route::get('languages', [ConstantController::class, 'languageIndex']);
    Route::post('languages', [ConstantController::class, 'languageStore']);
    Route::get('languages/{language}', [ConstantController::class, 'languageShow']);
    Route::put('languages/{language}', [ConstantController::class, 'languageUpdate']);
    Route::delete('languages/{language}', [ConstantController::class, 'languageDestroy']);
    Route::post('languages/delete-multiple', [ConstantController::class, 'languageDestroyMultiple']);

    // Subject endpoints
    Route::get('subjects', [ConstantController::class, 'subjectIndex']);
    Route::post('subjects', [ConstantController::class, 'storeSubject']);
    Route::get('subjects/{subject}', [ConstantController::class, 'subjectShow']);
    Route::put('subjects/{subject}', [ConstantController::class, 'subjectUpdate']);
    Route::delete('subjects/{subject}', [ConstantController::class, 'subjectDestroy']);
    Route::post('subjects/delete-multiple', [ConstantController::class, 'subjectDestroyMultiple']);

    Route::get('ebook-types', [ConstantController::class, 'ebookTypes']);
    Route::get('grades', [ConstantController::class, 'grades']);
});

// 2. Authenticated User Routes
Route::middleware('auth:api')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/{notification}', [NotificationController::class, 'show']);
    Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])
         ->name('notifications.read');
    Route::post('notifications/{id}', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::delete('notifications/{id}', [NotificationController::class, 'deleteNotification']);

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('book-items', [BookItemController::class, 'index']);
    Route::post('book-items', [BookItemController::class, 'store']);
    Route::put('book-items/{book_item}', [BookItemController::class, 'update']);
    Route::delete('book-items/{book_item}', [BookItemController::class, 'destroy']);
    Route::post('book-items/delete-multiple', [BookItemController::class, 'destroyMultiple']);

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
        // Route::get('books', [BookController::class, 'index']);
        // Route::get('books/{book}', [BookController::class, 'show']);
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
        // Route::get('books', [BookController::class, 'index']);
        // Route::get('books/{book}', [BookController::class, 'show']);
    });

    // 5. Librarian Role
    Route::middleware('role:librarian')->group(function () {
        // CRUD books, ebooks, book items
        Route::apiResource('books', BookController::class);
        Route::apiResource('ebooks', EBookController::class);
        Route::apiResource('loans', LoanController::class);
        Route::get('fines', [FineController::class, 'index']);
        Route::get('fines/{fine}', [FineController::class, 'show']);
        Route::post('fines', [FineController::class, 'store']);
        Route::put('fines/{fine}', [FineController::class, 'update']);
        Route::delete('fines/{fine}', [FineController::class, 'destroy']);

        // Route::apiResource('book-items', BookItemController::class);
        // CRUD collections
        Route::apiResource('collections', CollectionController::class);
    });

    // 6. Admin Role
    Route::middleware('role:admin')->group(function () {
        // Full access to all resources
        // Route::apiResource('books', BookController::class);
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

    Route::apiResource('reservations', ReservationController::class);
});
