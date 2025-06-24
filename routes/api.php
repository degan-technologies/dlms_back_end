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
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\RoleController;

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
    Route::post('/reply', [AskLibrarianController::class, 'reply']);
    Route::delete('/{id}', [AskLibrarianController::class, 'destroy']);
    Route::put('/{id}', [AskLibrarianController::class, 'update']);
});

// Serve PDF files with CORS headers
Route::get('ebooks/pdf/{filename}', [EBookFileController::class, 'servePdf'])->where('filename', '.*');

// 2. Authenticated User Routes

Route::middleware(['auth:api'])->group(function () {
    // Announcement CRUD routes
    Route::apiResource('announcements', AnnouncementController::class)
        ->except(['show']);

    // Toggle publish status
    Route::put(
        'announcements/{announcement}/toggle-publish',
        [AnnouncementController::class, 'togglePublish']
    );
});
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


        Route::prefix('student/notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::put('{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');

            //Route::get('notifications/unread', [NotificationController::class, 'unreadNotifications'])->name('notifications.read');
            Route::get('/unread', [NotificationController::class, 'unreadNotifications'])->name('notifications.read');
            Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
            Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
            Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        });
        // Librarian-specific announcements
        Route::prefix('student/announcements')->group(function () {
            Route::get('/', [AnnouncementController::class, 'StudentAnnouncementIndex']);
        });
    });

    // 4. Teacher Role
    Route::middleware('role:teacher')->group(function () {
        // CRUD ebooks
        Route::get('teacher-book-items', [BookItemController::class, 'teacherBookItems']);
    });


    // Public routes (accessible by all users)

    // Admin-only routes
    // 5. Librarian Role
    Route::middleware('role:librarian')->group(function () {
        // CRUD books, ebooks, book items

        Route::prefix('librarian/loans')->group(function () {
            Route::get('/', [LoanController::class, 'librarianIndex']);
            Route::post('/', [LoanController::class, 'store']);
            Route::get('/{loan}', [LoanController::class, 'show']);
            Route::put('/{loan}', [LoanController::class, 'update']);
            Route::delete('/{loan}', [LoanController::class, 'destroy']);
        });
        Route::get('fines', [FineController::class, 'index']);
        Route::get('fines/{fine}', [FineController::class, 'show']);
        Route::post('fines', [FineController::class, 'store']);
        Route::put('fines/{fine}', [FineController::class, 'update']);
        Route::delete('fines/{fine}', [FineController::class, 'destroy']);
        Route::get('reservations', [ReservationController::class, 'index']);
        Route::prefix('librarian/notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'librarianIndex']);
            Route::get('/{notification}', [NotificationController::class, 'show']);
            Route::post('/', [NotificationController::class, 'store']);
            Route::put('/{notification}', [NotificationController::class, 'update']);
            Route::delete('/{notification}', [NotificationController::class, 'librarianDestroy']);
            Route::put('/{notification}/mark-as-read', [NotificationController::class, 'librarianMarkAsRead'])->name('notifications.mark-as-read');
            Route::put('/{notification}/mark-all-read', [NotificationController::class, 'librarianMarkAllAsRead'])->name('notifications.mark-all-read');
            Route::delete('/{notification}/clear-all', [NotificationController::class, 'librarianClearAll'])->name('notifications.clear-all');
        });

        // Librarian-specific announcements
        Route::prefix('librarian/announcements')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index']);
            Route::post('/', [AnnouncementController::class, 'store']);
            Route::put('/{announcement}', [AnnouncementController::class, 'update']);
            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy']);
            Route::put('/{announcement}/toggle-publish', [AnnouncementController::class, 'togglePublish']);
        });
    });
    Route::middleware('role:admin')->group(function () {
        Route::prefix('admin/announcements')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index']);
            Route::post('/', [AnnouncementController::class, 'store']);
            Route::put('/{announcement}', [AnnouncementController::class, 'update']);
            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy']);
            Route::put('/{announcement}/toggle-publish', [AnnouncementController::class, 'togglePublish']);
        });
        Route::get('admin/loans', [LoanController::class, 'adminIndex']);
        Route::prefix('admin/notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'adminNotificationIndex']);
            Route::get('/{notification}', [NotificationController::class, 'show']);
            Route::post('/', [NotificationController::class, 'store']);
            Route::put('/{notification}', [NotificationController::class, 'update']);
            Route::delete('/{notification}', [NotificationController::class, 'adminDestroy']);
            Route::put('/{notification}/mark-as-read', [NotificationController::class, 'adminMarkAsRead'])->name('notifications.mark-as-read');
            Route::put('/{notification}/mark-all-read', [NotificationController::class, 'adminMarkAllAsRead'])->name('notifications.mark-all-read');
            Route::delete('/{notification}/clear-all', [NotificationController::class, 'adminClearAll'])->name('notifications.clear-all');
        });
        Route::prefix('admin/loans')->group(function () {
            Route::get('/', [LoanController::class, 'adminIndex']);
            // Add other admin-specific loan endpoints if needed
        });
    });

    // 7. Superadmin Role
    Route::get('/branches', [LibraryBranchController::class, 'index']);
    Route::middleware('role:superadmin')->group(function () {
        Route::resource('/branches', LibraryBranchController::class)->except(['index']);
        Route::delete('/bulkdelete', [LibraryBranchController::class, 'bulkDelete']);
        Route::prefix('superadmin/announcements')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index']);
            Route::post('/', [AnnouncementController::class, 'store']);
            Route::put('/{announcement}', [AnnouncementController::class, 'update']);
            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy']);
            Route::put('/{announcement}/toggle-publish', [AnnouncementController::class, 'togglePublish']);
        });
        Route::prefix('superadmin/notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'superadminNotificationIndex']);
            Route::get('/{notification}', [NotificationController::class, 'show']);
            Route::post('/', [NotificationController::class, 'store']);
            Route::put('/{notification}', [NotificationController::class, 'update']);
            Route::delete('/{notification}', [NotificationController::class, 'superadminDestroy']);
            Route::put('/{notification}/mark-as-read', [NotificationController::class, 'superadminMarkAsRead'])->name('notifications.mark-as-read');
            Route::put('/{notification}/mark-all-read', [NotificationController::class, 'superadminMarkAllAsRead'])->name('notifications.mark-all-read');
            Route::delete('/{notification}/clear-all', [NotificationController::class, 'superadminClearAll'])->name('notifications.clear-all');
        });
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

        // Roles API
        Route::get('/roles', [RoleController::class, 'getRoles']);
        Route::get('/permissions', [RoleController::class, 'getPermissions']);

        Route::prefix('roles')->group(function () {
            Route::post('/', [RoleController::class, 'store']);
            Route::put('/{role}', [RoleController::class, 'update']);
            Route::delete('/{role}', [RoleController::class, 'destroy']);
        });
    });
});
