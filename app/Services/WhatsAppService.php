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
}
