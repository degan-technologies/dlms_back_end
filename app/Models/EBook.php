<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'book_item_id';
    protected $table = 'ebooks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_item_id',
        'file_url',
        'file_format',
        'file_size_mb',
        'pages',
        'is_downloadable',
        'requires_authentication',
        'drm_type',
        'access_expires_at',
        'max_downloads',
        'reader_app',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'file_size_mb' => 'float',
        'pages' => 'integer',
        'is_downloadable' => 'boolean',
        'requires_authentication' => 'boolean',
        'access_expires_at' => 'datetime',
        'max_downloads' => 'integer',
    ];

    /**
     * Get the book item that owns this ebook.
     */
    public function bookItem(): BelongsTo
    {
        return $this->belongsTo(BookItem::class, 'book_item_id');
    }

    /**
     * Get the category of the ebook through book item.
     */
    public function category()
    {
        return $this->bookItem->category();
    }

    /**
     * Get the library branch that houses this ebook.
     */
    public function libraryBranch()
    {
        return $this->bookItem->libraryBranch();
    }
    
    /**
     * Get the title of the ebook from its parent BookItem.
     */
    public function getTitle()
    {
        return $this->bookItem->title;
    }
    
    /**
     * Get the author of the ebook from its parent BookItem.
     */
    public function getAuthor()
    {
        return $this->bookItem->author;
    }
    
    /**
     * Check if the ebook is currently available.
     */
    public function isAvailable()
    {
        // First check parent's availability status
        if ($this->bookItem->availability_status !== BookItem::STATUS_AVAILABLE) {
            return false;
        }
        
        // If there's an expiration date, check if it's still valid
        if ($this->access_expires_at && now() > $this->access_expires_at) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the file extension from the format.
     */
    public function getFileExtension()
    {
        return strtolower($this->file_format);
    }
}
