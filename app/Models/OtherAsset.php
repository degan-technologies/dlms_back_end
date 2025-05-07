<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherAsset extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'AssetID';

    protected $fillable = ['Title', 'AssetType', 'UniqueID', 'Details'];

    public function bookItems()
    {
        return $this->hasMany(BookItem::class, 'AssetID');
    }
}
