<?php

namespace App\Services\WhatsApp;

use App\Models\Appointment;

interface WhatsAppServiceInterface
{
    public function sendConfirmation(Appointment $appointment);
    public function sendReminder(Appointment $appointment);
    public function sendCancellation(Appointment $appointment, string $reason);
    public function sendGroupAlert(Appointment $appointment);
}
