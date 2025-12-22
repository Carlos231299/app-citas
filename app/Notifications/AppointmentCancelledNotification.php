<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class AppointmentCancelledNotification extends Notification
{
    use Queueable;

    protected $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Cita Cancelada: ' . $this->appointment->client_name,
            'message' => "Motivo: " . ($this->appointment->cancellation_reason ?? 'No especificado'),
            'icon' => 'bi-calendar-x',
            'color' => 'danger',
            'url' => route('dashboard')
        ];
    }
}
