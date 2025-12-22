<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_name',
        'appointment_id',
        'total',
        'payment_method',
        'items', // JSON column
        'completed_at'
    ];

    protected $casts = [
        'items' => 'array',
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // Who processed the sale
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
