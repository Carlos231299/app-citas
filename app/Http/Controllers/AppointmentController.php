<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    // Public Booking Page Data
    public function publicIndex()
    {
        $services = Service::orderBy('sort_order')->get();
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
        $end = 24; // Allow checking until end of day

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
                // Lunch Break Logic: Standard break is 12-13.
                // If work_during_lunch is TRUE, we treat 12-13 as regular hours.
                $isLunchHour = ($hour >= 12 && $hour < 13);
                $isWorkHour = ($hour >= 8 && $hour < 22); // 8AM - 10PM (Extended Normal Hours)

                if ($barber->work_during_lunch) {
                    $isRegular = $isWorkHour; // 8-22 (Includes 12-13)
                } else {
                    $isRegular = $isWorkHour && !$isLunchHour; // 8-22 excluding 12-13
                }
                
                // Special Early & Late
                $isEarlySpecial = ($hour >= 4 && $hour < 8); // 4AM - 8AM
                $isLateSpecial = ($hour >= 22); // 22:00+ (10PM onwards)

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
                // 12:00 - 13:00 is Lunch Break
                if ($hour === 12) {
                    $isLunchBreak = true;
                    
                    // Check if barber works during lunch for THIS specific date
                    if ($barber->work_during_lunch && $barber->lunch_work_start && $barber->lunch_work_end) {
                        $checkDate = Carbon::parse($date);
                        $startLunch = Carbon::parse($barber->lunch_work_start)->startOfDay();
                        $endLunch = Carbon::parse($barber->lunch_work_end)->endOfDay();
                        
                        if ($checkDate->between($startLunch, $endLunch)) {
                            $isLunchBreak = false; // It's NOT a break today
                        }
                    }
                    
                    if ($isLunchBreak) {
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
        // If Admin is booking, generally 'scheduled' (Confirmed)
        // BUT if it is 'Otro servicio' (isRequest), it must remain as 'request' for price negotiation.
        if (auth()->check()) {
            if ($isRequest) {
                // Keep as request
                $status = 'request';
            } else {
                $status = 'scheduled';
            }
        } else {
            $status = $isRequest ? 'request' : 'scheduled';
        }

        if ($request->has(['phone_prefix', 'phone_number'])) {
            $clientPhone = $request->phone_prefix . $request->phone_number;
        } else {
            $clientPhone = $request->client_phone;
        }

        // PRICE CALCULATION (Extra Time Logic)
        // Standard Hours: 08:00 - 22:00
        // Extra Time: Before 08:00 OR After 22:00 (10 PM)
        $hour = $scheduledAt->hour;
        $minute = $scheduledAt->minute;
        
        // Extra Time: Before 8:00 AM OR After 22:00 (10 PM)
        $isExtraTime = ($hour < 8 || $hour >= 22);
        
        $finalPrice = $service->price; // Default
        if ($isExtraTime && $service->extra_price > 0) {
            $finalPrice = $service->extra_price;
        }

        \Illuminate\Support\Facades\Log::info("💰 DEBUG PRICE: Time {$scheduledAt->format('H:i')} | Hour: $hour | Extra: " . ($isExtraTime?'YES':'NO') . " | Service Price: {$service->price} | Extra Price: {$service->extra_price} | FINAL: $finalPrice");

        $appointment = Appointment::create([
            'service_id' => $request->service_id,
            'barber_id' => $request->barber_id,
            'scheduled_at' => $scheduledAt,
            'client_name' => $request->client_name,
            'client_phone' => $clientPhone,
            'custom_details' => $request->custom_details,
            'status' => $status,
            'price' => $finalPrice
        ]);
        
        // Notify Admins
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewAppointmentNotification($appointment));
        }

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

            // FORMAT PRICE FOR WHATSAPP
            $formattedPrice = number_format($finalPrice, 0, ',', '.');
            if ($isRequest) {
                 $displayPrice = "Acordar con el barbero";
            } else {
                 $displayPrice = "$" . $formattedPrice;
                 if ($isExtraTime && $service->extra_price > 0) {
                     $displayPrice .= " (Horario Extra)";
                 }
            }

            // Timeout after 2 seconds to avoid 500 Error
            \Illuminate\Support\Facades\Log::info("🤖 Bot Payload: display_price = [{$displayPrice}], is_request = [{$isRequest}]");
            \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/appointment', [
                'phone' => $request->phone_prefix . $request->phone_number,
                'name' => $request->client_name,
                'date' => $request->date,
                'time' => $request->time,
                'place' => 'Barbería JR',
                'barber_name' => $barberName,
                'service_name' => $notificationServiceName,
                'is_request' => $isRequest, // true = waiting, false = confirmed
                'display_price' => $displayPrice
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WA Notification Error: ' . $e->getMessage());
        }


        // NOTIFY BARBER IF IT IS A REQUEST (Otro Servicio)
        if ($isRequest && $request->barber_id) {
             try {
                 $barber = Barber::find($request->barber_id);
                 if ($barber && $barber->whatsapp_number) {
                     $msg = "🔔 *Nueva Solicitud de Cita*\n\n" .
                            "👤 *Cliente:* {$request->client_name}\n" .
                            "📞 *Tel:* {$clientPhone}\n" .
                            "💇‍♂️ *Servicio:* {$serviceName}\n" .
                            "📅 *Fecha:* {$scheduledAt->format('Y-m-d H:i')}\n\n" .
                            "⚠️ Ingresa a la plataforma para aceptar o rechazar.";
                     
                     \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/send-message', [
                         'phone' => $barber->whatsapp_number,
                         'message' => $msg
                     ]);
                 }
             } catch (\Exception $e) {
                 \Illuminate\Support\Facades\Log::error('Barber Notification Error: ' . $e->getMessage());
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

        // [NEW] Fetch Requests (Pending Confirmation)
        $requestQuery = Appointment::where('status', 'request')->orderBy('created_at', 'asc');
        
        if (trim(auth()->user()->role) !== 'admin') {
             $barberId = auth()->user()->barber?->id;
             if ($barberId) $requestQuery->where('barber_id', $barberId);
        }
        $pendingRequests = $requestQuery->get();

        $stats = [
            'total_today' => $todaysAppointments->count(),
            'revenue_today' => $todaysAppointments->where('status', 'completed')->sum(function($appt) {
                return $appt->confirmed_price ?? $appt->price;
            }),
            'pending_today' => $todaysAppointments->where('status', 'scheduled')->count(),
            'completed_today' => $todaysAppointments->where('status', 'completed')->count(),
            'cancelled_today' => $todaysAppointments->where('status', 'cancelled')->count(),
            'active_barbers' => Barber::where('is_active', true)->count(),
            'pending_requests' => $pendingRequests->count() 
        ];

        $services = Service::orderBy('sort_order')->get();
        $barbers = Barber::all();
        $products = \App\Models\Product::where('stock', '>', 0)->orderBy('name')->get();

        return view('admin.dashboard', compact('stats', 'services', 'barbers', 'pendingRequests', 'products'));
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
            'service_id' => 'required|exists:services,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string'
        ]);

        $scheduledAt = Carbon::parse($validated['date'] . ' ' . $validated['time']);

        // RECALCULATE PRICE IF TIME OR SERVICE CHANGED
        $service = Service::find($validated['service_id']);
        $hour = $scheduledAt->hour;
        
        // Extra Time: Before 8:00 AM OR After 22:00 (10 PM)
        $isExtraTime = ($hour < 8 || $hour >= 22);
        
        $finalPrice = $service->price; // Default
        if ($isExtraTime && $service->extra_price > 0) {
            $finalPrice = $service->extra_price;
        }

        \Illuminate\Support\Facades\Log::info("💰 UPDATE PRICE: Time {$scheduledAt->format('H:i')} | Hour: $hour | Extra: " . ($isExtraTime?'YES':'NO') . " | Service Price: {$service->price} | Extra Price: {$service->extra_price} | FINAL: $finalPrice");

        $appointment->update([
            'scheduled_at' => $scheduledAt,
            'barber_id' => $validated['barber_id'],
            'service_id' => $validated['service_id'],
            'client_name' => $validated['client_name'],
            'client_phone' => $validated['client_phone'],
            'custom_details' => $request->custom_details,
            'price' => $finalPrice // RECALCULATED PRICE
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

        try {
            DB::beginTransaction();

            // [NEW] Record who completed the appointment
            $data['completed_by'] = auth()->id();
            $appointment->update($data);

            // Handle Products (POS) - Full Sync with Stock Restoration
            
            // 0. Ensure relationships are loaded
            $appointment->load('products');

            // 1. Restore stock for previously attached products
            foreach ($appointment->products as $existingProduct) {
                $existingProduct->stock += $existingProduct->pivot->quantity;
                $existingProduct->save();
            }
            
            // 2. Clear current attachments
            $appointment->products()->detach();

            // 3. Process new list
            $itemsData = [];
            $productsTotal = 0;

            if ($request->has('products') && is_array($request->products)) {
                foreach ($request->products as $item) {
                    $product = \App\Models\Product::find($item['product_id']);
                    
                    if (!$product) continue;

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para: " . $product->name);
                    }

                    // Attach to pivot
                    $appointment->products()->attach($product->id, [
                        'quantity' => $item['quantity'],
                        'price' => $product->price // Snapshot price
                    ]);

                    $subtotal = $product->price * $item['quantity'];
                    $productsTotal += $subtotal;
                    
                    $itemsData[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal
                    ];

                    // Deduct stock
                    $product->stock -= $item['quantity'];
                    $product->save();
                }
            }

            // [NEW] ONLY create a Sale Record if there are PRODUCTS included
            // Ensure no duplicate sales for this appointment if re-completed
            \App\Models\Sale::where('appointment_id', $appointment->id)->delete();

            $sale = null;
            $grandTotal = $request->confirmed_price ?? ($appointment->price + $productsTotal);

            if (!empty($itemsData)) {
                $sale = \App\Models\Sale::create([
                    'user_id' => auth()->id(),
                    'client_name' => $appointment->client_name,
                    'appointment_id' => $appointment->id,
                    'total' => $productsTotal, // Sale total is ONLY for products
                    'payment_method' => $request->payment_method ?? 'efectivo',
                    'items' => $itemsData, // ONLY products, NO service
                    'completed_at' => now()
                ]);
            }
            
            DB::commit();

            // [NEW] NOTIFY CLIENT VIA WHATSAPP (BOT)
            if ($appointment->client_phone) {
                // 1. Send Text Summary (Always includes service and products total)
                try {
                    $msg = "✅ *¡Cita Finalizada con Éxito!* ✅\n\n" .
                           "Hola *{$appointment->client_name}*, qué gusto saludarte. ✂️✨\n" .
                           "Tu servicio en *Barbería JR* ha sido procesado.\n\n" .
                           "💰 *Total:* " . '$ ' . number_format($grandTotal, 0) . "\n" .
                           "🙏 ¡Gracias por confiar en nosotros para cuidar tu estilo!\n\n" .
                           "¡Esperamos verte pronto por aquí! Recuerda que puedes agendar tu próxima cita cuando desees en la plataforma: https://citasbarberiajr.online. 😉";

                    if ($sale) {
                        $msg .= "\n\nTe adjuntamos tu recibo digital de productos:";
                    }

                    \Illuminate\Support\Facades\Http::timeout(15)->post('http://localhost:3000/send-message', [
                        'phone' => $appointment->client_phone,
                        'message' => $msg
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Bot Text Message Failed: " . $e->getMessage());
                }

                // 2. Send PDF Receipt (ONLY if there are products sold)
                if ($sale) {
                    try {
                        // Generate a temporary signed URL valid for 30 minutes
                        $pdfUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                            'pos.sale.pdf', 
                            now()->addMinutes(30), 
                            ['sale' => $sale->id]
                        );

                        // Dynamic Filename
                        $safeClientName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $appointment->client_name);
                        $filename = "Recibo - {$safeClientName} - " . now()->format('d-m-Y H-i') . ".pdf";
                        
                        \Illuminate\Support\Facades\Http::timeout(30)->post('http://localhost:3000/send-pdf', [
                            'phone' => $appointment->client_phone,
                            'pdf_url' => $pdfUrl,
                            'filename' => $filename
                        ]);

                    } catch (\Exception $botError) {
                        \Illuminate\Support\Facades\Log::error("Bot PDF Receipt Failed: " . $botError->getMessage());
                    }
                }

            }

            // [NEW] ASK FOR RATING (Runs for everyone, even if no products/PDF)
            try {
                 if ($appointment->client_phone) {
                    \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/ask-rating', [
                        'phone' => $appointment->client_phone,
                        'barber_name' => $appointment->barber?->name ?? 'tu barbero'
                    ]);
                 }
            } catch (\Exception $ratingError) {
                \Illuminate\Support\Facades\Log::error("Bot Rating Trigger Failed: " . $ratingError->getMessage());
            }

            return request()->wantsJson() 
                ? response()->json([
                    'success' => true, 
                    'message' => 'Cita completada con éxito.',
                    'sale' => $sale,
                    'client_phone' => $appointment->client_phone
                ])
                : redirect()->back()->with('success', 'Cita completada');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Complete Error: ' . $e->getMessage());
            return request()->wantsJson() 
                ? response()->json(['message' => 'Error: ' . $e->getMessage()], 500)
                : redirect()->back()->with('error', 'Error al completar cita: ' . $e->getMessage());
        }
    }

    // [NEW] Re-open Finished Appointment (Rollback)
    public function reopen(Request $request, Appointment $appointment)
    {
        // Permission Check (Same as complete)
        if (trim(auth()->user()->role) !== 'admin') {
            if (auth()->user()->barber?->id != $appointment->barber_id) {
                 return request()->wantsJson() 
                     ? response()->json(['message' => 'No autorizado.'], 403)
                     : abort(403, 'No tienes permiso para reabrir esta cita.');
            }
        }

        // 1. Restore Stock
        $appointment->load('products');
        foreach ($appointment->products as $existingProduct) {
            $existingProduct->stock += $existingProduct->pivot->quantity;
            $existingProduct->save();
        }

        // 2. Detach Products
        $appointment->products()->detach();

        // 3. Delete associated Sale if exists
        \App\Models\Sale::where('appointment_id', $appointment->id)->delete();

        // 4. Reset Status
        $appointment->update([
            'status' => 'scheduled',
            'confirmed_price' => null,
            'completed_by' => null
        ]);

        return request()->wantsJson() 
            ? response()->json(['message' => 'Reabierta'])
            : redirect()->back()->with('success', 'Cita reabierta y stock restaurado.');
    }

    public function destroy(Appointment $appointment)
    {
        // Permission Check: Admin Only for Deletion (safest) or Owner Barber
        if (trim(auth()->user()->role) !== 'admin') {
             return request()->wantsJson() 
                 ? response()->json(['message' => 'No autorizado. Solo admin puede eliminar.'], 403)
                 : abort(403, 'No autorizado.');
        }

        // 1. If Completed, Restore Stock
        if ($appointment->status === 'completed') {
            $appointment->load('products');
            foreach ($appointment->products as $existingProduct) {
                // Restore logic 
                $existingProduct->stock += $existingProduct->pivot->quantity;
                $existingProduct->save();
            }
        }

        // 2. Detach everything
        $appointment->products()->detach();
        
        // 3. Delete associated Sale if exists
        \App\Models\Sale::where('appointment_id', $appointment->id)->delete();

        // 4. Delete
        $appointment->delete();

        return request()->wantsJson() 
            ? response()->json(['message' => 'Eliminada'])
            : redirect()->back()->with('success', 'Cita eliminada permanentemente.');
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

        // NOTIFY CLIENT VIA WHATSAPP
        try {
            $msg = "❌ *Cita Cancelada/Rechazada* ❌\n\n" .
                   "Hola *{$appointment->client_name}*,\n" .
                   "Tu cita para *{$appointment->service->name}* ha sido cancelada.\n\n" .
                   "📝 *Motivo:* " . ($request->reason ?? 'No especificado') . "\n\n" .
                   "Te invitamos a agendar nuevamente.";

            \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/send-message', [
                'phone' => $appointment->client_phone,
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Client Cancellation Notification Error: ' . $e->getMessage());
        }
        
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
    // Bot: Cancel Appointment via API
    public function cancelFromBot(Request $request)
    {
        try {
        \Illuminate\Support\Facades\Log::info("🤖 Bot Cancel Request Received. Phone: {$request->phone}, Reason: {$request->reason}");
    } catch (\Exception $e) {
        // Log failure shouldn't crash the request
    }

        $request->validate([
            'phone' => 'required',
            'reason' => 'required'
        ]);

        $phone = $request->phone;
        
        // Robust Phone Matching: Use last 10 digits to match DB
        // Remove non-digits
        $digits = preg_replace('/\D/', '', $phone);
        $last10 = substr($digits, -10);

        // Find the MOST RECENTLY CREATED appointment for this phone
        // matching the user's "undo last action" intent.
        $appointment = Appointment::where('client_phone', 'LIKE', "%$last10")
            ->whereIn('status', ['scheduled', 'request'])
            ->orderBy('created_at', 'desc') // Target the one just created
            ->first();

        if (!$appointment) {
             // Fallback: Try with + prefix just in case
             $appointment = Appointment::where('client_phone', '+' . $digits)
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>=', Carbon::now())
                ->first();
        }

        if (!$appointment) {
             return response()->json(['success' => false, 'message' => 'No active appointment found for phone: ' . $phone], 404);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason
        ]);

        // NOTIFY BARBER OF CANCELLATION
        // Trigger System Notification
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\AppointmentCancelledNotification($appointment));
        }

        if ($appointment->barber && $appointment->barber->whatsapp_number) {
             try {
                 $msg = "❌ *Cita Cancelada por Cliente* ❌\n\n" .
                        "👤 *Cliente:* {$appointment->client_name}\n" .
                        "📞 *Tel:* {$appointment->client_phone}\n" .
                        "💇‍♂️ *Servicio:* {$appointment->service->name}\n" .
                        "📅 *Fecha:* {$appointment->scheduled_at->format('Y-m-d H:i')}\n\n" .
                        "📝 *Motivo:* " . ($request->reason ?? 'No especificado');
                 
                 \Illuminate\Support\Facades\Http::timeout(2)->post('http://localhost:3000/send-message', [
                     'phone' => $appointment->barber->whatsapp_number,
                     'message' => $msg
                 ]);
             } catch (\Exception $e) {
                 \Illuminate\Support\Facades\Log::error('Barber Cancel Notification Error: ' . $e->getMessage());
             }
        }

        return response()->json(['success' => true, 'message' => 'Cancelled']);
    }

    // Calendar View (FullCalendar)
    public function calendar()
    {
        return view('admin.calendar');
    }

    // Single Appointment Details (For Notifications/Deep Linking)
    public function show(Appointment $appointment)
    {
        // Permissions
        if (trim(auth()->user()->role) !== 'admin') {
             $barberId = auth()->user()->barber?->id;
             if (!$barberId || $appointment->barber_id !== $barberId) {
                 return response()->json(['error' => 'Unauthorized'], 403);
             }
        }

        $appointment->load(['service', 'barber', 'products', 'completedBy']);
        
        $duration = 30;
        $end = $appointment->scheduled_at->copy()->addMinutes($duration);

        // Format products for frontend
        $formattedProducts = $appointment->products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'qty' => $p->pivot->quantity,
                'price' => $p->pivot->price // Historical price
            ];
        });

        // Exact same structure as 'events' method extendedProps
        $eventData = [
            'id' => $appointment->id,
            'title' => $appointment->client_name . ' (' . $appointment->service->name . ')',
            'start' => $appointment->scheduled_at->toIso8601String(),
            'end' => $end->toIso8601String(),
            'backgroundColor' => $this->getStatusColor($appointment->status),
            'borderColor' => $this->getStatusColor($appointment->status),
            'allDay' => false,
            'extendedProps' => [
                'type' => 'appointment',
                'barber' => $appointment->barber->name,
                'barber_id' => $appointment->barber->id,
                'service_id' => $appointment->service->id,
                'service' => $appointment->service->name,
                'status' => $appointment->status,
                'client_phone' => $appointment->client_phone,
                'custom_details' => $appointment->custom_details ?? 'Sin detalles adicionales',
                'cancellation_reason' => $appointment->cancellation_reason,
                'price' => $appointment->price, // Stored Price (Base or Extra)
                'base_price' => $appointment->service->price, // Service Base Price
                'extra_price' => $appointment->service->extra_price, // Service Extra Price
                'final_price' => $appointment->confirmed_price,
                'completed_by' => $appointment->completedBy->name ?? null,
                'products' => $formattedProducts
            ]
        ];

        return response()->json($eventData);
    }

    // JSON Events for FullCalendar
    public function events(Request $request)
    {
        $start = Carbon::parse($request->start)->startOfDay();
        $end = Carbon::parse($request->end)->endOfDay();

        // 1. Fetch Regular Appointments
        $query = Appointment::with(['service', 'barber', 'products', 'completedBy'])
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', '!=', 'request')
            ->orderBy('scheduled_at', 'asc');

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
                        'client_name' => $appointment->client_name,
                        'client_phone' => $appointment->client_phone,
                        'custom_details' => $appointment->custom_details ?? 'Sin detalles adicionales',
                        'price' => $appointment->confirmed_price ?? $appointment->price,
                        'base_price' => $appointment->service->price, 
                        'extra_price' => $appointment->service->extra_price,
                        'final_price' => $appointment->confirmed_price,
                        'cancellation_reason' => $appointment->cancellation_reason,
                        'products' => $appointment->products->map(function($prod) {
                            return [
                                'id' => $prod->id,
                                'name' => $prod->name,
                                'price' => $prod->pivot->price,
                                'qty' => $prod->pivot->quantity
                            ];
                        }),
                        'completed_by' => $appointment->completedBy->name ?? null
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
    public function search(Request $request)
    {
        $query = $request->get('query');
        if (!$query) return response()->json([]);

        $appointments = Appointment::with(['barber', 'service'])
            ->where('client_name', 'like', "%{$query}%")
            ->orderBy('scheduled_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'title' => $app->service->name,
                    'start' => $app->scheduled_at,
                    'status' => $app->status,
                    'client_name' => $app->client_name,
                    'barber_name' => $app->barber->name,
                    'scheduled_at_formatted' => Carbon::parse($app->scheduled_at)->format('d/m/Y h:i A')
                ];
            });

        return response()->json($appointments);
    }
    public function getSlotsForAgenda(Request $request)
    {
        $dateStr = $request->get('date');
        $barberId = $request->get('barber_id');
        
        if (!$dateStr) return response()->json([]);

        $date = Carbon::parse($dateStr);
        $barbers = $barberId ? \App\Models\Barber::where('id', $barberId)->get() : \App\Models\Barber::where('is_active', true)->get();
        
        $allSlots = [];

        foreach ($barbers as $barber) {
             // Logic simplified from existing slots() method
             $dayOfWeek = strtolower($date->format('l'));
             $schedule = is_string($barber->schedule) ? json_decode($barber->schedule, true) : $barber->schedule;
             
             if (!isset($schedule[$dayOfWeek]) || !$schedule[$dayOfWeek]['active']) continue;

             $start = Carbon::parse($dateStr . ' ' . $schedule[$dayOfWeek]['start']);
             $end = Carbon::parse($dateStr . ' ' . $schedule[$dayOfWeek]['end']);
             
             $bookedSlots = Appointment::where('barber_id', $barber->id)
                ->whereDate('scheduled_at', $date)
                ->where('status', '!=', 'cancelled')
                ->get()
                ->map(fn($a) => $a->scheduled_at->format('H:i'))
                ->toArray();

             $curr = $start->copy();
             while ($curr->lt($end)) {
                 $time24 = $curr->format('H:i');
                 if (!in_array($time24, $bookedSlots)) {
                     // Return in 12-hour format for frontend
                     $time12 = $curr->format('g:i A');
                     $allSlots[] = [
                         'time' => $time12,
                         'barber_id' => $barber->id,
                         'barber_name' => $barber->name
                     ];
                 }
                 $curr->addMinutes(30);
             }
        }

        return response()->json($allSlots);
    }
    // --- API NOTIFICATIONS FOR LOCAL BOT --- //

    public function getPendingNotifications()
    {
        // Fetch appointments created recently (e.g., last 24h) that haven't been notified to barber
        $pending = Appointment::with(['barber', 'service'])
            ->where('barber_notification_sent', false)
            ->whereIn('status', ['scheduled', 'request']) 
            ->where('created_at', '>=', now()->subHours(24))
            ->whereHas('barber', function($q) {
                $q->whereNotNull('whatsapp_number')->where('whatsapp_number', '!=', '');
            })
            ->get();

        // Format for bot
        $data = $pending->map(function($appt) {
            $serviceName = $appt->service->name;
            if($appt->custom_details) $serviceName .= " ({$appt->custom_details})";

            return [
                'id' => $appt->id,
                'barber_phone' => $appt->barber->whatsapp_number,
                'barber_name' => $appt->barber->name,
                'client_name' => $appt->client_name,
                'service' => $serviceName,
                'time' => $appt->scheduled_at->format('h:i A'),
                'date' => $appt->scheduled_at->format('d/m/Y'),
                'is_request' => $appt->status === 'request'
            ];
        });

        return response()->json($data);
    }

    public function markNotificationSent(Request $request)
    {
        $request->validate(['id' => 'required|exists:appointments,id']);
        
        $appt = Appointment::find($request->id);
        $appt->update(['barber_notification_sent' => true]);

        return response()->json(['success' => true]);
    }

    // --- API REMINDERS (15 MIN BEFORE) --- //

    public function getPendingReminders()
    {
        // 1. Get appointments scheduled between NOW and NOW + 20 MINS
        // 2. That haven't had reminder sent
        // 3. Status is scheduled
        
        $startWindow = now();
        $endWindow = now()->addMinutes(20);

        $reminders = Appointment::with(['barber', 'service'])
            ->where('reminder_15min_sent', false)
            ->where('status', 'scheduled') // Only confirmed appointments
            ->whereBetween('scheduled_at', [$startWindow, $endWindow])
            ->whereNotNull('client_phone')
            ->get();

        $data = $reminders->map(function($appt) {
            $serviceName = $appt->service->name;
            if($appt->custom_details) $serviceName .= " ({$appt->custom_details})";
            
            // Calculate Display Price
            $formattedPrice = number_format($appt->price, 0, ',', '.');
             // Check for Extra Time logic if needed, simplify for reminder: use confirmed or base
            $finalPrice = $appt->confirmed_price ?? $appt->price;
            $displayPrice = "$" . number_format($finalPrice, 0, ',', '.');
            if ($appt->is_extra_time) $displayPrice .= " (Horario Extra)";

            return [
                'id' => $appt->id,
                'phone' => $appt->client_phone,
                'client_name' => $appt->client_name,
                'barber_name' => $appt->barber->name,
                'service_name' => $serviceName,
                'time' => $appt->scheduled_at->format('h:i A'),
                'date' => $appt->scheduled_at->format('Y-m-d'), // Format for message
                'display_price' => $displayPrice,
                'is_request' => false // Reminders are for confirmed slots generally
            ];
        });

        return response()->json($data);
    }

    public function markReminderSent(Request $request)
    {
        $request->validate(['id' => 'required|exists:appointments,id']);
        Appointment::where('id', $request->id)->update(['reminder_15min_sent' => true]);
        return response()->json(['success' => true]);
    }

    // --- API BOT CONFIRMATION --- //
    public function confirmFromBot(Request $request)
    {
        $request->validate(['phone' => 'required']);
        
        // Find active appointment for this phone
        // Similar logic to cancelFromBot but for Confirmation action
        $digits = preg_replace('/\D/', '', $request->phone);
        $last10 = substr($digits, -10);

        $appointment = Appointment::where('client_phone', 'LIKE', "%$last10")
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now()->subHours(1)) // Actively valid
            ->orderBy('created_at', 'desc')
            ->first();

        if ($appointment && $appointment->barber && $appointment->barber->whatsapp_number) {
            try {
                $msg = "✅ *Confirmación de Cita*\n\n" .
                       "👤 *Cliente:* {$appointment->client_name}\n" .
                       "📞 *Tel:* {$appointment->client_phone}\n" .
                       "📅 *Hora:* {$appointment->scheduled_at->format('h:i A')}\n\n" .
                       "Ha confirmado su asistencia vía WhatsApp.";
                
                // Send to Barber via Local Bot (reuse the generic send endpoint logic if calling from within bot, 
                // BUT wait, this API is CALLED by the BOT.
                // If the BOT calls this, we can't tell the BOT to send a message via HTTP response easily 
                // unless we return it.
                // Better approach: The BOT sends the message to the barber directly?
                // NO, keeping logic centrally is good, but here the Server is remote.
                // The Server cannot talk to the Bot to send a message (no ingress).
                // SO: The Bot calls this endpoint to LOG the confirmation (maybe?) 
                // AND the BOT itself should send the message to the barber?
                // Actually the user wants " notify the barber".
                // Since the Bot initiates this with "1", the BOT is closest to the Barber's phone number.
                // However, the Bot doesn't know the Barber's number unless we send it in the reminder payload?
                // OPTION A: The Bot sends the confirmation to Barber. (Needs Barber Phone in State).
                // OPTION B: This API queues a notification? (Complex).
                
                // LET'S DO THIS: 
                // The `confirmFromBot` will return the BARBER'S PHONE and MESSAGE to the Bot.
                // The Bot will then send it.
                
                return response()->json([
                    'success' => true,
                    'action' => 'notify_barber',
                    'barber_phone' => $appointment->barber->whatsapp_number,
                    'message' => $msg
                ]);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Logged']);
    }
}
