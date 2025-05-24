<?php

namespace App\Http\Controllers\Constant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Constant\CategoryCollection;
use App\Http\Resources\Constant\LanguageCollection;

use App\Http\Resources\Constant\SubjectCollection;
use App\Http\Resources\Constant\EbookTypeCollection;
use App\Http\Resources\Constant\AllFiltersResource;
use App\Http\Resources\Constant\GradeCollection;
use App\Http\Resources\Constant\LibraryCollection;
use App\Http\Resources\Constant\ShelfCollection;
use App\Models\BookCondition;
use App\Models\Category;
use App\Models\Language;
use App\Models\Subject;
use App\Models\EbookType;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ConstantController extends Controller {
    /**
     * Get all filter constants in a single call
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllFilters() {
        $user = Auth::user();
        $libraryBranch = $user ? $user->libraryBranch : null;
        $libraries = $libraryBranch ? $libraryBranch->library()->get()->unique('id')->values() : collect();
        // Get shelves through libraryBranch -> library -> shelves
        // Get all shelves from all libraries in the user's library branch
        $shelves = $libraries->isNotEmpty()
            ? $libraries->flatMap(function ($library) {
            return $library->shelves;
            })->unique('id')->values()
            : collect();
        return response()->json([
            'categories'   => (new CategoryCollection(Category::all()))->response()->getData(true),
            'languages'    => (new LanguageCollection(Language::all()))->response()->getData(true),
            'grades'       => (new GradeCollection(Grade::with('sections')->get()))->response()->getData(true),
            'subjects'     => (new SubjectCollection(Subject::all()))->response()->getData(true),
            'ebook_types'  => (new EbookTypeCollection(EbookType::all()))->response()->getData(true),
            'libraries'    => (new LibraryCollection($libraries))->response()->getData(true),
            'shelves'      => (new ShelfCollection($shelves))->response()->getData(true),
            
        ]);
    }

    /**
     * Get categories list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function categories() {
        return Cache::remember('categories', 60 * 30, function () {
            return new CategoryCollection(Category::all());
        });
    }

    /**
     * Get languages list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function languages() {
        return Cache::remember('languages', 60 * 30, function () {
            return new LanguageCollection(Language::all());
        });
    }


    /**
     * Get subjects list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function subjects() {
        return Cache::remember('subjects', 60 * 30, function () {
            return new SubjectCollection(Subject::all());
        });
    }

    /**
     * Get ebook types list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function ebookTypes() {
        return Cache::remember('ebook-types', 60 * 30, function () {
            return new EbookTypeCollection(EbookType::all());
        });
    }

    public function grades() {
        return Cache::remember('grades', 60 * 30, function () {
            return new GradeCollection(Grade::all());
        });
    }
    public function libraries() {
        return Cache::remember('libraries', 60 * 30, function () {
            $user = auth()->user();
            // Get the user's library branch and its related libraries
            $libraryBranch = $user ? $user->libraryBranch : null;
            $libraries = $libraryBranch ? $libraryBranch->library()->get()->unique('id')->values() : collect();

            return new LibraryCollection($libraries);
        });
    }

     public function shelves() {
        return Cache::remember('shelves', 60 * 30, function () {
            $user = auth()->user();
            // Get the user's library branch and its related libraries
            $libraryBranch = $user ? $user->libraryBranch : null;
            $shelves = $libraryBranch && $libraryBranch->library
                ? $libraryBranch->library->shelves()->get()->unique('id')->values()
                : collect();

            return new ShelfCollection($shelves);
        });
    }

    /**
     * Update filter cache after data change
     * Use this method after admin updates to categories, languages, etc.
     * 
     * @return \Illuminate\Http\JsonResponse
     */   
     public function refreshFilterCache() {
        
        Cache::forget('all-filters');
        Cache::forget('categories');
        Cache::forget('languages');
        Cache::forget('subjects');
        Cache::forget('ebook-types');
        Cache::forget('grades');
        Cache::forget('libraries');

        return response()->json(['message' => 'Filter cache refreshed successfully']);
    }
}
