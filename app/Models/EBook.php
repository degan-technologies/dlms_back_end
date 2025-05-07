<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EBook extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'EBookID';

    protected $fillable = ['author', 'ISBN', 'FileURL', 'FileFormat'];

    public function book()
    {
        return $this->belongsTo(Book::class, 'ISBN', 'ISBN');
    }
}
