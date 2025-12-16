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
        // Fetch ALL barbers to show unavailability messages
        $barbers = Barber::all();
        return view('welcome', compact('services', 'barbers'));
    }

    // Get Slots (Core Logic)
    public function getAvailableSlots(Request $request)
    {
        try {
            $date = Carbon::parse($request->date);
            if ($date->lt(Carbon::today())) {
                 return response()->json([]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date'], 400);
        }

        $barberId = $request->barber_id;
        $barber = Barber::find($barberId);

        if (!$barber) {
            return response()->json([]);
        }

        // Check for Special Mode (Extra Time)
        // Logic: Special Mode acts INDEPENDENTLY of is_active status.
        // If Special Mode is ON and Date is within Range, allow slots regardless of is_active.
        $isSpecial = $barber->special_mode ?? false;
        
        if ($isSpecial) {
             $startStr = $barber->extra_time_start;
             $endStr = $barber->extra_time_end;
 
             if ($startStr && $endStr) {
                 $extraStart = Carbon::parse($startStr)->startOfDay();
                 $extraEnd = Carbon::parse($endStr)->endOfDay();
                 
                 // If current date is NOT within extra time range, disable special mode for this check
                 if (!$date->between($extraStart, $extraEnd)) {
                      $isSpecial = false; 
                 }
             } else {
                  $isSpecial = false;
             }
        }

        // Base Active Check: Only enforce if NOT special.
        // If not special, and inactive, then return empty.
        // Also check "Unavailable" range if it overlaps.
        
        // Check for Temporary Unavailability Range (Pre-fetch)
        $unavailableStart = $barber->unavailable_start;
        $unavailableEnd = $barber->unavailable_end;
        $isTemprorarilyUnavailable = false;

        if ($unavailableStart && $unavailableEnd) {
             $dayStart = $date->copy()->startOfDay();
             $dayEnd = $date->copy()->endOfDay();
             
             // If Unavailability covers the ENTIRE day...
             // If Unavailability covers the ENTIRE day...
             if ($unavailableStart->lte($dayStart) && $unavailableEnd->gte($dayEnd)) {
                  // If NOT special mode for this day, then return empty.
                  // If IS special, we continue, but regular slots will be blocked by slot-check logic or explicit flag.
                  if (!$isSpecial) {
                      return response()->json([]); 
                  }
             }
             $isTemprorarilyUnavailable = true;
        }

        if (!$isSpecial) {
             // Basic Active Check (Only if NOT special)
             if (!$barber->is_active) {
                 return response()->json([]);
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
                
                // Special Early & Late
                $isEarlySpecial = ($hour >= 4 && $hour < 7) || ($hour == 7 && $minute == '00');
                $isLateSpecial = ($hour > 18) || ($hour == 18 && $minute == '30'); // 18:30+

                // Determine validity
                $isValid = false;
                
                if ($isRegular && $barber->is_active) {
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

                // CHECK UNAVAILABILITY RANGE PER SLOT
                // Exemption: Special Slots (Extra Time) ignore unavailability (User Request)
                $isSpecialSlot = ($isSpecial && ($isEarlySpecial || $isLateSpecial));
                
                if ($isTemprorarilyUnavailable && !$isSpecialSlot) {
                     $slotDateTime = $date->copy()->setTime($hour, (int)$minute);
                     
                     // Strict block if inside invalid range
                     if ($slotDateTime->gte($unavailableStart) && $slotDateTime->lt($unavailableEnd)) {
                         continue;
                     }
                }

                if (!in_array($timeString, $bookedSlots)) {
                    $slots[] = Carbon::createFromFormat('H:i', $timeString)->format('g:i A');
                }
            }
        }




        return response()->json($slots);
    }

    // Book Appointment
    public function store(Request $request)
    {
        // Permission Check: Only Admin can book internally
        // Public booking is different (publicIndex), but this store is used by both?
        // Wait! This function handles PUBLIC booking via POST /book too!
        // Route::post('/book', ...) is Public (Line 15 web.php).
        
        // ISSUE: public bookings must be allowed.
        // Dashboard uses the SAME route?
        // Let's check dashboard.blade.php submitAdminBooking -> axios.post("{{ route('book') }}")
        
        // If the route is shared, we differentiate by Auth.
        // If Auth::check(), it's an internal booking.
        // User wants "Barber cannot book".
        // So if (auth()->check() && auth()->user()->role !== 'admin') -> ABORT.
        
        if (auth()->check() && trim(auth()->user()->role) !== 'admin') {
             return response()->json(['message' => 'No tienes permisos para agendar citas.'], 403);
        }

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
        ], [
            'service_id.required' => 'Por favor selecciona un servicio.',
            'barber_id.required' => 'Selecciona un barbero de la lista.',
            'date.required' => 'La fecha es obligatoria.',
            'time.required' => 'Selecciona una hora disponible.',
            'client_name.required' => 'Ingresa tu nombre para la reserva.',
            'phone_number.required' => 'El número de celular es obligatorio.',
            'phone_number.min' => 'El número de celular debe tener al menos 7 dígitos.'
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
        
        \Illuminate\Support\Facades\Log::info('Dashboard Access: User ' . auth()->id() . ' Role: ' . auth()->user()->role);

        $query = Appointment::whereDate('scheduled_at', $today)
            ->where('status', '!=', 'request');

        if (trim(auth()->user()->role) !== 'admin') {
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
        $barbers = Barber::all();

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
        if (trim(auth()->user()->role) !== 'admin') {
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
