<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:remind';
    protected $description = 'Send WhatsApp reminders for appointments starting in 10-15 minutes';

    public function handle()
    {
        $now = \Carbon\Carbon::now();
        $targetTime = $now->copy()->addMinutes(12); // Target approx 10 min window
        
        // Find appointments scheduled between 10 and 15 mins from now
        // And NOT requested (must be confirmed)
        // And reminder NOT sent
        $appointments = \App\Models\Appointment::where('status', 'scheduled')
            ->where('reminder_sent', false)
            ->whereBetween('scheduled_at', [
                $now->copy()->addMinutes(10), 
                $now->copy()->addMinutes(15)
            ])
            ->get();

        $this->info("Found {$appointments->count()} appointments to remind.");

        foreach ($appointments as $appointment) {
            try {
                // Determine Service Name
                $serviceName = $appointment->service->name;
                if ($appointment->custom_details) {
                    $serviceName .= " ({$appointment->custom_details})";
                }

                // Send to Local Bot
                // Use Short Timeout (1s) because we track 'reminder_sent' in DB 
                // and we don't want to block the cron loop.
                \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/reminder', [
                    'phone' => $appointment->client_phone,
                    'name' => $appointment->client_name,
                    'time' => \Carbon\Carbon::parse($appointment->scheduled_at)->format('h:i A'),
                    'barber_name' => $appointment->barber->name ?? 'Barbería JR',
                    'service_name' => $serviceName
                ]);

                // Mark as sent
                $appointment->update(['reminder_sent' => true]);
                $this->info("Reminder sent to {$appointment->client_name}");

            } catch (\Exception $e) {
                // Log but continue
                \Illuminate\Support\Facades\Log::error("Reminder Failed ID {$appointment->id}: " . $e->getMessage());
                $this->error("Failed ID {$appointment->id}");
            }
        }
    }
}
