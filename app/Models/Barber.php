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
        'work_during_lunch' => 'boolean',
        'lunch_work_start' => 'date',
        'lunch_work_end' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('score'), 1) ?? 0; // 0 if no reviews
    }
}
