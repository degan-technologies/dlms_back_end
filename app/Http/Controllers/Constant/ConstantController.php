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
use Illuminate\Validation\Rule;
use App\Http\Requests\V1\Subject\StoreSubjectRequest;
use Illuminate\Support\Facades\App;
use App\Http\Requests\V1\Language\UpdateLanguageRequest;
use App\Http\Requests\V1\Language\StoreLanguageRequest;
use App\Http\Requests\V1\Subject\UpdateSubjectRequest;
use App\Http\Resources\constant\LanguageResource;
use Illuminate\Http\Response;
use App\Http\Resources\Constant\SubjectResource;


class ConstantController extends Controller
{
    /**
     * Get all filter constants in a single call
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllFilters()
    {
        return new AllFiltersResource([
            'categories' => Category::all(),
            'languages' => Language::all(),
            'grades' => Grade::all(),
            'subjects' => Subject::all(),
            'ebook_types' => EbookType::all()
        ]);
    }

    /**
     * Get categories list
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */

    public function categories(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $paginator = Category::with(['bookItems' => function ($q) {
            $q->withCount('books');
        }])
            ->withCount('bookItems')
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        // Inject total_books into each category
        $paginator->getCollection()->transform(function ($category) {
            $category->books_count = $category->bookItems->sum('books_count');
            return $category;
        });

        return new CategoryCollection($paginator);
    }


    /**
     * Create a new category
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCategory(Request $request)
    {

        $validated = $request->validate([
            'category_name' => 'required|string|max:255|unique:categories,category_name'
        ]);

        $category = Category::create([
            'category_name' => $validated['category_name']
        ]);

        // Invalidate all known cached category keys (basic workaround)
        $this->bumpCategoryCacheVersion();

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Update a category
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'category_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'category_name')->ignore($category->id)
            ],
        ]);

        $category->update([
            'category_name' => $validated['category_name'],
        ]);

        $this->bumpCategoryCacheVersion();

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }


    private function bumpCategoryCacheVersion()
    {
        $version = Cache::get('categories_cache_version', 1);
        Cache::forever('categories_cache_version', $version + 1);
    }
    /**
     * Delete a category
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCategory($id)
    {
        // Eager load bookItems and their related books
        $category = Category::with('bookItems.books')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        // Check if the category is used in any book items
        if ($category->bookItems->isNotEmpty()) {
            return response()->json([
                'message' => 'Cannot delete category because it is used in book items.',
                'used_in' => 'book_items',
            ], 409); // Conflict response
        }

        // Optionally, check if there are books through the book items
        $usedInBooks = $category->bookItems->some(function ($bookItem) {
            return $bookItem->books()->exists();
        });

        if ($usedInBooks) {
            return response()->json([
                'message' => 'Cannot delete category because it is used in books.',
                'used_in' => 'books',
            ], 409);
        }

        // Soft delete the category
        $category->delete();

        // Invalidate cache
        $this->bumpCategoryCacheVersion(); // Custom method or use Cache::flush() if appropriate

        return response()->json(['message' => 'Category deleted successfully']);
    }




    /**
     * Invalidate filter cache for languages and subjects
     */
    private function bumpFilterCacheVersion()
    {
        Cache::forget('all-filters');
        Cache::forget('languages');
        Cache::forget('subjects');
    }

