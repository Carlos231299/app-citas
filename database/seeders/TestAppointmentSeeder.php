<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use Carbon\Carbon;

class TestAppointmentSeeder extends Seeder
{
    public function run()
    {
        $barber = \App\Models\Barber::first();
        $service = \App\Models\Service::first();

        if (!$barber || !$service) {
            $this->command->error('No Barbers or Services found to attach appointment.');
            return;
        }

        Appointment::create([
            'barber_id' => $barber->id,
            'service_id' => $service->id,
            'client_name' => 'NO BORRAR ES DE PRUEBA',
            'client_phone' => '3000000000',
            'scheduled_at' => Carbon::today()->startOfDay(), // 12:00 AM today
            'status' => 'scheduled',
            'price' => 20000
        ]);
    }
}
