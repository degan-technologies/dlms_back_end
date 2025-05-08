<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shelf extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'location',
        'capacity',
        'is_active',
        'section_id',
        'library_branch_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the section that owns this shelf.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the library branch that owns this shelf.
     */
    public function libraryBranch(): BelongsTo
    {
        return $this->belongsTo(LibraryBranch::class);
    }

    /**
     * Get the book items stored on this shelf.
     */
    public function bookItems(): HasMany
    {
        return $this->hasMany(BookItem::class);
    }
    
    /**
     * Get the current occupancy of the shelf
     */
    public function getOccupancyAttribute()
    {
        return $this->bookItems()->count();
    }
    
    /**
     * Check if the shelf has available space
     */
    public function hasAvailableSpace(): bool
    {
        return $this->occupancy < $this->capacity;
    }
    
    /**
     * Get remaining capacity
     */
    public function getRemainingCapacityAttribute()
    {
        return max(0, $this->capacity - $this->occupancy);
    }
    
    /**
     * Get full location string including branch name
     */
    public function getFullLocationAttribute()
    {
        $location = $this->code;
        
        if ($this->location) {
            $location .= ' - ' . $this->location;
        }
        
        if ($this->libraryBranch) {
            $location .= ' (' . $this->libraryBranch->branch_name . ')';
        }
        
        return $location;
    }
}