    /**
     * Get languages list (no cache, always fresh)
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    // Removed duplicate languageIndex method to resolve redeclaration error.

    /**
     * Get subjects list (no cache, always fresh)
     * 
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function subjectIndex(Request $request)
    {
        $query = Subject::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%");
        }
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $perPage = $request->input('per_page', 25);
        $subjects = $query->orderBy($sortBy, $sortDir)->paginate($perPage);
        return new \App\Http\Resources\Constant\SubjectCollection($subjects);
    }

    public function storeLanguage(StoreLanguageRequest $request)
    {
        $language = Language::create($request->validated());
        $this->bumpLanguageCacheVersion();
        return response()->json([
            'message' => 'Language created successfully.',
            'language' => new LanguageResource($language)
        ], Response::HTTP_CREATED);
    }

    public function showLanguage(Language $language)
    {
        return new LanguageResource($language);
    }

    public function update(UpdateLanguageRequest $request, Language $language)
    {
        $language->update($request->validated());
        $this->bumpLanguageCacheVersion();
        return response()->json([
            'message' => 'Language updated successfully.',
            'language' => new LanguageResource($language)
        ]);
    }

    public function destroy(Language $language)
    {
        $language->delete();
        $this->bumpLanguageCacheVersion();
        return response()->json(['message' => 'Language deleted successfully.']);
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        $failed = [];
        foreach ($ids as $id) {
            $language = Language::find($id);
            if ($language) {
                $language->delete();
            } else {
                $failed[] = $id;
            }
        }
        $this->bumpLanguageCacheVersion();
        if (!empty($failed)) {
            return response()->json([
                'message' => 'Some languages could not be deleted.',
                'failed' => $failed
            ], Response::HTTP_CONFLICT);
        }
        return response()->json(['message' => 'Languages deleted successfully.']);
    }

    // --- Language CRUD ---
    public function languageIndex(Request $request)
    {
        $query = Language::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                ->orWhere('code', 'like', "%$search%");
        }
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $perPage = $request->input('per_page', 20);
        $languages = $query->orderBy($sortBy, $sortDir)->paginate($perPage);
        return new LanguageCollection($languages);
    }

    public function languageStore(StoreLanguageRequest $request)
    {
        $language = Language::create($request->validated());
        $this->bumpLanguageCacheVersion();
        return response()->json([
            'message' => 'Language created successfully.',
            'language' => new LanguageResource($language)
        ], 201);
    }

    public function languageShow(Language $language)
    {
        return new LanguageResource($language);
    }

    public function languageUpdate(UpdateLanguageRequest $request, Language $language)
    {
        $language->update($request->validated());
        $this->bumpLanguageCacheVersion();
        return response()->json([
            'message' => 'Language updated successfully.',
            'language' => new LanguageResource($language)
        ]);
    }

    public function languageDestroy(Language $language)
    {
        $language->delete();
        $this->bumpLanguageCacheVersion();
        return response()->json(['message' => 'Language deleted successfully.']);
    }

    public function languageDestroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        $failed = [];
        foreach ($ids as $id) {
            $language = Language::find($id);
            if ($language) {
                $language->delete();
            } else {
                $failed[] = $id;
            }
        }
        $this->bumpLanguageCacheVersion();
        if (!empty($failed)) {
            return response()->json([
                'message' => 'Some languages could not be deleted.',
                'failed' => $failed
            ], 409);
        }
        return response()->json(['message' => 'Languages deleted successfully.']);
    }

    // --- Subject CRUD ---
    public function subjectShow(Subject $subject)
    {
        return new SubjectResource($subject);
    }

    public function subjectUpdate(UpdateSubjectRequest $request, Subject $subject)
    {
        $subject->update($request->validated());
        $this->bumpSubjectCacheVersion();
        return response()->json([
            'message' => 'Subject updated successfully.',
            'subject' => new SubjectResource($subject)
        ]);
    }

    public function subjectDestroy(Subject $subject)
    {
        $subject->delete();
        $this->bumpSubjectCacheVersion();
        return response()->json(['message' => 'Subject deleted successfully.']);
    }

    public function subjectDestroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        $failed = [];
        foreach ($ids as $id) {
            $subject = Subject::find($id);
            if ($subject) {
                $subject->delete();
            } else {
                $failed[] = $id;
            }
        }
        $this->bumpSubjectCacheVersion();
        if (!empty($failed)) {
            return response()->json([
                'message' => 'Some subjects could not be deleted.',
                'failed' => $failed
            ], Response::HTTP_CONFLICT);
        }
        return response()->json(['message' => 'Subjects deleted successfully.']);
    }

    private function bumpLanguageCacheVersion()
    {
        $version = Cache::get('languages_cache_version', 1);
        Cache::forever('languages_cache_version', $version + 1);
        // Only the language cache version is updated. No other caches are affected.
    }

    private function bumpSubjectCacheVersion()
    {
        $version = Cache::get('subjects_cache_version', 1);
        Cache::forever('subjects_cache_version', $version + 1);
        // Only the subject cache version is updated. No other caches are affected.
    }
}
