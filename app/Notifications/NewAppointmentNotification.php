<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Appointment;

class NewAppointmentNotification extends Notification
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
            'title' => 'Nueva Cita: ' . $this->appointment->client_name,
            'message' => "Fecha: " . $this->appointment->appointment_date . " - " . $this->appointment->appointment_time,
            'icon' => 'bi-calendar-check',
            'color' => 'success',
            'url' => route('dashboard')
        ];
    }
}
