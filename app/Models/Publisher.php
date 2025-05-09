<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publisher extends Model
{
    use SoftDeletes;
    


    protected $fillable = ['publisher_name', 'address', 'contact_info'];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
