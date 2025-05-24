<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_name',
    ];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function bookItems(): HasMany
    {
        return $this->hasMany(BookItem::class, 'category_id');
    }
}
