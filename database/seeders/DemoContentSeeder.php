<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barber;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;

class DemoContentSeeder extends Seeder
{
    public function run()
    {
        // 1. Create 3 New Barbers
        $newBarbers = [
            ['name' => 'Carlos Rodriguez', 'is_active' => true, 'avatar' => 'default.png'],
            ['name' => 'Andres "El Mago"', 'is_active' => true, 'avatar' => 'default.png'],
            ['name' => 'Luis Fade', 'is_active' => true, 'avatar' => 'default.png'],
        ];

        $barberIds = [];
        foreach ($newBarbers as $b) {
            $barber = Barber::firstOrCreate(['name' => $b['name']], $b);
            $barberIds[] = $barber->id;
        }

        // Add the existing default barber to the mix if available
        $defaultBarber = Barber::where('name', 'Javier Mosquera')->first();
        if($defaultBarber) $barberIds[] = $defaultBarber->id;

        // 2. Create Future Appointments (Tomorrow + 3 days)
        $services = Service::all();
        if($services->count() === 0) {
            $this->command->error('No services found. Run DatabaseSeeder first.');
            return;
        }

        $startDate = Carbon::tomorrow();
        $daysToSeed = 3;

        $clientNames = ['Juan Perez', 'Maria Garcia', 'Pedro Lopez', 'Ana Martinez', 'Diego Torres', 'Sofia Ruiz', 'Miguel Angel'];
        
        for ($i = 0; $i < $daysToSeed; $i++) {
            $currentDate = $startDate->copy()->addDays($i);
            
            // Create 3-5 appointments per day
            $appointmentsPerDay = rand(3, 5);
            
            for ($j = 0; $j < $appointmentsPerDay; $j++) {
                // Random time between 09:00 and 19:00
                $hour = rand(9, 19);
                $minute = (rand(0, 1) === 0) ? '00' : '30';
                
                $scheduledAt = $currentDate->copy()->setTime($hour, $minute);
                
                $service = $services->random();
                $barberId = $barberIds[array_rand($barberIds)];

                Appointment::create([
                    'client_name' => $clientNames[array_rand($clientNames)],
                    'client_phone' => '300' . rand(1000000, 9999999),
                    'service_id' => $service->id,
                    'barber_id' => $barberId,
                    'scheduled_at' => $scheduledAt,
                    'status' => 'scheduled', // Default
                    'custom_details' => 'Cita generada automáticamente (Demo)'
                ]);
            }
        }

        $this->command->info("Created 3 barbers and populated appointments for the next 3 days.");
    }
}
