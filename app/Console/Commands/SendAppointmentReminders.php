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
                // Calculate Display Price
                $price = $appointment->price;
                $displayPrice = "$" . number_format($price, 0, ',', '.');

                // Check for Extra Time (Re-evaluating logic or relying on stored price difference?)
                // Since we don't store "is_extra" boolean, we can infer it or just check the time again.
                // Logic: < 08:00 or > 18:30 (18:30 is 18*60+30 = 1110 min)
                $sched = \Carbon\Carbon::parse($appointment->scheduled_at);
                $minutes = $sched->hour * 60 + $sched->minute;
                $isExtra = ($minutes < 480) || ($minutes >= 1110); 

                if ($isExtra && $appointment->service->extra_price > 0 && $price >= $appointment->service->extra_price) {
                     $displayPrice .= " (Horario Extra)";
                }
                
                // Override for "Otro servicio" if it was somehow confirmed with variable price?
                // User said: "cuando sea otro servicio... acordar con el barbero" -> This usually applies to requests.
                // If a reminder is sending for a CONFIRMED appointment, it implies price is set.
                // BUT if custom_details are present, maybe we should be vague?
                // Let's stick to: If price is 0, show "Acordar".
                if ($price == 0) {
                    $displayPrice = "Acordar con el barbero";
                }

                // Send to Local Bot
                \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/reminder', [
                    'phone' => $appointment->client_phone,
                    'name' => $appointment->client_name,
                    'time' => $sched->format('h:i A'),
                    'barber_name' => $appointment->barber->name ?? 'Barbería JR',
                    'service_name' => $serviceName,
                    'date' => 'Hoy',
                    'place' => 'Barbería JR',
                    'display_price' => $displayPrice,
                    'is_request' => false
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
