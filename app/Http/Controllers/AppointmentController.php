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
        
        // Permission Check: Admin can book for anyone. Barber can ONLY book for themselves.
        if (auth()->check() && trim(auth()->user()->role) !== 'admin') {
             $userBarberId = auth()->user()->barber?->id;
             // If not a barber or trying to book for someone else
             if (!$userBarberId || $request->barber_id != $userBarberId) {
                 return response()->json(['message' => 'Solo puedes agendar citas para ti mismo.'], 403);
             }
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
        
        // DEBUG: Trace what the server sees
        \Illuminate\Support\Facades\Log::info("🔍 Booking Debug: Service Name = [{$serviceName}], DB is_custom = [{$service->is_custom}]");

        // Logic: Request (Pre-reservada) IF:
        // 1. Service is marked 'is_custom' in DB
        // 2. Name contains 'otro'
        
        // Native PHP check for robustness
        $isOtro = (strpos($serviceName, 'otro') !== false) || ($service->is_custom == 1);
        
        $isRequest = $isOtro; // Strictly only for "Otro" type services
        
        \Illuminate\Support\Facades\Log::info("🔍 Booking Debug: Result isRequest = [" . ($isRequest ? 'TRUE' : 'FALSE') . "]");

        // If Admin is booking, always 'scheduled' (Confirmed)
        if (auth()->check()) {
            $status = 'scheduled';
            $isRequest = false;
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
        
        // Sender Number (Twilio Sandbox) - unused now but kept for legacy
        $senderNumber = '14155238886'; 
        
        // Fetch Objects for Notification
        $barberObj = Barber::find($request->barber_id);
        $barberName = $barberObj ? $barberObj->name : 'Barbería JR';
        
        // Logic for WhatsApp Link (Frontend) vs Auto-Send (Bot)
        if($isRequest) {
            // Public Request -> "Otro Servicio"
            // Show alert "Solicitud Creada", but NO WhatsApp Link (User request: "borrar todo lo que tenga que ver con el comprobante")
            // Instead, we will trigger the Bot to send a "Waiting Confirmation" message.
            $whatsappUrl = null; 
        } else {
            // Scheduled (Confirmed)
            // No manual voucher link anymore.
            $whatsappUrl = null;
        }

        // 🚀 ALWAYS Send Notification to Local Bot (if active)
        try {
            $notificationServiceName = $service->name;
            if ($request->custom_details) {
                 $notificationServiceName .= " ({$request->custom_details})";
            }

            // Timeout after 2 seconds to avoid 500 Error
            \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/appointment', [
                'phone' => $request->phone_prefix . $request->phone_number,
                'name' => $request->client_name,
                'date' => $request->date,
                'time' => $request->time,
                'place' => 'Barbería JR',
                'barber_name' => $barberName,
                'service_name' => $notificationServiceName,
                'is_request' => $isRequest // true = waiting, false = confirmed
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WA Notification Error: ' . $e->getMessage());
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

        // [NEW] Fetch Requests (Pending Confirmation)
        $requestQuery = Appointment::where('status', 'request')->orderBy('created_at', 'asc');
        
        if (trim(auth()->user()->role) !== 'admin') {
             $barberId = auth()->user()->barber?->id;
             if ($barberId) $requestQuery->where('barber_id', $barberId);
        }
        $pendingRequests = $requestQuery->get();

        $stats = [
            'total_today' => $todaysAppointments->count(),
            'revenue_today' => $todaysAppointments->where('status', 'completed')->sum('price'),
            'pending_today' => $todaysAppointments->where('status', 'scheduled')->count(),
            'active_barbers' => Barber::where('is_active', true)->count(),
            'pending_requests' => $pendingRequests->count() 
        ];

        $services = Service::all();
        $barbers = Barber::all();

        return view('admin.dashboard', compact('stats', 'services', 'barbers', 'pendingRequests'));
    }

    // Admin: Update Appointment
    public function update(Request $request, Appointment $appointment)
    {
        // Permission Check
        if (trim(auth()->user()->role) !== 'admin') {
            if (auth()->user()->barber?->id != $appointment->barber_id) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }
        }

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
        // Permission Check
        if (trim(auth()->user()->role) !== 'admin') {
            if (auth()->user()->barber?->id != $appointment->barber_id) {
                 return request()->wantsJson() 
                     ? response()->json(['message' => 'No autorizado.'], 403)
                     : abort(403, 'No tienes permiso para completar esta cita.');
            }
        }

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
        // Permission Check
        if (trim(auth()->user()->role) !== 'admin') {
            if (auth()->user()->barber?->id != $appointment->barber_id) {
                 return request()->wantsJson() 
                     ? response()->json(['message' => 'No autorizado.'], 403)
                     : abort(403, 'No tienes permiso para cancelar esta cita.');
            }
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Cancelada por administrador'
        ]);
        
        return request()->wantsJson() 
            ? response()->json(['message' => 'Cancelada'])
            : redirect()->back()->with('success', 'Cita cancelada');
    }

    // Admin: Confirm Request (Otro Servicio -> Scheduled)
    public function confirm(Request $request, Appointment $appointment)
    {
        // Permission Check
        if (trim(auth()->user()->role) !== 'admin') {
            if (auth()->user()->barber?->id != $appointment->barber_id) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }
        }

        try {
            // Default to service price if not provided or valid
            $finalPrice = $request->input('price');
            if (is_null($finalPrice) || $finalPrice === '') {
                 $finalPrice = $appointment->service->price;
            }
    
            $appointment->update([
                'status' => 'scheduled',
                'price' => $finalPrice,
            ]);
    
            // Send WhatsApp Confirmation (Manual Trigger via Bot)
            $serviceName = $appointment->service->name;
            if ($appointment->custom_details) {
                 $serviceName .= " ({$appointment->custom_details})";
            }
            
            // Timeout after 2 seconds to avoid 500 Error if Tunnel is down
            
            try {
                \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/appointment', [
                    'phone' => $appointment->client_phone,
                    'name' => $appointment->client_name,
                    'date' => $appointment->scheduled_at->format('Y-m-d'),
                    'time' => $appointment->scheduled_at->format('g:i A'),
                    'barber_name' => $appointment->barber->name,
                    'service_name' => $serviceName,
                    'is_request' => false, // FALSE sets 'Confirmed' template
                    'display_price' => 'Acordar con el Barbero'
                ]);
            } catch (\Exception $botError) {
                // Log bot error but DO NOT fail the request
                \Illuminate\Support\Facades\Log::error("Bot Trigger Failed (Confirm): " . $botError->getMessage());
            }
            
            
            return response()->json(['message' => 'Solicitud confirmada']);

        } catch (\Throwable $e) {
            // WRITE ERROR TO PUBLIC FILE for debugging
            try {
                file_put_contents(public_path('last_error.txt'), date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            } catch (\Exception $writeErr) {
                // Ignore write error
            }

            \Illuminate\Support\Facades\Log::error("Critical Confirm Error: " . $e->getMessage());
            return response()->json([
                'message' => 'Error interno al confirmar',
                'error' => $e->getMessage()
            ], 500);
        }
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
