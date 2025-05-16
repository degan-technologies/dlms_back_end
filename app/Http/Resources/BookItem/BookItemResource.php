<?php

namespace App\Http\Resources\BookItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Book\BookResource;
use App\Http\Resources\EBook\EBookResource;

class BookItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */    public function toArray(Request $request): array
    {
        // Common BookItem properties
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'cover_image_url' => $this->cover_image_url,
            'language_id' => $this->language_id,
            'category_id' => $this->category_id,
            'grade' => $this->grade,
            'library_id' => $this->library_id,
            'shelf_id' => $this->shelf_id,
            'subject_id' => $this->subject_id,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
        
        // Determine the item type - prefer ebooks if both are available and 'format=ebook' is specified
        $hasPhysicalBook = $this->relationLoaded('books') && $this->books->isNotEmpty();
        $hasEbook = $this->relationLoaded('ebooks') && $this->ebooks->isNotEmpty();
        
        $preferEbook = $request->has('format') && $request->format === 'ebook';
        
        // Include type flag for downstream processing
        $data['item_type'] = ($hasEbook && $preferEbook) ? 'ebook' : ($hasPhysicalBook ? 'book' : ($hasEbook ? 'ebook' : null));
        
        // Include either book data or ebook data, not both
        if ($data['item_type'] === 'book' || ($hasPhysicalBook && !$preferEbook)) {
            $data['available_books_count'] = $this->when(isset($this->available_books_count), $this->available_books_count);
            $data['books'] = BookResource::collection($this->whenLoaded('books'));
        } else if ($data['item_type'] === 'ebook') {
            $data['ebooks'] = EBookResource::collection($this->whenLoaded('ebooks'));
        }
          // Add flags for filtering
        $data['has_physical_book'] = $hasPhysicalBook;
        $data['has_ebook'] = $hasEbook;
        
        // Include other relationships when loaded
        $data['language'] = $this->whenLoaded('language', function() {
            return [
                'id' => $this->language->id,
                'name' => $this->language->name,
                'code' => $this->language->code,
            ];
        });

        $data['grade'] = $this->whenLoaded('grade', function() {
            return [
                'id' => $this->grade->id,
                'name' => $this->grade->name,
            ];
        });
        
        $data['category'] = $this->whenLoaded('category', function() {
            return [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ];
        });
        
        $data['library'] = $this->whenLoaded('library', function() {
            return [
                'id' => $this->library->id,
                'name' => $this->library->name,
            ];
        });
          $data['subject'] = $this->whenLoaded('subject', function() {
            return [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ];
        });
        
        return $data;
    }
}
