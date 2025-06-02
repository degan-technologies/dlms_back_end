<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\API\LibraryBranchController;
use App\Http\Controllers\API\LibraryController;
use App\Http\Controllers\API\SectionController;
use App\Http\Controllers\AskLibrarianController;
use App\Http\Controllers\ReadingPerformanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bookmark\BookmarkController;
use App\Http\Controllers\Note\NoteController;
use App\Http\Controllers\ChatMessage\ChatMessageController;
use App\Http\Controllers\Collection\CollectionController;
use App\Http\Controllers\BookItem\BookItemController;
use App\Http\Controllers\Book\BookController;
// use App\Http\Controllers\Collection\CollectionController;
use App\Http\Controllers\EBook\EBookController;
use App\Http\Controllers\EBook\EBookFileController;
use App\Http\Controllers\Constant\ConstantController;
use App\Http\Controllers\DashboardStatsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Language\LanguageController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RecentlyViewed\RecentlyViewedController;
use App\Http\Controllers\LearningRecommendationController;
use App\Http\Controllers\HomeController;

// 1. Public Routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Public book item routes
Route::get('book-items/search', [BookItemController::class, 'search']);
Route::get('new-arrivals', [BookItemController::class, 'newArrivals']);
Route::get('featured-books', [BookItemController::class, 'featured']);
Route::get('physical-books', [BookItemController::class, 'physicalBooks']);
Route::get('physical-books/{book_item}', [BookItemController::class, 'showPhysicalBook']);
Route::get('ebooks', [BookItemController::class, 'ebooks']);
Route::get('ebooks/{book_item}', [BookItemController::class, 'showEbook']);
Route::get('book-items/{book_item}', [BookItemController::class, 'show']);

