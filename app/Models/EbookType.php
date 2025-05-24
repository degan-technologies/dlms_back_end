<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EbookType extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'e_book_types';

    protected $fillable =[
        'name'
    ];

    public function ebooks()
    {
        return $this->hasMany(EBook::class, 'e_book_type_id');
    }
}
