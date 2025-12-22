<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color', // hex or bootstrap class suffix (e.g., 'primary', 'warning')
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
