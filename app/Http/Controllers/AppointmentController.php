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
            // Prevent past dates logic
            if ($date->lt(Carbon::today())) {
                 return response()->json([]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date'], 400);
        }

        // Check Barber Special Mode
        $barberId = $request->barber_id;
        $barber = Barber::find($barberId);

        if (!$barber || !$barber->is_active) {
            return response()->json([]);
        }

        $isSpecial = $barber->special_mode ?? false;

        // Check Extra Time Date Range Logic
        if ($isSpecial) {
             $startStr = $barber->extra_time_start;
             $endStr = $barber->extra_time_end;
 
             if ($startStr && $endStr) {
                 $extraStart = Carbon::parse($startStr)->startOfDay();
                 $extraEnd = Carbon::parse($endStr)->endOfDay();
                 
                 // If current date is NOT within range, disable special mode for this request
                 if (!$date->between($extraStart, $extraEnd)) {
                      $isSpecial = false; 
                 }
             } else {
                  // Strict: If dates missing but mode ON, disable to prevent leak.
                  $isSpecial = false;
             }
        }

        $start = 4; // Allow checking from 4 AM
        $end = 22;

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
                
                // Special Early: 4:00 AM - 7:30 AM (Last slot 7:00, ends 7:30)
                $isEarlySpecial = ($hour >= 4 && $hour < 7) || ($hour == 7 && $minute == '00');
                
                // Special Late: 6:30 PM - 10:00 PM (Last slot 21:30, ends 22:00)
                // 18:30 (Yes), 19, 20, 21:00, 21:30 (Yes).
                $isLateSpecial = ($hour > 18) || ($hour == 18 && $minute == '30');

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
            // 'client_phone' => 'required', // Replaced by prefix+number
            'phone_prefix' => 'sometimes|required',
            'phone_number' => 'sometimes|required|min:7',
            'custom_details' => 'nullable|string|max:255'
        ]);

        $scheduledAt = Carbon::parse($request->date . ' ' . $request->time);
        
        // Determine Status & Logic
        $service = Service::find($request->service_id);
        $serviceName = strtolower(trim($service->name));
        
        $isRequest = !empty($request->custom_details) || in_array($serviceName, ['otro', 'otro servicio']);
        
        // If Admin is booking, always 'scheduled' (Confirmed)
        if (auth()->check()) {
            $status = 'scheduled';
            $isRequest = false; // Admin bookings are never requests
        } else {
            $status = $isRequest ? 'request' : 'scheduled';
        }

        if ($request->has(['phone_prefix', 'phone_number'])) {
            $clientPhone = $request->phone_prefix . $request->phone_number;
        } else {
            $clientPhone = $request->client_phone;
        }

        $appointment = Appointment::create([
            'service_id' => $request->service_id,
            'barber_id' => $request->barber_id,
            'scheduled_at' => $scheduledAt,
            'client_name' => $request->client_name,
            'client_phone' => $clientPhone,
            'custom_details' => $request->custom_details,
            'status' => $status
        ]);

        $whatsappUrl = null;
        
        // Sender Number (Twilio Sandbox) - Used for Opt-in/Voucher
        $senderNumber = '14155238886'; 
        
        // Logic for WhatsApp Link or Auto-Send
        if($isRequest) {
            // Public Request
            $barber = Barber::find($request->barber_id);
            $phone = $barber->whatsapp_number ?? $senderNumber; 
            $msg = "Hola {$barber->name}, soy *{$request->client_name}*. Quisiera agendar para *{$request->custom_details}* el día {$request->date} a las {$request->time}. Quedo atento.";
            $whatsappUrl = "https://wa.me/{$phone}?text=" . urlencode($msg);
        } else {
            // Scheduled (Confirmed) - Voucher Flow
            // Fetch Barber for the message details
            $barber = Barber::find($request->barber_id);
            
            $msg = "Hola, soy *{$request->client_name}* 👋.\n\n" .
                   "Acabo de reservar una cita en Barbería JR:\n" .
                   "💈 *Barbero:* {$barber->name}\n" .
                   "✂️ *Servicio:* {$service->name}\n" .
                   "📅 *Fecha:* {$request->date}\n" .
                   "⏰ *Hora:* {$request->time}\n\n" .
                   "Este es mi comprobante. Quedo atento a su confirmación.";
            
            $whatsappUrl = "https://wa.me/{$senderNumber}?text=" . urlencode($msg);

            // Trigger API (Best effort)
            try {
                $whatsappService->sendConfirmation($appointment);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('WA Notification Error: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cita creada exitosamente',
                'whatsapp_url' => $whatsappUrl,
                'is_request' => $isRequest
            ]);
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
        // Stats for Today
        $today = Carbon::today();
        
        $query = Appointment::whereDate('scheduled_at', $today)
            ->where('status', '!=', 'request');

        if (auth()->user()->role !== 'admin') {
            $barberId = auth()->user()->barber?->id;
            if ($barberId) {
                $query->where('barber_id', $barberId);
            } else {
                 // Fallback if not linked
                $query->where('id', 0);
            }
        }

        $todaysAppointments = $query->get();

        $stats = [
            'total_today' => $todaysAppointments->count(),
            'revenue_today' => $todaysAppointments->where('status', 'completed')->sum('price'),
            'pending_today' => $todaysAppointments->where('status', 'scheduled')->count(),
            'active_barbers' => Barber::where('is_active', true)->count()
        ];

        $services = Service::all();
        $barbers = Barber::where('is_active', true)->get();

        // Get appointments for Calendar (handled by API usually, but if initial render needs them?)
        // Actually, calendar fetches via API. We just need the view.
        
        return view('admin.dashboard', compact('stats', 'services', 'barbers'));
    }

    // Admin: Update Appointment
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id'
        ]);

        $scheduledAt = Carbon::parse($validated['date'] . ' ' . $validated['time']);

        $appointment->update([
            'scheduled_at' => $scheduledAt,
            'barber_id' => $validated['barber_id'],
            'service_id' => $validated['service_id']
        ]);

        return response()->json(['message' => 'Cita actualizada correctamente']);
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

        // 1. Fetch Regular Appointments
        // 1. Fetch Regular Appointments
        $query = Appointment::with(['service', 'barber'])
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', '!=', 'request');

        if ($request->filled('barber_id')) {
            $query->where('barber_id', $request->barber_id);
        }

        // PERMISSION CHECK: Non-admins can ONLY see their own appointments
        if (auth()->user()->role !== 'admin') {
            $barberId = auth()->user()->barber?->id;
             if ($barberId) {
                $query->where('barber_id', $barberId);
            } else {
                 $query->where('id', 0); // See nothing
            }
        }

        $events = $query->get()
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
                        'type' => 'appointment',
                        'barber' => $appointment->barber->name,
                         // Passing IDs for Edit Mode
                        'barber_id' => $appointment->barber->id,
                        'service_id' => $appointment->service->id,
                        'service' => $appointment->service->name,
                        'status' => $appointment->status,
                        'client_phone' => $appointment->client_phone,
                        'custom_details' => $appointment->custom_details ?? 'Sin detalles adicionales',
                        'price' => $appointment->service->price,
                        'cancellation_reason' => $appointment->cancellation_reason
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
            'holiday'   => '#0B968B', // Teal/Google Holiday Green
            default => '#2563EB', // Primary Blue
        };
    }
}
