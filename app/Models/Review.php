<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['appointment_id', 'barber_id', 'score', 'comment'];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
