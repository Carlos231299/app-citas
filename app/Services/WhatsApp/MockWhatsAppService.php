<?php

namespace App\Services\WhatsApp;

use App\Models\Appointment;
use Illuminate\Support\Facades\Log;

class MockWhatsAppService implements WhatsAppServiceInterface
{
    public function sendConfirmation(Appointment $appointment)
    {
        Log::info("WHATSAPP MOCK: Sending Confirmation to {$appointment->client_phone}");
        Log::info("MSG: Hola {$appointment->client_name}, confirma tu cita para el {$appointment->scheduled_at} con {$appointment->barber->name}.");
    }

    public function sendReminder(Appointment $appointment)
    {
        Log::info("WHATSAPP MOCK: Sending Reminder to {$appointment->client_phone}");
        Log::info("MSG: Hola {$appointment->client_name}, tu cita es en 15 minutos via {$appointment->barber->name}. Por favor confirma asistencia.");
    }

    public function sendCancellation(Appointment $appointment, string $reason)
    {
        Log::info("WHATSAPP MOCK: Sending Cancellation to {$appointment->client_phone}");
        Log::info("MSG: Tu cita ha sido cancelada. Motivo: {$reason}");
    }

    public function sendGroupAlert(Appointment $appointment)
    {
        Log::info("WHATSAPP MOCK: Sending Group Alert");
        Log::info("MSG: ¡Cupo Disponible! {$appointment->scheduled_at->format('H:i')} con {$appointment->barber->name}.");
    }
}
