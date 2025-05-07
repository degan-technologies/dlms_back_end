<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publisher extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'PublisherID';


    protected $fillable = ['PublisherName', 'Address', 'ContactInfo'];

    public function books()
    {
        return $this->hasMany(Book::class, 'PublisherID');
    }
}
