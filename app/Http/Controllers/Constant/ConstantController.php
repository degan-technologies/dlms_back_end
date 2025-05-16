<?php

namespace App\Http\Controllers\Constant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Constant\CategoryCollection;
use App\Http\Resources\Constant\LanguageCollection;
use App\Http\Resources\Constant\LibraryCollection;
use App\Http\Resources\Constant\SubjectCollection;
use App\Http\Resources\Constant\EbookTypeCollection;
use App\Http\Resources\Constant\AllFiltersResource;
use App\Http\Resources\Constant\GradeCollection;
use App\Models\Category;
use App\Models\Language;
use App\Models\Library;
use App\Models\Subject;
use App\Models\EbookType;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ConstantController extends Controller
{
    /**
     * Get all filter constants in a single call
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllFilters()
    {
       
        return Cache::remember('all-filters', 60 * 30, function () {
            return new AllFiltersResource([
                'categories' => Category::all(),
                'languages' => Language::all(),
                'grades'=>Grade::all(),
                'subjects' => Subject::all(),
                'ebook_types' => EbookType::all()
            ]);
        });
    }

    /**
     * Get categories list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function categories()
    {
        return Cache::remember('categories', 60 * 30, function () {
            return new CategoryCollection(Category::all());
        });
    }

    /**
     * Get languages list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function languages()
    {
        return Cache::remember('languages', 60 * 30, function () {
            return new LanguageCollection(Language::all());
        });
    }


    /**
     * Get subjects list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function subjects()
    {
        return Cache::remember('subjects', 60 * 30, function () {
            return new SubjectCollection(Subject::all());
        });
    }

    /**
     * Get ebook types list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function ebookTypes()
    {
        return Cache::remember('ebook-types', 60 * 30, function () {
            return new EbookTypeCollection(EbookType::all());
        });
    }

    public function grades(){
        return Cache::remember('grades', 60 * 30, function (){
            return new GradeCollection(Grade::all());
        });
    }


    /**
     * Update filter cache after data change
     * Use this method after admin updates to categories, languages, etc.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshFilterCache()
    {
        Cache::forget('all-filters');
        Cache::forget('categories');
        Cache::forget('languages');
        Cache::forget('subjects');
        Cache::forget('ebook-types');
        Cache::forgot('graades');

        return response()->json(['message' => 'Filter cache refreshed successfully']);
    }
}
