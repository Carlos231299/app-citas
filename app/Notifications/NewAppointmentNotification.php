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
            'message' => 'Fecha: ' . $this->appointment->scheduled_at->format('d/m/Y h:i A'),
            'icon' => 'bi-calendar-check',
            'color' => 'success',
            'url' => route('dashboard', ['open_appointment' => $this->appointment->id]),
            'action_type' => 'appointment',
            'action_id' => $this->appointment->id
        ];
    }
}
