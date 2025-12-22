<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use Carbon\Carbon;

class TestAppointmentSeeder extends Seeder
{
    public function run()
    {
        Appointment::create([
            'barber_id' => 1,
            'service_id' => 1,
            'client_name' => 'NO BORRAR ES DE PRUEBA',
            'client_phone' => '3000000000',
            'scheduled_at' => Carbon::today()->startOfDay(), // 12:00 AM today
            'status' => 'scheduled',
            'price' => 20000
        ]);
    }
}
