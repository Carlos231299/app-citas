<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'unavailable_start' => 'datetime',
        'unavailable_end' => 'datetime',
        'extra_time_start' => 'date',
        'extra_time_end' => 'date',
        'is_active' => 'boolean',
        'special_mode' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
