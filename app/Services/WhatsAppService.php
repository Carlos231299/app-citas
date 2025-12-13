<?php

namespace App\Services;

class WhatsAppService
{
    protected $apiKey = 'cada509515b08ade588b923f53bf1f6e-5283bfeb-ef7e-467b-b3b0-700b4626054a';
    protected $baseUrl = 'https://vy6lye.api.infobip.com';
    protected $sender = '447860088970';

    /**
     * Send a template message via Infobip API
     */
    public function sendInfobipTemplate($to, $templateName, $language, $templateData)
    {
        try {
            // Clean phone number
            $cleanPhone = preg_replace('/[^0-9]/', '', $to);
            if (strlen($cleanPhone) == 10) {
                // Ensure usage of the user's test number strictly or append country code
                // For the user request, they specifically asked to test "573042189080"
                // Ideally we should adhere to E.164.
                // If it's 10 digits (Colombia), add 57
                $cleanPhone = '57' . $cleanPhone;
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
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
                \Illuminate\Support\Facades\Log::info("Infobip Sent to $cleanPhone: " . $response->body());
                return $response->json();
            } else {
                \Illuminate\Support\Facades\Log::error("Infobip Failed: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Infobip Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Confirmation Message
     * For now, we use the TEST template as requested to verify connectivity.
     */
    public function sendConfirmation($appointment)
    {
        // For the specific test user request:
        // Template: test_whatsapp_template_en
        // Placeholders: ["Soporte"] -> Mapped to Client Name for personalization
        
        $name = explode(' ', $appointment->client_name)[0]; // First name only

        return $this->sendInfobipTemplate(
            $appointment->client_phone,
            'test_whatsapp_template_en',
            'en',
            ['body' => ['placeholders' => [$name]]]
        );
    }
}
