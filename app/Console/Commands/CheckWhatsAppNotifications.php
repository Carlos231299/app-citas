<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckWhatsAppNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-whats-app-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for appointments needing reminders or auto-cancellation';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\WhatsApp\WhatsAppServiceInterface $whatsappService)
    {
        $now = now();
        $this->info("Running WhatsApp Check at: " . $now);

        // 1. Reminders (15 minutes before)
        // Logic: Scheduled at is between [now + 14min, now + 16min] roughly, to catch the 15m mark.
        // OR: Scheduled_at <= now + 15min AND Scheduled_at > now AND !reminder_sent
        
        $reminders = \App\Models\Appointment::with('barber')
            ->where('status', 'scheduled')
            ->where('reminder_sent', false)
            ->whereBetween('scheduled_at', [$now->copy()->addMinutes(14), $now->copy()->addMinutes(16)])
            ->get();

        foreach ($reminders as $appt) {
            $whatsappService->sendReminder($appt);
            $appt->update(['reminder_sent' => true]);
            $this->info("Reminder sent for Appointment ID: {$appt->id}");
        }

        // 2. Auto-Cancellation (5 minutes after start if not confirmed)
        // Logic: Scheduled_at < now - 5min AND status=scheduled AND is_confirmed=false
        
        $toCancel = \App\Models\Appointment::with('barber')
            ->where('status', 'scheduled')
            ->where('is_confirmed', false)
            ->where('scheduled_at', '<', $now->copy()->subMinutes(5)) // e.g. 5 mins past start
            ->get();

        foreach ($toCancel as $appt) {
            $whatsappService->sendCancellation($appt, 'No confirmada tras 5 minutos de inicio.');
            $whatsappService->sendGroupAlert($appt);
            
            $appt->update([
                'status' => 'cancelled',
                'cancellation_reason' => 'Automática: No confirmada (WhatsApp)'
            ]);
            $this->info("Auto-Cancelled Appointment ID: {$appt->id}");
        }
        
        return 0;
    }
}
