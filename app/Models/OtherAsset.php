<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'book_item_id';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'asset_type_id',
        'asset_type', // Deprecated
        'media_type',
        'unique_id',
        'duration_minutes',
        'manufacturer',
        'physical_condition',
        'location_details',
        'acquisition_date',
        'usage_instructions',
        'restricted_access',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'acquisition_date' => 'date',
        'restricted_access' => 'boolean',
    ];

    /**
     * Get the book item that owns this asset.
     */
    public function bookItem(): BelongsTo
    {
        return $this->belongsTo(BookItem::class, 'book_item_id');
    }

    /**
     * Get the asset type that this asset belongs to.
     */
    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }
}
