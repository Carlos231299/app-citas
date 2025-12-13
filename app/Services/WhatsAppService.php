<?php

namespace App\Services;

class WhatsAppService
{
    protected $baseUrl = 'https://wa.me/';

    /**
     * Generate a link for the client to confirm/view appointment details
     */
    public function generateClientLink($phone, $name, $service, $date, $time)
    {
        $message = "Hola *$name*, tu cita para *$service* está confirmada para el *$date* a las *$time*. \n\n¡Gracias por preferir Barbería JR!";
        return $this->buildLink($phone, $message);
    }

    /**
     * Generate a link to notify the barber of a new booking
     */
    public function generateBarberLink($phone, $clientName, $service, $date, $time)
    {
        $message = "📅 *Nueva Cita* \nCliente: *$clientName* \nServicio: *$service* \nFecha: *$date* \nHora: *$time*";
        return $this->buildLink($phone, $message);
    }

    protected function buildLink($phone, $message)
    {
        // Remove non-numeric characters from phone
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure country code (assuming 57 for Colombia based on user location context, or generic)
        if (strlen($cleanPhone) == 10) {
            $cleanPhone = '57' . $cleanPhone;
        }

        return $this->baseUrl . $cleanPhone . '?text=' . urlencode($message);
    }

    /**
     * Send a real WhatsApp message via local Node.js service
     */
    public function sendMessage($phone, $message)
    {
        try {
            // Call the local Node.js service
            $response = \Illuminate\Support\Facades\Http::post('http://localhost:3000/send', [
                'phone' => $phone,
                'message' => $message
            ]);

            if ($response->successful()) {
                \Illuminate\Support\Facades\Log::info("WA Sent to $phone");
                return true;
            } else {
                \Illuminate\Support\Facades\Log::error("WA Failed: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("WA Connection Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Confirmation Message
     */
    public function sendConfirmation($appointment)
    {
        $phone = $appointment->client_phone;
        $date = $appointment->scheduled_at->format('Y-m-d');
        $time = $appointment->scheduled_at->format('h:i A');
        $name = $appointment->client_name;
        $service = $appointment->service->name;

        $msg = "Hola *$name*! 👋\n\nTu cita en *Barbería JR* ha sido confirmada.\n\n✂️ Servicio: *$service*\n📅 Fecha: *$date*\n⏰ Hora: *$time*\n\n¡Te esperamos!";

        // Send via Node Service
        return $this->sendMessage($phone, $msg);
    }
}
