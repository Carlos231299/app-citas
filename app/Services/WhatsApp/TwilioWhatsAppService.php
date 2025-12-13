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

        // 2. Prepare Variables for Template "HXb5b62575e6e4ff6129ad7c8efe1f983e"
        // Variable 1: Date
        // Variable 2: Time
        $date = $appointment->scheduled_at->format('d/m/Y');
        $time = $appointment->scheduled_at->format('h:i A');

        $contentVariables = json_encode([
            "1" => $date,
            "2" => $time
        ]);

        return $this->sendMessageWithTemplate($to, $contentVariables);
    }

    protected function sendMessageWithTemplate($to, $contentVariables)
    {
        try {
            $message = $this->twilio->messages->create(
                $to, 
                [
                    "from" => $this->from,
                    "contentSid" => "HXb5b62575e6e4ff6129ad7c8efe1f983e",
                    "contentVariables" => $contentVariables,
                    // "body" => "..." // Fallback body is often not needed if ContentSid is present, 
                    // or acts as fallback for SMS. For WhatsApp, ContentSid triggers the template.
                ]
            );
            
            Log::info("Twilio Template Sent to $to: " . $message->sid);
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
