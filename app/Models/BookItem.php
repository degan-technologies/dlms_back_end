<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookItem extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'BookItemID';
    protected $fillable = [
        'ISBN','AssetID','library_branch_id',
        'ItemType','ShelfID','AvailabilityStatus'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class,'ISBN','isbn');
    }
    public function asset()
    {
        return $this->belongsTo(OtherAsset::class,'AssetID','AssetID');
    }
    public function branch()
    {
        return $this->belongsTo(LibraryBranch::class,'library_branch_id');
    }
    public function shelf()
    {
        return $this->belongsTo(Shelf::class,'ShelfID','ShelfID');
    }
}

