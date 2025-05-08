<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_electronic',
        'file_type_category',
        'allowed_extensions',
        'max_file_size',
        'icon',
        'metadata',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_electronic' => 'boolean',
        'allowed_extensions' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all other assets of this type.
     */
    public function otherAssets(): HasMany
    {
        return $this->hasMany(OtherAsset::class);
    }

    /**
     * Check if this asset type is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get allowed extension list as a comma-separated string.
     */
    public function getAllowedExtensionsAttribute($value)
    {
        return $this->attributes['allowed_extensions'] = json_decode($value, true);
    }

    /**
     * Set allowed extension list from a comma-separated string or array.
     */
    public function setAllowedExtensionsAttribute($value)
    {
        if (is_string($value)) {
            $extensions = array_map('trim', explode(',', $value));
            $this->attributes['allowed_extensions'] = json_encode($extensions);
        } else if (is_array($value)) {
            $this->attributes['allowed_extensions'] = json_encode($value);
        }
    }
}