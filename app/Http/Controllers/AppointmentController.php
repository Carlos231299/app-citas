<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // Public Booking Page Data
    public function publicIndex()
    {
        $services = Service::all();
        $barbers = Barber::where('is_active', true)->get();
        return view('welcome', compact('services', 'barbers'));
    }

    // Get Slots (Core Logic)
    public function getAvailableSlots(Request $request)
    {
        try {
            $date = Carbon::parse($request->date);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date'], 400);
        }

        // Check Barber Special Mode
        $barberId = $request->barber_id;
        $barber = Barber::find($barberId);
        $isSpecial = $barber->special_mode ?? false;

        $start = 4; // Earliest possible (Special)
        $end = 22;  // Latest possible (Special)

        $slots = [];
        // ... (query existing bookings) ...
        $bookedSlots = Appointment::whereDate('scheduled_at', $date)
            ->where('barber_id', $barberId)
            ->whereNotIn('status', ['cancelled', 'request']) 
            ->pluck('scheduled_at')
            ->map(fn($dt) => $dt->format('H:i'))
            ->toArray();

        // Generate 30-min intervals
        for ($hour = $start; $hour < $end; $hour++) {
            foreach (['00', '30'] as $minute) {
                // Logic Filter
                $isRegular = ($hour >= 8 && $hour < 12) || ($hour >= 13 && $hour < 18);
                
                // Special Early: 4:00 - 7:30 (So hour 4, 5, 6. Hour 7: 00, 30. Hour 7 < 8 is true. Logic: hour < 8)
                // Special Late: 6:30 PM - 10:00 PM. (18:30 starts).
                // Hour 18: If minute 30 -> Special.
                // Hour 19, 20, 21 -> Special.
                
                $isEarlySpecial = ($hour >= 4 && $hour < 8); // 4, 5, 6, 7
                $isLateSpecial = ($hour > 18) || ($hour == 18 && $minute == '30'); // 18:30, 19+, 20+, 21+

                // Determine validity
                $isValid = false;
                
                if ($isRegular) {
                    $isValid = true;
                } elseif ($isSpecial && ($isEarlySpecial || $isLateSpecial)) {
                    $isValid = true;
                }

                if (!$isValid) continue;

                $timeString = sprintf('%02d:%s', $hour, $minute);
                
                // If today, filter past times
                if ($date->isToday() && $timeString < now()->format('H:i')) {
                    continue;
                }

                if (!in_array($timeString, $bookedSlots)) {
                    // Convert to 12h format for display
                    $slots[] = Carbon::createFromFormat('H:i', $timeString)->format('g:i A');
                }
            }
        }

        return response()->json($slots);
    }

    // Book Appointment
    public function store(Request $request, \App\Services\WhatsApp\WhatsAppServiceInterface $whatsappService)
    {
        $request->validate([
            'service_id' => 'required',
            'barber_id' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'client_name' => 'required',
            'client_phone' => 'required',
            'custom_details' => 'nullable|string|max:255'
        ]);

        $scheduledAt = Carbon::parse($request->date . ' ' . $request->time);
        
        // Determine Status & Logic
        $service = Service::find($request->service_id);
        $serviceName = strtolower(trim($service->name));
        
        $isRequest = !empty($request->custom_details) || in_array($serviceName, ['otro', 'otro servicio']);
        $status = $isRequest ? 'request' : 'scheduled';

        $appointment = Appointment::create([
            'service_id' => $request->service_id,
            'barber_id' => $request->barber_id,
            'scheduled_at' => $scheduledAt,
            'client_name' => $request->client_name,
            'client_phone' => $request->client_phone,
            'custom_details' => $request->custom_details,
            'status' => $status
        ]);

        $whatsappUrl = null;
        if($isRequest) {
            $barber = Barber::find($request->barber_id);
            $phone = $barber->whatsapp_number ?? '573000000000'; 
            $msg = "Hola {$barber->name}, soy {$request->client_name}. Quisiera agendar para *{$request->custom_details}* el día {$request->date} a las {$request->time}. Quedo atento a confirmación.";
            $whatsappUrl = "https://wa.me/{$phone}?text=" . urlencode($msg);
        } else {
            // Automated Confirmation for Scheduled Appointments
            try {
                $whatsappService->sendConfirmation($appointment);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Illuminate\Support\Facades\Log::error('WA Notification Error: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with([
            'success' => true, 
            'client_name' => $request->client_name,
            'is_request' => $isRequest,
            'whatsapp_url' => $whatsappUrl
        ]);
    }

    // Admin Dashboard
    public function index()
    {
        // Get today's appointments ordered by time
        $appointments = Appointment::with(['service', 'barber'])
            ->whereDate('scheduled_at', Carbon::today())
            ->where('status', '!=', 'request') // Hide requests from daily dashboard for now if they aren't 'scheduled'
            ->orderBy('scheduled_at')
            ->get();
            
        return view('admin.dashboard', compact('appointments'));
    }

    // Admin: Mark Complete with Price
    public function complete(Request $request, Appointment $appointment)
    {
        $data = ['status' => 'completed'];
        
        if ($request->has('confirmed_price')) {
            $data['confirmed_price'] = $request->confirmed_price;
        }

        $appointment->update($data);

        return request()->wantsJson() 
            ? response()->json(['message' => 'Completada'])
            : redirect()->back()->with('success', 'Cita completada');
    }

    // Admin: Cancel
    public function cancel(Request $request, Appointment $appointment)
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Cancelada por administrador'
        ]);
        
        return request()->wantsJson() 
            ? response()->json(['message' => 'Cancelada'])
            : redirect()->back()->with('success', 'Cita cancelada');
    }
    // Calendar View (FullCalendar)
    public function calendar()
    {
        return view('admin.calendar');
    }

    // JSON Events for FullCalendar
    public function events(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $events = Appointment::with(['service', 'barber'])
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', '!=', 'request') // Exclude requests from calendar
            ->get()
            ->map(function ($appointment) {
                $duration = 30; // Minutes
                $end = $appointment->scheduled_at->copy()->addMinutes($duration);

                return [
                    'id' => $appointment->id,
                    'title' => $appointment->client_name . ' (' . $appointment->service->name . ')',
                    'start' => $appointment->scheduled_at->toIso8601String(),
                    'end' => $end->toIso8601String(),
                    'backgroundColor' => $this->getStatusColor($appointment->status),
                    'borderColor' => $this->getStatusColor($appointment->status),
                    'extendedProps' => [
                        'barber' => $appointment->barber->name,
                        'service' => $appointment->service->name,
                        'status' => $appointment->status,
                        'client_phone' => $appointment->client_phone,
                        'custom_details' => $appointment->custom_details ?? 'Sin detalles adicionales',
                        'price' => $appointment->service->price
                    ]
                ];
            });

        return response()->json($events);
    }

    private function getStatusColor($status)
    {
        return match ($status) {
            'completed' => '#10B981', // Success Green
            'cancelled' => '#EF4444', // Danger Red
            default => '#2563EB', // Primary Blue
        };
    }
}
