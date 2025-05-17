<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BookItem;
use App\Models\User;
use App\Models\OtherAsset;
use App\Models\Language;
use App\Models\Category;
use App\Models\Students;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomePageController extends Controller
{
    /**
     * Format statistics for marketing display
     * 
     * @param int $actualCount The actual count from the database
     * @param int $minDisplay The minimum number to display (for marketing)
     * @param bool $roundUp Whether to round up to nice numbers
     * @return array
     */
    private function formatStatistic($actualCount, $minDisplay = 0, $roundUp = true)
    {
        $displayCount = max($actualCount, $minDisplay);
        
        if ($roundUp && $displayCount > 100) {
            // Round up to nice numbers
            if ($displayCount < 1000) {
                $displayCount = ceil($displayCount / 100) * 100;
            } else if ($displayCount < 10000) {
                $displayCount = ceil($displayCount / 1000) * 1000;
            } else {
                $displayCount = ceil($displayCount / 5000) * 5000;
            }
        }
        
        return [
            'actual' => $actualCount,
            'display' => $displayCount,
            'formatted' => number_format($displayCount) . '+'
        ];
    }
    
    /**
     * Get the homepage data with library statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHomePageData()
    {
      
        // Get counts for various entities
        $booksCount = BookItem::where('item_type', 'physical')
            ->orWhere('item_type', 'ebook')
            ->count();

        $videosCount = OtherAsset::where('media_type', 'video')
            ->orWhere('media_type', 'DVD')
            ->orWhere('media_type', 'like', '%video%')
            ->count();

        $studentsCount = Students::count();

        $languagesCount = Language::count();
        
        // Get additional statistics
        $categoriesCount = Category::count();
        $tagsCount = Tag::count();
        $ebooksCount = BookItem::where('item_type', 'ebook')->count();
        $physicalBooksCount = BookItem::where('item_type', 'physical')->count();

        // Get top categories with their book counts
        $topCategories = Category::withCount('bookItems')
            ->orderByDesc('book_items_count')
            ->take(5)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'books_count' => $category->book_items_count
                ];
            });

        // Get new arrival count
        $newArrivalsCount = BookItem::where('is_new_arrival', true)->count();        // Format statistics for presentation with marketing minimums
        $booksStats = $this->formatStatistic($booksCount, 10000);
        $videosStats = $this->formatStatistic($videosCount, 5000);
        $studentsStats = $this->formatStatistic($studentsCount, 30000);
        $languagesStats = $this->formatStatistic($languagesCount, 50, false);
        
        // Return data in structured format
        return response()->json([
            'statistics' => [
                'books_and_ebooks' => [
                    'count' => $booksStats['actual'],
                    'display_count' => $booksStats['display'],
                    'formatted_count' => $booksStats['formatted'],
                    'label' => 'Books & E-Books',
                    'details' => [
                        'ebooks' => $ebooksCount,
                        'physical_books' => $physicalBooksCount
                    ]
                ],
                'educational_videos' => [
                    'count' => $videosStats['actual'],
                    'display_count' => $videosStats['display'],
                    'formatted_count' => $videosStats['formatted'],
                    'label' => 'Educational Videos'
                ],
                'students' => [
                    'count' => $studentsStats['actual'],
                    'display_count' => $studentsStats['display'],
                    'formatted_count' => $studentsStats['formatted'],
                    'label' => 'Students'
                ],
                'languages' => [
                    'count' => $languagesStats['actual'],
                    'display_count' => $languagesStats['display'],
                    'formatted_count' => $languagesStats['formatted'],
                    'label' => 'Languages'
                ]
            ],
            'additional_data' => [
                'categories_count' => $categoriesCount,
                'tags_count' => $tagsCount,
                'new_arrivals_count' => $newArrivalsCount,
                'top_categories' => $topCategories
            ]
        ]);
    }
}
