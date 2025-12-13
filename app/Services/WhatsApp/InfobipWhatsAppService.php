<?php

namespace App\Services\WhatsApp;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InfobipWhatsAppService implements WhatsAppServiceInterface
{
    protected $apiKey = 'cada509515b08ade588b923f53bf1f6e-5283bfeb-ef7e-467b-b3b0-700b4626054a';
    protected $baseUrl = 'https://vy6lye.api.infobip.com';
    protected $sender = '447860088970';

    /**
     * Send a template message via Infobip API
     */
    protected function sendInfobipTemplate($to, $templateName, $language, $templateData)
    {
        try {
            // Clean phone number
            $cleanPhone = preg_replace('/[^0-9]/', '', $to);
            if (strlen($cleanPhone) == 10) {
                $cleanPhone = '57' . $cleanPhone;
            }

            $response = Http::withHeaders([
                'Authorization' => 'App ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->baseUrl . '/whatsapp/1/message/template', [
                'messages' => [
                    [
                        'from' => $this->sender,
                        'to' => $cleanPhone,
                        'content' => [
                            'templateName' => $templateName,
                            'templateData' => $templateData,
                            'language' => $language
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                Log::info("Infobip Sent to $cleanPhone: " . $response->body());
                return $response->json();
            } else {
                Log::error("Infobip Failed: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Infobip Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendConfirmation(Appointment $appointment)
    {
        $name = explode(' ', $appointment->client_name)[0]; // First name only

        // Using 'test_whatsapp_template_en' as requested by user
        return $this->sendInfobipTemplate(
            $appointment->client_phone,
            'test_whatsapp_template_en',
            'en',
            ['body' => ['placeholders' => [$name]]]
        );
    }

    public function sendReminder(Appointment $appointment)
    {
        Log::info("Infobip: Send Reminder mock (Not implemented yet)");
        return true;
    }

    public function sendCancellation(Appointment $appointment, string $reason)
    {
        Log::info("Infobip: Send Cancellation mock (Not implemented yet)");
        return true;
    }

    public function sendGroupAlert(Appointment $appointment)
    {
        Log::info("Infobip: Send Group Alert mock (Not implemented yet)");
        return true;
    }
}