// Constants & filters for frontend
Route::prefix('constants')->group(function () {
    Route::get('all', [ConstantController::class, 'getAllFilters']);
    Route::get('categories', [ConstantController::class, 'categories']);
    Route::post('/categories', [ConstantController::class, 'createCategory']);
    Route::put('/categories/{id}', [ConstantController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [ConstantController::class, 'deleteCategory']);
    Route::get('grades', [ConstantController::class, 'grades']);
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
    Route::get('grades',  [ConstantController::class, 'grades']);
});

Route::prefix('anonymous-chat')->group(function () {
    Route::get('/', [AskLibrarianController::class, 'index']);       // GET messages?session_id=...
    Route::post('/', [AskLibrarianController::class, 'store']);      // POST new visitor message
    Route::post('/reply', [AskLibrarianController::class, 'reply']); // POST reply (admin only frontend, if needed)
});

Route::prefix('anonymous-chat')->group(function () {
    Route::get('/', [AskLibrarianController::class, 'index']);       // GET messages?session_id=...
    Route::post('/', [AskLibrarianController::class, 'store']);      // POST new visitor message
    Route::post('/reply', [AskLibrarianController::class, 'reply']); // POST reply (admin only frontend, if needed)
});

// Serve PDF files with CORS headers
Route::get('ebooks/pdf/{filename}', [EBookFileController::class, 'servePdf'])->where('filename', '.*');

// 2. Authenticated User Routes
Route::middleware('auth:api')->group(function () {
    // User profile & auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::get('/counts', [HomeController::class, 'getCounts']);


    // Learning Recommendations
    Route::get('learning-recommendations', [LearningRecommendationController::class, 'getRecommendations']);


    // Recently Viewed
    Route::get('recently-viewed', [RecentlyViewedController::class, 'index']);
    Route::post('recently-viewed', [RecentlyViewedController::class, 'store']);

    // Notes CRUD
    Route::resource('notes', NoteController::class);

    // Chat Messages CRUD  
    Route::resource('chat-messages', ChatMessageController::class);

    // Bookmarks CRUD
    Route::resource('bookmarks', BookmarkController::class);
    Route::delete('bookmarks/by-ebook/{ebook}', [BookmarkController::class, 'destroyByEbookId']);

    // Collections CRUD
    Route::resource('collections', CollectionController::class);
    Route::get('my-collections', [CollectionController::class, 'myCollections']);
    Route::get('my-collections/{collection}', [CollectionController::class, 'myCollectionShow']);
    Route::post('collections/{collection}/add-ebook', [CollectionController::class, 'addEbook']);

    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations', [ReservationController::class, 'index']);
    Route::get('reservations/{reservation}', [ReservationController::class, 'show']);
    Route::put('reservations/{reservation}', [ReservationController::class, 'update']);
    Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy']);
    Route::get('read/{ebook}', [EBookController::class, 'show']);


    // EBooks CRUD
    Route::resource('ebooks', EBookController::class);

    Route::get('category', [DashboardController::class, 'index']);
    Route::get('/users', [AuthController::class, 'allUsers']);
    Route::put('/user', [AuthController::class, 'updateUser']);
    Route::post('/user', [AuthController::class, 'changePassword']);
    Route::get('/loan-history', [LoanController::class, 'history']);

    Route::get('dashboard-stats', [DashboardStatsController::class, 'stats']);
    Route::get('/reading-performance', [ReadingPerformanceController::class, 'index']);

    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('book-items', [BookItemController::class, 'index']);
    Route::post('book-items', [BookItemController::class, 'store']);
    Route::put('book-items/{book_item}', [BookItemController::class, 'update']);
    Route::delete('book-items/{book_item}', [BookItemController::class, 'destroy']);
    Route::post('book-items/delete-multiple', [BookItemController::class, 'destroyMultiple']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');


    // 3. Student Role
    Route::apiResource('ebooks', EBookController::class);
    Route::apiResource('books', BookController::class);
    Route::middleware('role:student')->group(function () {
        // Read-only access to book items, books, ebooks

        // Route::get('books', [BookController::class, 'index']);
        // Route::get('books/{book}', [BookController::class, 'show']);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');

        //Route::get('notifications/unread', [NotificationController::class, 'unreadNotifications'])->name('notifications.read');
        Route::get('notifications/unread', [NotificationController::class, 'unreadNotifications'])->name('notifications.read');  
        Route::delete('notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
        Route::put('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);
        // Reading lists (legacy)
        // Route::get('reading-lists', ...);
    });

    // 4. Teacher Role
    Route::middleware('role:teacher')->group(function () {
        // CRUD ebooks
        Route::get('teacher-book-items', [BookItemController::class, 'teacherBookItems']);
        // Read-only access to book items, books

        // Route::get('books', [BookController::class, 'index']);
        // Route::get('books/{book}', [BookController::class, 'show']);
    });

    // 5. Librarian Role
    Route::middleware('role:librarian')->group(function () {
        // CRUD books, ebooks, book items
        // Route::apiResource('books', BookController::class);
        // Route::apiResource('ebooks', EBookController::class);
        // Route::apiResource('book-items', BookItemController::class);

        Route::apiResource('loans', LoanController::class);
        Route::get('fines', [FineController::class, 'index']);
        Route::get('fines/{fine}', [FineController::class, 'show']);
        Route::post('fines', [FineController::class, 'store']);
        Route::put('fines/{fine}', [FineController::class, 'update']);
        Route::delete('fines/{fine}', [FineController::class, 'destroy']);
        Route::get('reservations', [ReservationController::class, 'index']);
        // Route::get('notifications', [NotificationController::class, 'index']);
        // Route::get('notifications/unread', [NotificationController::class, 'unreadNotifications']);
        // Route::put('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::get('notifications/{notification}', [NotificationController::class, 'show']);
        Route::post('notifications', [NotificationController::class, 'store']);
        Route::put('notifications/{notification}', [NotificationController::class, 'update']);


        // Route::apiResource('book-items', BookItemController::class);
        // CRUD collections
    });

    // 6. Admin Role
    Route::middleware('role:admin')->group(function () {
        // Full access to all resources
        // // Route::apiResource('books', BookController::class);

        // Route::apiResource('book-items', BookItemController::class);
        // Libraries, sections, users, publishers, asset types, shelves, etc.
        // Route::apiResource('libraries', LibraryController::class);
        // Route::apiResource('sections', SectionController::class);
        // Route::apiResource('users', UserController::class);
        // ...add other admin resources
    });

    Route::get('/branches', [LibraryBranchController::class, 'index']); // Accessible by all users

    // 7. Superadmin Role
    Route::middleware('role:superadmin')->group(function () {
        // Branch management, admin user management
        // Route::apiResource('branches', BranchController::class);
        // Route::apiResource('admins', AdminController::class);
        // ...inherits all admin privileges

        Route::resource('/branches', LibraryBranchController::class)->except(['index']);
        Route::delete('/bulkdelete', [LibraryBranchController::class, 'bulkDelete']);
    });

    //8.both admin and super admin
    Route::middleware('role:superadmin|admin')->group(function () {
        Route::Resource('/libraries', LibraryController::class);
        Route::apiResource('/sections', SectionController::class);
        Route::resource('staff', StaffController::class);
        Route::post('staff/bulk', [StaffController::class, 'storeBulk']);
        Route::resource('students', StudentController::class);
        Route::post('/students/batch', [StudentController::class, 'batchStore']);
        Route::delete('/bulk-delete', [LibraryController::class, 'bulkDelete']);
    });
});

