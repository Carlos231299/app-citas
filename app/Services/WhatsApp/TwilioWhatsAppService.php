<?php

namespace App\Services\WhatsApp;

use App\Models\Appointment;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioWhatsAppService implements WhatsAppServiceInterface
{
    protected $sid;
    protected $token;
    protected $from;
    protected $twilio;

    public function __construct()
    {
        // Credentials via Config/Env
        $this->sid = config('services.twilio.sid');
        $this->token = config('services.twilio.token');
        $this->from = config('services.twilio.whatsapp_from');

        try {
            $this->twilio = new Client($this->sid, $this->token);
        } catch (\Exception $e) {
            Log::error("Twilio Init Error: " . $e->getMessage());
        }
    }

    public function sendConfirmation(Appointment $appointment)
    {
        // 1. Format Phone Number (Twilio needs whatsapp:+CountryCodeNumber)
        $details = $this->formatPhone($appointment->client_phone);
        $to = $details['to'];

        // 2. Build Message
        $barberName = $appointment->barber->name ?? 'Barbero';
        $serviceName = $appointment->service->name ?? 'Servicio';
        
        // Detailed message as requested
        $msg = "📢 *Confirmación de Cita - Barbería JR*\n\n" .
               "Hola *{$appointment->client_name}* 👋, tu cita ha sido confirmada:\n\n" .
               "💈 *Barbero:* {$barberName}\n" .
               "✂️ *Servicio:* {$serviceName}\n" .
               "📅 *Fecha:* {$appointment->scheduled_at->format('Y-m-d')}\n" .
               "⏰ *Hora:* {$appointment->scheduled_at->format('H:i')}\n\n" .
               "📍 Te esperamos. Si deseas cancelar, responde con *CANCELAR*.";

        return $this->sendMessage($to, $msg);
    }

    protected function sendMessage($to, $messageBody)
    {
        try {
            $message = $this->twilio->messages->create(
                $to, 
                [
                    "from" => $this->from,
                    "body" => $messageBody
                ]
            );
            
            Log::info("Twilio Sent to $to: " . $message->sid);
            return $message->sid;
        } catch (\Exception $e) {
            Log::error("Twilio Failed to $to: " . $e->getMessage());
            return false;
        }
    }

    private function formatPhone($phone)
    {
        // Clean non-numeric except +
        $clean = preg_replace('/[^0-9+]/', '', $phone);
        
        // If it starts with +, assume it is good to go (just prepend whatsapp:)
        if (strpos($clean, '+') === 0) {
            return ['to' => "whatsapp:" . $clean];
        }

        // If 10 digits (Colombia), add +57
        if (strlen($clean) == 10) {
            return ['to' => "whatsapp:+57" . $clean];
        }

        // Fallback catch-all: add + if missing
        return ['to' => "whatsapp:+" . $clean];
    }

    public function sendReminder(Appointment $appointment)
    {
        // Mock
        Log::info("Twilio Reminder Mock"); 
        return true;
    }

    public function sendCancellation(Appointment $appointment, string $reason)
    {
        // Mock
        Log::info("Twilio Cancellation Mock");
        return true;
    }

    public function sendGroupAlert(Appointment $appointment)
    {
        // Mock
        Log::info("Twilio GroupAlert Mock");
        return true;
    }
}
