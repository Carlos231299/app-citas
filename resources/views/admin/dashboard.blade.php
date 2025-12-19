@extends('layouts.admin')

@section('title', 'Dashboard - Barbería JR')
@section('header', 'Agenda')

@section('content')
<div class="d-flex flex-column h-100">
    <!-- Stats Row -->
    <!-- Stats Row -->
    @if(isset($pendingRequests) && $pendingRequests->count() > 0)
    <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center" role="alert">
        <div class="flex-grow-1">
            <h5 class="alert-heading fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Solicitudes Pendientes</h5>
            <p class="mb-0 small">Tienes <strong>{{ $pendingRequests->count() }}</strong> cita(s) esperando confirmación y precio final.</p>
        </div>
        <button class="btn btn-light text-info fw-bold shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#pendingRequestsList" aria-expanded="false">
            Ver Solicitudes <i class="bi bi-chevron-down ms-1"></i>
        </button>
    </div>
    
    <div class="collapse show mb-4" id="pendingRequestsList">
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="list-group list-group-flush">
                @foreach($pendingRequests as $req)
                    <div class="list-group-item p-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                                <i class="bi bi-person-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">{{ $req->client_name }}</h6>
                                <div class="text-muted small">
                                    <i class="bi bi-scissors me-1"></i> {{ $req->service->name }} 
                                    @if($req->custom_details) <span class="badge bg-warning text-dark ms-1">{{ $req->custom_details }}</span> @endif
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-calendar me-1"></i> {{ $req->scheduled_at->format('d/m/Y') }} 
                                    <i class="bi bi-clock ms-2 me-1"></i> {{ $req->scheduled_at->format('g:i A') }}
                                    @if($req->barber) <span class="ms-2 badge bg-secondary">{{ $req->barber->name }}</span> @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                             <button onclick="rejectRequest({{ $req->id }})" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-lg"></i> Rechazar
                            </button>
                            <button onclick="confirmRequest({{ $req->id }}, '{{ $req->service->name }}', {{ $req->service->price ?? 0 }})" class="btn btn-primary btn-sm fw-bold px-3">
                                <i class="bi bi-check-lg me-1"></i> Confirmar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="row g-2 mb-4 animate-fade-in">
        @php
            $colClass = trim(auth()->user()->role) === 'admin' ? 'col-6 col-md-6 col-xl-3' : 'col-md-6';
        @endphp

        <!-- Citas Hoy -->
        <div class="{{ $colClass }}">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Citas Hoy</h6>
                            <h2 class="mb-0 fw-bold text-dark display-6">{{ $stats['total_today'] }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-calendar-check text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingresos (Admin Only) -->
        @if(trim(auth()->user()->role) === 'admin')
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Ingresos</h6>
                            <h2 class="mb-0 fw-bold text-dark display-6">${{ number_format($stats['revenue_today'], 0) }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-currency-dollar text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Pendientes -->
        <div class="{{ $colClass }}">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Pendientes</h6>
                            <h2 class="mb-0 fw-bold text-dark display-6">{{ $stats['pending_today'] }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-clock-history text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barberos Disponibles -->
        <!-- Barberos Disponibles (Admin Only) -->
        @if(trim(auth()->user()->role) === 'admin')
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-info bg-opacity-10 p-2 rounded-circle me-2">
                            <i class="bi bi-people fw-bold text-info fs-5"></i>
                        </div>
                        <h6 class="text-secondary small text-uppercase mb-0 fw-bold">Barberos Activos</h6>
                    </div>
                    
                    <h3 class="fw-bold text-dark mb-3">{{ $barbers->where('is_active', true)->count() }}</h3>

                    <!-- Avatar List -->
                    <div class="d-flex gap-1 overflow-visible" style="min-height: 45px;">
                        @foreach($barbers as $barber)
                            @php
                                $isActive = $barber->is_active;
                                $isSpecial = $barber->special_mode;
                                $rawAvatar = $barber->user->avatar;
                                $isImage = $rawAvatar && (
                                    str_starts_with($rawAvatar, 'users/') || 
                                    str_ends_with(strtolower($rawAvatar), '.jpg') || 
                                    str_ends_with(strtolower($rawAvatar), '.jpeg') || 
                                    str_ends_with(strtolower($rawAvatar), '.png') || 
                                    str_ends_with(strtolower($rawAvatar), '.webp')
                                );
                                $initials = substr($barber->name, 0, 1);
                            @endphp

                            <div class="position-relative" data-bs-toggle="tooltip" title="{{ $barber->name }} {{ $isActive ? '(Activo)' : ($isSpecial ? '(Horario Extra)' : '(Inactivo)') }}">
                                @if($rawAvatar && $isImage)
                                    <img src="{{ asset('storage/' . $rawAvatar) }}" alt="{{ $barber->name }}" class="rounded-circle border border-2 {{ $isActive ? 'border-success' : 'border-secondary' }}" style="width: 45px; height: 45px; object-fit: cover;">
                                @elseif($rawAvatar)
                                    <!-- Emoji Avatar (User Style) -->
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary fw-bold border border-primary-subtle transition-all hover-scale" style="width: 45px; height: 45px; font-size: 1.5rem;">
                                        {{ $rawAvatar }}
                                    </div>
                                @else
                                    <!-- Initials Fallback -->
                                    <div class="rounded-circle d-flex align-items-center justify-content-center border border-2 {{ $isActive ? 'border-success bg-success text-white' : 'border-secondary bg-secondary text-white' }}" style="width: 45px; height: 45px; font-weight: bold;">
                                        {{ $initials }}
                                    </div>
                                @endif

                                <!-- Status Dot -->
                                @if($isActive)
                                    <span class="position-absolute bottom-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle"></span>
                                @elseif($isSpecial)
                                    <span class="position-absolute bottom-0 start-100 translate-middle p-1 bg-warning border border-light rounded-circle"></span>
                                @else
                                    <span class="position-absolute bottom-0 start-100 translate-middle p-1 bg-secondary border border-light rounded-circle"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Calendar Container -->
    <div class="card border-0 shadow-sm flex-grow-1 overflow-hidden" style="min-height: 600px;">
        <div class="card-body p-0 p-md-3 h-100 position-relative">
            <!-- Custom View Selector -->
            <div id="custom-view-selector" class="d-none">
                <div class="d-flex gap-2 flex-wrap justify-content-end">
                     <!-- Barber Filter (New) -->
                     <!-- Barber Filter (New) -->
                    @if(trim(auth()->user()->role) === 'admin')
                    <select id="barberFilter" class="form-select form-select-sm shadow-sm border-0 bg-white" style="width: 180px; color: #3C4043; font-weight: 500;" onchange="refreshCalendar()">
                        <option value="">Todos los Barberos</option>
                        @foreach(\App\Models\Barber::where('is_active', true)->get() as $barber)
                            <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                        @endforeach
                    </select>
                    @else
                        <input type="hidden" id="barberFilter" value="{{ auth()->user()->barber?->id }}">
                        <span class="text-primary fw-bold align-self-center me-3">Mi Agenda</span>
                    @endif

                    </select>

                    <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-2 shadow-sm" onclick="openBookingModal()">
                        <i class="bi bi-plus-lg"></i>
                        <span class="d-none d-sm-inline">Apartar Cita</span>
                    </button>
                    <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle btn-sm fw-bold border-0 bg-transparent text-dark d-flex align-items-center gap-2" type="button" id="calendarViewBtn" data-bs-toggle="dropdown" aria-expanded="false" style="color: #3C4043 !important;">
                        <span>Mes</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-2" aria-labelledby="calendarViewBtn" style="min-width: 240px;">
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="timeGridDay"><span>Día</span><span class="text-muted small">D</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="timeGridWeek"><span>Semana</span><span class="text-muted small">W</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="dayGridMonth"><span>Mes</span><span class="text-muted small">M</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="listYear"><span>Año</span><span class="text-muted small">Y</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="listWeek"><span>Agenda</span><span class="text-muted small">A</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="fourDay"><span>4 días</span><span class="text-muted small">X</span></a></li>
                        <li><hr class="dropdown-divider my-2"></li>
                        <li>
                            <div class="dropdown-item d-flex gap-2 align-items-center rounded-2 py-2" onclick="toggleOption(event, 'weekends')">
                                <i class="bi bi-check-lg text-primary" id="check-weekends"></i>
                                <span>Mostrar Fines de Semana</span>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-item d-flex gap-2 align-items-center rounded-2 py-2" onclick="toggleOption(event, 'rejected')">
                                <i class="bi bi-check-lg text-primary" id="check-rejected"></i>
                                <span>Mostrar Citas Canceladas </span>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-item d-flex gap-2 align-items-center rounded-2 py-2" onclick="toggleOption(event, 'completed')">
                                <i class="bi bi-check-lg text-primary" id="check-completed"></i>
                                <span>Mostrar Citas Completadas</span>
                            </div>
                        </li>
                    </ul>
                </div>
                </div>
            </div>
            
            <div id="calendar" class="h-100"></div>
        </div>
    </div>
    <!-- Rendered: {{ now() }} -->
</div>

<!-- Admin Booking Modal -->
<div class="modal fade" id="adminBookingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-calendar-plus me-2"></i>Apartar Cita
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4">
                <form id="adminBookingForm" onsubmit="submitAdminBooking(event)">
                    @csrf
                    <!-- Service & Barber -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">SERVICIO</label>
                            <select name="service_id" id="modal_service_id" class="form-select border-2" required>
                                <option value="" selected disabled>Seleccionar...</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">BARBERO</label>
                            <select name="barber_id" id="modal_barber_id" class="form-select border-2" required onchange="onModalBarberChange()">
                                <option value="" selected disabled>Seleccionar...</option>
                                @foreach($barbers as $barber)
                                    <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">FECHA</label>
                            <input type="date" name="date" id="modal_date" class="form-control border-2" required min="{{ date('Y-m-d') }}" onchange="onModalDateChange()">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">HORA</label>
                            <select name="time" id="modal_time" class="form-select border-2" required disabled>
                                <option value="" selected disabled>Elige fecha...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Client Info -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">CLIENTE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="client_name" class="form-control border-start-0 ps-0" placeholder="Nombre completo" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">TELÉFONO</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-whatsapp"></i></span>
                            <select name="phone_prefix" class="form-select border-start-0 border-end-0 bg-light" style="max-width: 100px;" required>
                                <option value="+57" selected>+57</option>
                                <option value="+58">VE (+58)</option>
                                <option value="+51">PE (+51)</option>
                                <option value="+593">EC (+593)</option>
                                <option value="+1">US (+1)</option>
                            </select>
                            <input type="tel" name="phone_number" class="form-control border-start-0 ps-2" placeholder="3001234567" required minlength="7" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>

                    <!-- Custom Details -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">DETALLES (OPCIONAL)</label>
                        <input type="text" name="custom_details" class="form-control" placeholder="Ej: Corte específico, notas...">
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="btnBookAdmin" class="btn btn-primary btn-lg fw-bold rounded-3">
                            Confirmar Cita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ... Existing scripts ...

    // Request Handling
    function confirmRequest(id, serviceName, basePrice) {
        Swal.fire({
            title: '¿Confirmar Cita?',
            html: `
                <p class="text-muted mb-3">Servicio: <b>${serviceName}</b></p>
                <div class="form-text mt-2">Se enviará el mensaje de WhatsApp al cliente indicando que la cita ha sido agendada.</div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sí, Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10B981'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit Status Change (Price will remain as is or default)
                axios.patch(`/appointments/${id}/confirm`)
                .then(response => {
                    Swal.fire('¡Confirmada!', 'La cita ha sido agendada y notificada.', 'success').then(() => {
                        location.reload();
                    });
                }).catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'No se pudo confirmar. Revisa la consola o conexión.', 'error');
                });
            }
        });
    }

    function rejectRequest(id) {
        Swal.fire({
            title: '¿Rechazar Solicitud?',
            text: "Se marcará como cancelada.",
            input: 'text',
            inputPlaceholder: 'Motivo (opcional)...',
            showCancelButton: true,
            confirmButtonText: 'Sí, rechazar',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                 axios.patch(`/appointments/${id}/cancel`, {
                    reason: result.value || 'No disponible'
                }).then(response => {
                    Swal.fire('Descartada', 'La solicitud fue rechazada.', 'success').then(() => {
                        location.reload();
                    });
                }).catch(err => {
                    Swal.fire('Error', 'Error al rechazar.', 'error');
                });
            }
        });
    }

    // Admin Booking Logic
    const bookingModal = new bootstrap.Modal(document.getElementById('adminBookingModal'));

    function openBookingModal() {
        document.getElementById('adminBookingForm').reset();
        document.getElementById('modal_time').innerHTML = '<option value="" selected disabled>Elige fecha...</option>';
        document.getElementById('modal_time').disabled = true;
        bookingModal.show();
    }

    function onModalBarberChange() {
        const dateInput = document.getElementById('modal_date');
        if(dateInput.value) checkModalAvailability();
    }

    function onModalDateChange() {
        checkModalAvailability();
    }

    function checkModalAvailability() {
        const barberId = document.getElementById('modal_barber_id').value;
        const date = document.getElementById('modal_date').value;
        const timeSelect = document.getElementById('modal_time');

        if (!barberId || !date) return;

        timeSelect.disabled = true;
        timeSelect.innerHTML = '<option>Cargando...</option>';

        axios.get(`/api/slots?barber_id=${barberId}&date=${date}`)
            .then(response => {
                const slots = response.data;
                timeSelect.innerHTML = '<option value="" selected disabled>Seleccionar hora...</option>';
                
                if (!Array.isArray(slots) || slots.length === 0) {
                    timeSelect.innerHTML = '<option disabled>Estás lleno este día</option>';
                    return;
                }

                slots.forEach(time => {
                    // Convert "4:30 PM" format back if needed or use as string
                    // API returns "4:30 PM" (g:i A). Form expects H:i usually? 
                    // Wait, Controller store expects combined date+time. 
                    // Let's check how store works: Carbon::parse($request->date . ' ' . $request->time);
                    // "2023-10-27 4:30 PM" works fine in Carbon.
                    const option = document.createElement('option');
                    option.value = time;
                    option.text = time;
                    timeSelect.appendChild(option);
                });
                timeSelect.disabled = false;
            })
            .catch(err => {
                console.error(err);
                timeSelect.innerHTML = '<option disabled>Error al cargar</option>';
            });
    }

    function submitAdminBooking(e) {
        e.preventDefault();
        const btn = document.getElementById('btnBookAdmin');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        const formData = new FormData(e.target);

        axios.post("{{ route('book') }}", formData, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => {
                bookingModal.hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Cita Creada!',
                    text: 'La cita se ha agendado correctamente en el calendario.',
                    timer: 2000,
                    showConfirmButton: false
                });
                // Refresh Calendar
                if(window.calendarInstance) window.calendarInstance.refetchEvents();
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo crear la cita. Revisa los datos o la conexión.'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }
</script>
@endpush


@push('scripts')
<script>
    // Global State for Filters
    let calendarState = {
        weekends: true,
        showRejected: true, // Cancelled
        showCompleted: true
    };
    window.calendarInstance = null;

    function initCalendar() {
        var calendarEl = document.getElementById('calendar');
        if(!calendarEl) return;
        
        // Initialize State UI
        updateCheckboxes();

        window.calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '' // Leaving empty to inject custom dropdown
            },
            navLinks: true, 
            height: '100%',
            contentHeight: 'auto',
            aspectRatio: window.innerWidth < 768 ? 0.65 : 1.35,
            handleWindowResize: true,
            locale: 'es',
            weekends: true, // Default
            firstDay: 1, // Lunes
            
            // Time Format
            // Time Format
            slotMinTime: '00:00:00', // Full 24h
            slotMaxTime: '24:00:00',
            scrollTime: '06:00:00', // Start view at 6 AM
            expandRows: true, 
            stickyHeaderDates: true,
            allDaySlot: false,
            weekends: true,
            editable: false,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: false,
            nowIndicator: true, // Enabled Google-style check
            
            // Interaction: Month -> Day
            navLinks: true, 
            navLinkDayClick: 'timeGridDay',
            
            views: {
                dayGridMonth: { dayMaxEvents: false }, // Expand all events (No "+1 more")
                timeGrid: { dayMaxEvents: true },
                fourDay: {
                    type: 'timeGrid',
                    duration: { days: 4 },
                    buttonText: '4 días'
                },
                listYear: { buttonText: 'Año' }
            },
            
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                omitZeroMinute: false,
                meridiem: 'short',
                hour12: true
            },
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short',
                hour12: true
            },
            events: {
                url: "{{ route('calendar.events') }}",
                extraParams: function() {
                    return {
                        barber_id: document.getElementById('barberFilter') ? document.getElementById('barberFilter').value : ''
                    };
                }
            },
            
            // Logic for Hiding Events based on Filters
            eventClassNames: function(arg) {
                const props = arg.event.extendedProps;
                let classes = [];
                
                // Filter: Rejected (Cancelled)
                if (props.status === 'cancelled' && !calendarState.showRejected) {
                    classes.push('d-none');
                }
                
                // Filter: Completed
                if (props.status === 'completed' && !calendarState.showCompleted) {
                    classes.push('d-none');
                }
                
                return classes;
            },

            // Lifecycle Hooks
            datesSet: function(info) {
                // 1. Update View Dropdown text
                const viewNameMap = {
                    'timeGridDay': 'Día',
                    'timeGridWeek': 'Semana',
                    'dayGridMonth': 'Mes',
                    'listYear': 'Año',
                    'listWeek': 'Agenda',
                    'fourDay': '4 días'
                };
                const btn = document.getElementById('calendarViewBtn');
                if(btn) {
                    btn.innerHTML = `<span>${viewNameMap[info.view.type] || 'Vista'}</span>`;
                }

                // 2. Mini Calendar (Flatpickr) on Title
                const titleEl = document.querySelector('.fc-toolbar-title');
                if(titleEl && !titleEl._flatpickr) {
                    flatpickr(titleEl, {
                        locale: 'es',
                        defaultDate: calendarInstance.getDate(),
                        dateFormat: "Y-m-d", // value format
                        position: 'auto center',
                        disableMobile: "true", // Force custom dropdown on mobile too if needed
                        onChange: function(selectedDates, dateStr, instance) {
                            calendarInstance.gotoDate(selectedDates[0]);
                        },
                        onOpen: function(selectedDates, dateStr, instance) {
                            // Sync picker with current calendar date when opening
                            instance.setDate(calendarInstance.getDate());
                        }
                    });
                }
            },
            
            eventClick: function(info) {
                const event = info.event;
                const props = event.extendedProps;
                
                // Format Date
                const dateOptions = { weekday: 'long', day: 'numeric', month: 'long' };
                const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
                
                const dateStr = event.start.toLocaleDateString('es-ES', dateOptions);
                const timeStr = event.allDay ? 'Todo el día' : event.start.toLocaleTimeString('es-ES', timeOptions);
                
                // Determine Styles based on Type
                let headerContent = '';
                let bgStyle = '';
                let titleColor = '#1f1f1f';
                
                if (props.type === 'holiday') {
                    // Holiday Style (like the image)
                    bgStyle = 'background-color: #F8FAFE; background-image: url("https://www.gstatic.com/classroom/themes/img_birthday.jpg"); background-size: cover; background-position: center;';
                    // Fallback gradient if image fails or for cleaner look:
                    // bgStyle = 'background: linear-gradient(135deg, #FFD1DC 0%, #C1E1C1 100%);'; 
                } else {
                    // Appointment Style
                    const statusColors = {
                        'completed': 'linear-gradient(135deg, #34D399 0%, #059669 100%)',
                        'cancelled': 'linear-gradient(135deg, #EF4444 0%, #B91C1C 100%)',
                        'scheduled': 'linear-gradient(135deg, #6366F1 0%, #3B82F6 100%)'
                    };
                    bgStyle = `background: ${statusColors[props.status] || statusColors['scheduled']}`;
                    titleColor = '#ffffff'; // White text on dark gradients
                }

                // STANDARD SWEETALERT 2 DESIGN
                let statusBadge = '';
                if (props.status && props.type !== 'holiday') {
                    const colors = { 
                        'scheduled': 'primary', 
                        'completed': 'success', 
                        'cancelled': 'danger' 
                    };
                    const labels = { 
                        'scheduled': 'Programada', 
                        'completed': 'Completada', 
                        'cancelled': 'Cancelada' 
                    };
                    const color = colors[props.status] || 'secondary';
                    const label = labels[props.status] || props.status;
                    statusBadge = `<span class="badge bg-${color}">${label}</span>`;
                }

                // Action Buttons (Standard Bootstrap)
                // Action Buttons (Standard Bootstrap)
                let actionButtons = '';
                if (props.type === 'appointment' && props.status === 'scheduled') {
                    // Visibility Logic: "Complete" button only appears if 20 mins have passed
                    const now = new Date();
                    const diffMs = now - event.start;
                    const diffMinutes = diffMs / (1000 * 60);
                    
                    let completeBtn = '';
                    if (diffMinutes >= 20) {
                        completeBtn = `
                            <button onclick="completeAppointment(${event.id}, ${props.price})" class="btn btn-success px-4">
                                <i class="bi bi-check-circle-fill me-1"></i> Completar
                            </button>
                        `;
                    }

                    actionButtons = `
                        <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
                            ${completeBtn}
                            <button onclick="editAppointment(${event.id})" class="btn btn-primary px-4">
                                <i class="bi bi-pencil-fill me-1"></i> Editar
                            </button>
                            <button onclick="cancelAppointment(${event.id})" class="btn btn-warning px-4 text-white">
                                <i class="bi bi-slash-circle me-1"></i> Cancelar
                            </button>
                            <button onclick="deleteAppointment(${event.id})" class="btn btn-danger px-4">
                                <i class="bi bi-trash-fill me-1"></i> Eliminar
                            </button>
                        </div>
                    `;
                } else if (props.type === 'appointment') {
                     // For Cancelled/Completed/Request - Show Delete Button
                     actionButtons = `
                        <div class="d-flex justify-content-center gap-2 mt-4 flex-wrap">
                            <button onclick="deleteAppointment(${event.id})" class="btn btn-danger px-4">
                                <i class="bi bi-trash-fill me-1"></i> Eliminar Definitivamente
                            </button>
                        </div>
                    `;
                }


                Swal.fire({
                    title: event.title,
                    html: `
                        <div class="text-start fs-6">
                            <p class="mb-2"><strong><i class="bi bi-calendar-event me-2"></i>Fecha:</strong> ${dateStr}</p>
                            <p class="mb-2"><strong><i class="bi bi-clock me-2"></i>Hora:</strong> ${timeStr}</p>
                            ${props.barber ? `<p class="mb-2"><strong><i class="bi bi-person me-2"></i>Barbero:</strong> ${props.barber}</p>` : ''}
                            ${props.service ? `<p class="mb-2"><strong><i class="bi bi-scissors me-2"></i>Servicio:</strong> ${props.service} ($${props.price})</p>` : ''}
                            ${props.client_phone ? `<p class="mb-2"><strong><i class="bi bi-whatsapp me-2"></i>Teléfono:</strong> ${props.client_phone}</p>` : ''}
                            <p class="mb-2"><strong><i class="bi bi-info-circle me-2"></i>Estado:</strong> ${statusBadge}</p>
                            ${props.custom_details ? `<p class="mb-0 text-muted small mt-3"><em>${props.custom_details}</em></p>` : ''}

                            ${props.status === 'cancelled' ? 
                                `<div class="mt-3 p-2 bg-danger bg-opacity-10 rounded border border-danger">
                                    <strong class="text-danger d-block">Motivo de Cancelación:</strong> 
                                    <span class="text-dark small">${props.cancellation_reason || 'No especificado'}</span>
                                </div>` : ''
                            }

                            ${props.status === 'completed' ? 
                                `<div class="mt-3 p-2 bg-success bg-opacity-10 rounded border border-success">
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Completada con éxito</strong>
                                </div>` : ''
                            }
                        </div>
                        ${actionButtons}
                    `,
                    icon: 'info',
                    showConfirmButton: false,
                    showCloseButton: true,
                    showCancelButton: false,
                    customClass: {
                        popup: 'rounded-4 shadow' // Simple rounded corners
                    }
                });
            }
        });
        
        calendarInstance.render();

        // Inject Custom Dropdown into FullCalendar Toolbar
        const toolbarRight = document.querySelector('.fc-toolbar-chunk:last-child');
        const selector = document.getElementById('custom-view-selector');
        
        if (toolbarRight && selector) {
            selector.classList.remove('d-none');
            toolbarRight.appendChild(selector);
            
            // Re-bind dropdown clicks to calendar
            selector.querySelectorAll('[data-view]').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Find closest link if click was on span
                    const link = e.target.closest('a');
                    const view = link.getAttribute('data-view');
                    if(view) calendarInstance.changeView(view);
                });
            });
        }
    }

    // Inject Server Data for JS
    const serverData = {
        services: @json($services),
        barbers: @json($barbers)
    };

    // Filter Toggle Logic
    window.toggleFilter = function(filterType) {
        if (filterType === 'rejected') {
             calendarState.showRejected = !calendarState.showRejected;
             document.getElementById('toggleRejected').classList.toggle('active', calendarState.showRejected);
        } else if (filterType === 'completed') {
             calendarState.showCompleted = !calendarState.showCompleted;
             document.getElementById('toggleCompleted').classList.toggle('active', calendarState.showCompleted);
        }
        calendarInstance.refetchEvents(); // Re-apply eventClassNames logic
    };

    // Toggle Logic
    function toggleOption(e, type) {
        e.preventDefault();
        e.stopPropagation(); // Keep dropdown open

        if(type === 'weekends') {
            calendarState.weekends = !calendarState.weekends;
            calendarInstance.setOption('weekends', calendarState.weekends);
        } else if (type === 'rejected') {
            calendarState.showRejected = !calendarState.showRejected;
            calendarInstance.render(); // Re-trigger eventClassNames
        } else if (type === 'completed') {
            calendarState.showCompleted = !calendarState.showCompleted;
            calendarInstance.render(); // Re-trigger eventClassNames
        }

        updateCheckboxes();
    }

    // Refresh Calendar (Global)
    window.refreshCalendar = function() {
        if(calendarInstance) {
            calendarInstance.refetchEvents();
        }
    };

    // Edit Appointment Logic (Enhanced)
    window.editAppointment = function(id) {
        Swal.close();

        const event = calendarInstance.getEventById(id);
        if (!event) return;

        const props = event.extendedProps;
        const currentServiceId = props.service_id;
        const currentBarberId = props.barber_id;
        const originalDate = event.start.toISOString().split('T')[0];
        // Format time to 12h format "04:30 PM" to match API/Blade Logic
        let hours = event.start.getHours();
        let minutes = event.start.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; 
        minutes = minutes < 10 ? '0'+minutes : minutes;
        const originalTime = `${hours}:${minutes} ${ampm}`;

        // Build Options
        const serviceOptions = serverData.services.map(s => 
            `<option value="${s.id}" data-custom="${s.is_custom || ['otro', 'otro servicio'].includes(s.name.toLowerCase())}" ${s.id == currentServiceId ? 'selected' : ''}>${s.name} ($${s.price})</option>`
        ).join('');

        const barberOptions = serverData.barbers.map(b => 
            `<option value="${b.id}" ${b.id == currentBarberId ? 'selected' : ''}>${b.name}</option>`
        ).join('');

        // Min Date (Today - Local Time)
        const todayObj = new Date();
        const year = todayObj.getFullYear();
        const month = String(todayObj.getMonth() + 1).padStart(2, '0');
        const day = String(todayObj.getDate()).padStart(2, '0');
        const minDate = `${year}-${month}-${day}`;

        Swal.fire({
            title: 'Editar Cita',
            html: `
                <div class="text-start">
                    <!-- Service -->
                    <label class="form-label fw-bold small text-muted">SERVICIO</label>
                    <select id="edit-service" class="form-select mb-3">
                        ${serviceOptions}
                    </select>

                    <!-- Custom Details (Hidden) -->
                    <div id="edit-custom-details-container" class="mb-3 d-none">
                        <label class="form-label fw-bold small text-muted">DETALLE (¿Qué te harás?)</label>
                        <input type="text" id="edit-custom-details" class="form-control" placeholder="Ej: Rayitos, Tintura..." value="${props.custom_details || ''}">
                    </div>
                    
                    <!-- Date & Barber Row -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                             <label class="form-label fw-bold small text-muted">BARBERO</label>
                             <select id="edit-barber" class="form-select">
                                <option value="" disabled>Selecciona...</option>
                                ${barberOptions}
                             </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-muted">FECHA</label>
                            <input type="date" id="edit-date" class="form-control" min="${minDate}" value="${originalDate}" 
                                   disabled title="Selecciona un barbero primero" style="cursor: not-allowed;">
                        </div>
                    </div>

                    <!-- Slots -->
                    <label class="form-label fw-bold small text-muted">HORARIO</label>
                    <div id="edit-slots-container" class="d-flex flex-wrap gap-2 p-3 border rounded bg-light" style="min-height: 60px; max-height: 150px; overflow-y: auto;">
                        <span class="text-muted small">Selecciona barbero y fecha...</span>
                    </div>
                    <input type="hidden" id="edit-time" value="${originalTime}">
                    <div id="edit-time-display" class="mt-2 text-primary fw-bold small text-end">Seleccionado: ${originalTime}</div>
                </div>
            `,
            width: '500px',
            showCancelButton: true,
            confirmButtonText: 'Guardar Cambios',
            cancelButtonText: 'Cancelar',
            checkValidity: false,
            didOpen: () => {
                const serviceSelect = document.getElementById('edit-service');
                const customContainer = document.getElementById('edit-custom-details-container');
                const barberSelect = document.getElementById('edit-barber');
                const dateInput = document.getElementById('edit-date');
                const slotsContainer = document.getElementById('edit-slots-container');
                const timeInput = document.getElementById('edit-time');
                const timeDisplay = document.getElementById('edit-time-display');

                // 1. Service Logic
                function toggleCustom() {
                    const option = serviceSelect.options[serviceSelect.selectedIndex];
                    const isCustom = option.getAttribute('data-custom') === 'true';
                    if(isCustom) customContainer.classList.remove('d-none');
                    else customContainer.classList.add('d-none');
                }
                serviceSelect.addEventListener('change', toggleCustom);
                toggleCustom(); 

                // 2. Enable Date Logic
                function toggleDate() {
                    if(barberSelect.value) {
                        dateInput.disabled = false;
                        dateInput.style.cursor = 'text';
                        dateInput.title = '';
                    } else {
                        dateInput.disabled = true;
                        dateInput.style.cursor = 'not-allowed';
                        dateInput.title = 'Selecciona un barbero primero';
                    }
                }
                barberSelect.addEventListener('change', toggleDate);
                // Trigger initially (it might be pre-selected)
                toggleDate();

                // 2.5 Strict Date Validation
                const todayStr = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD in local time
                
                // If the original date is in the past, clear it to force user to pick new
                if(dateInput.value < todayStr) {
                    dateInput.value = '';
                }

                dateInput.addEventListener('input', function() {
                   if(this.value && this.value < todayStr) {
                       this.value = '';
                       Swal.showValidationMessage('No puedes seleccionar fechas pasadas.');
                   } else {
                       Swal.resetValidationMessage(); 
                   }
                });

                // 3. Fetch Slots Logic
                function fetchSlots() {
                    const barberId = barberSelect.value;
                    const date = dateInput.value;
                    
                    if(!barberId) {
                        slotsContainer.innerHTML = '<small>Selecciona un barbero primero.</small>';
                        return;
                    }
                    if(!date) {
                         slotsContainer.innerHTML = '<small>Selecciona una fecha.</small>';
                         return;
                    }

                    slotsContainer.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';

                    axios.get(`/api/slots?barber_id=${barberId}&date=${date}`)
                        .then(res => {
                            let validSlots = res.data; 
                            slotsContainer.innerHTML = '';

                            const isSameContext = (barberId == currentBarberId && date == originalDate);
                            
                            // Inject original time if same context and not present
                            if(isSameContext && !validSlots.includes(originalTime)) {
                                validSlots.push(originalTime);
                                validSlots.sort((a,b) => {
                                    const dateA = new Date('1970/01/01 ' + a);
                                    const dateB = new Date('1970/01/01 ' + b);
                                    return dateA - dateB; 
                                });
                            }

                            if(!validSlots.length) {
                                slotsContainer.innerHTML = '<span class="text-danger small">No hay horarios disponibles.</span>';
                                return;
                            }

                            validSlots.forEach(t => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.textContent = t;
                                const isSelected = (timeInput.value === t);
                                btn.className = `btn btn-sm ${isSelected ? 'btn-primary' : 'btn-outline-secondary'}`;
                                btn.onclick = () => {
                                    timeInput.value = t;
                                    timeDisplay.textContent = 'Seleccionado: ' + t;
                                    Array.from(slotsContainer.children).forEach(c => c.className = 'btn btn-sm btn-outline-secondary');
                                    btn.className = 'btn btn-sm btn-primary';
                                };
                                slotsContainer.appendChild(btn);
                            });
                        })
                        .catch(err => {
                            slotsContainer.innerHTML = '<small class="text-danger">Error al cargar horarios</small>';
                        });
                }
                
                barberSelect.addEventListener('change', () => { 
                    timeInput.value=''; timeDisplay.textContent=''; 
                    fetchSlots(); 
                });
                dateInput.addEventListener('change', () => { 
                    timeInput.value=''; timeDisplay.textContent=''; 
                    fetchSlots(); 
                });

                // Check init - only load if date is valid
                if(barberSelect.value && dateInput.value) {
                    fetchSlots();
                } else {
                    slotsContainer.innerHTML = '<small>Selecciona una fecha válida.</small>';
                }
            },
            preConfirm: () => {
                const date = document.getElementById('edit-date').value;
                const time = document.getElementById('edit-time').value;
                const service_id = document.getElementById('edit-service').value;
                const barber_id = document.getElementById('edit-barber').value;
                const custom_details = document.getElementById('edit-custom-details').value;

                // Validation
                // 1. Check Date (No past dates)
                const selectedDate = new Date(date + 'T00:00:00'); // Force local midnight
                const today = new Date();
                today.setHours(0,0,0,0);
                
                if (selectedDate < today) {
                    Swal.showValidationMessage('No puedes seleccionar una fecha pasada.');
                    return false;
                }

                if(!time) {
                    Swal.showValidationMessage('Debes seleccionar un horario válido.');
                    return false;
                }
                // Check Custom
                const serviceSelect = document.getElementById('edit-service');
                const isCustom = serviceSelect.options[serviceSelect.selectedIndex].getAttribute('data-custom') === 'true';
                if(isCustom && !custom_details.trim()) {
                     Swal.showValidationMessage('Debes especificar el detalle del servicio.');
                     return false;
                }

                return {
                    date, time, service_id, barber_id, custom_details
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({title: 'Actualizando...', didOpen: () => Swal.showLoading()});
                
                axios.put(`/appointments/${id}`, result.value)
                    .then(response => {
                        Swal.fire('Actualizado', 'La cita se ha modificado correctamente', 'success');
                        calendarInstance.refetchEvents();
                    })
                    .catch(error => {
                        Swal.fire('Error', 'No se pudo actualizar la cita', 'error');
                    });
            }
        });
    };
    function updateCheckboxes() {
        const checkMap = {
            'weekends': calendarState.weekends,
            'rejected': calendarState.showRejected,
            'completed': calendarState.showCompleted
        };

        for (const [key, value] of Object.entries(checkMap)) {
            const icon = document.getElementById(`check-${key}`);
            if(icon) {
                if(value) {
                    icon.classList.remove('opacity-0');
                } else {
                    icon.classList.add('opacity-0');
                }
            }
        }
    }


    document.addEventListener('DOMContentLoaded', initCalendar);


    // Actions
    window.completeAppointment = function(id, basePrice) {
        Swal.fire({
            title: 'Confirmar Completado',
            text: '¿Deseas finalizar esta cita?',
            input: 'number',
            inputValue: basePrice,
            inputLabel: 'Precio Final Cobrado',
            showCancelButton: true,
            confirmButtonText: 'Sí, Completar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10B981',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes ingresar el precio cobrado';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                axios.patch(`/appointments/${id}/complete`, {
                    confirmed_price: result.value
                })
                .then(response => {
                    Swal.fire('¡Listo!', 'La cita ha sido marcada como completada.', 'success');
                    // Reload
                    setTimeout(() => location.reload(), 1000); 
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo actualizar la cita.', 'error');
                });
            }
        });
    };

    window.cancelAppointment = function(id) {
        Swal.fire({
            title: 'Cancelar Cita',
            input: 'text',
            inputLabel: 'Motivo de cancelación',
            showCancelButton: true,
            confirmButtonText: 'Sí, Cancelar',
            confirmButtonColor: '#EF4444',
            cancelButtonText: 'Volver'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.patch(`/appointments/${id}/cancel`, {
                    reason: result.value
                })
                .then(response => {
                    Swal.fire('Cancelada', 'La cita ha sido cancelada.', 'success');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    Swal.fire('Error', 'No se pudo cancelar la cita.', 'error');
                });
            }
        });
    };    
</script>
<style>
    /* Custom Calendar Styling for Google Calendar Look */
    .fc {
        font-family: 'Outfit', sans-serif;
    }
    .fc-col-header-cell {
        background-color: transparent !important;
        padding: 8px 0;
        border: none !important;
        border-bottom: 1px solid #E2E8F0 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
    }
    
    /* Toolbar & Title (Mini Calendar Trigger) */
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 400 !important;
        color: #1E293B;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 12px;
        border-radius: 6px;
        transition: background-color 0.2s;
        position: relative; /* For Flatpickr positioning */
    }
    .fc-toolbar-title:hover {
        background-color: #F1F5F9;
    }
    .fc-toolbar-title::after {
        content: '\F229'; /* Bootstrap Icons: chevron-down */
        font-family: 'bootstrap-icons';
        font-size: 1rem;
        color: #64748B;
    }
    .fc-header-toolbar {
        margin-bottom: 1.5rem !important;
    }

    /* Google Style Events */
    /* Common interactions */
    .fc-event {
        cursor: pointer;
        /* Removed global transform to prevent breaking List View table rows */
    }

    /* TYPE 1: TimeGrid (Week/Day - Pills) */
    .fc-timegrid-event {
        border: none !important;
        border-radius: 6px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        padding: 1px 4px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: transform 0.1s;
    }
    .fc-timegrid-event:hover {
        transform: scale(1.02);
        z-index: 50;
    }
    .fc-timegrid-event .fc-event-main {
        color: white; 
    }

    /* TYPE 2: DayGrid (Month - Dots) */
    .fc-daygrid-event {
        background: transparent !important;
        border: none !important;
        margin-top: 1px !important;
        padding: 2px 4px !important;
        transition: transform 0.1s;
    }
    .fc-daygrid-event:hover {
        background: rgba(0,0,0,0.05) !important;
        border-radius: 4px;
        transform: scale(1.02);
        z-index: 50;
    }
    .fc-daygrid-dot-event .fc-event-title,
    .fc-daygrid-dot-event .fc-event-time {
        color: #3C4043 !important; /* Dark text */
        font-weight: 500;
        font-size: 0.85rem;
    }
    .fc-daygrid-event-dot {
        border-width: 6px !important; /* Larger dot */
        margin-right: 6px;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.5); /* Definition ring */
        border-color: inherit; /* Ensure it takes the event color */
    }

    /* Custom Native Close Button Styling */
    .google-native-close {
        color: white !important;
        position: absolute !important;
        top: 15px !important;
        right: 15px !important;
        box-shadow: none !important;
        background: transparent !important;
        font-size: 2rem !important;
        font-weight: 300 !important;
        z-index: 99999 !important;
        outline: none !important;
    }
    .google-native-close:hover {
        color: rgba(255,255,255,0.8) !important;
        background: transparent !important;
    }



    /* TYPE 3: List View (Agenda) - Fix Blank Issue */
    .fc-list-event {
        cursor: pointer;
    }
    .fc-list-event:hover td {
        background-color: #F8F9FA !important;
    }
    .fc-list-event-title {
        color: #1E293B !important; /* Slate 800 */
        font-weight: 500;
    }
    .fc-list-event-time {
        color: #64748B !important; /* Slate 500 */
        font-weight: 600;
    }
    .fc-list-day-cushion {
        background-color: #F1F5F9 !important; /* Slate 100 */
    }
    .fc-list-day-text,
    .fc-list-day-side-text {
        color: #1E293B !important;
        font-weight: 600;
        text-transform: capitalize;
    }
    /* Ensure dot is visible */
    .fc-list-event-dot {
        border-color: #2563EB !important; /* Primary blue fallback */
    }
    /* Force table text color generally */
    .fc-list-table {
        color: #1E293B !important;
    }

    /* Today Circle (Month View) */
    .fc-daygrid-day.fc-day-today .fc-daygrid-day-top {
        display: flex;
        justify-content: center;
        padding-top: 4px;
    }
    .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background-color: #1a73e8;
        color: white !important;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        min-width: 28px; /* Prevent squash */
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        font-size: 0.9rem;
    }

    /* GLOBAL FIX: "See More" Popover */
    .fc-popover {
        /* ... existing styles ... */
        z-index: 10000 !important;
        /* ... */
    }

    /* Force SweetAlert above everything */
    .swal2-container {
        z-index: 20000 !important;
    }
    .fc-popover-header {
        background-color: #f8f9fa !important;
        padding: 12px 16px !important;
        border-bottom: 1px solid #eee !important;
        border-radius: 16px 16px 0 0 !important;
    }
    .fc-popover-title {
        font-size: 1.1rem !important;
        font-weight: 600 !important;
    }

    .fc-popover-close {
        opacity: 1 !important;
        font-size: 1.2rem !important;
        color: #666 !important;
        background: #e9ecef !important;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        cursor: pointer !important;
    }
    .fc-popover-body {
        max-height: 60vh !important;
        overflow-y: auto !important;
    }
    /* Backdrop for popover */
    .fc-popover-header::before { 
        /* Hacky backdrop attached to header to avoid breaking FC positioning logic with a global fixed div */
        content: '';
        position: fixed;
        top: -500vh;
        left: -500vw;
        width: 1000vw;
        height: 1000vh;
        background: rgba(0,0,0,0.5);
        z-index: -1;
        cursor: pointer;
    }

    /* Prevent Event Overflow (Horizontal) */
    .fc-daygrid-event {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 100% !important;
    }

    /* Mobile Responsive Toolbar */
    @media (max-width: 768px) {
        .fc-header-toolbar {
            flex-direction: column !important; /* Stack items vertically */
            gap: 12px;
            align-items: stretch !important; /* Full width items */
        }
        
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center; /* Center buttons within chunk */
            flex-wrap: wrap;
            gap: 8px;
        }
        
        /* Center Title */
        .fc-toolbar-chunk:nth-child(2) {
            order: -1; /* Place title at top if desired, or keep as is. Default center is fine here */
        }
        
        .fc-toolbar-title {
            font-size: 1.25rem !important;
            justify-content: center;
            width: 100%;
        }

        /* Adjust button sizes */
        .fc-button {
            padding: 0.25rem 0.75rem !important;
            font-size: 0.85rem !important;
        }
        
        /* View Selector Dropdown - Mobile Friendly */
        #custom-view-selector {
            width: 100%;
        }
        #custom-view-selector .btn {
            width: 100%;
            justify-content: center;
        }

        /* Ensure Day Cells are Tall on Mobile */
        .fc-daygrid-day-frame {
            min-height: 100px !important;
        }
    }
    
    /* Buttons (Google Style) */
    .fc-button {
        background-color: #fff !important;
        border: 1px solid #E2E8F0 !important;
        color: #334155 !important;
        text-transform: capitalize;
        font-weight: 500 !important;
        box-shadow: none !important;
        padding: 6px 16px !important;
        border-radius: 6px !important;
        transition: all 0.2s;
    }
    .fc-button-group > .fc-button {
        border-radius: 0 !important;
        margin: 0 !important;
    }
    .fc-button-group > .fc-button:first-child {
        border-top-left-radius: 6px !important;
        border-bottom-left-radius: 6px !important;
    }
    .fc-button-group > .fc-button:last-child {
        border-top-right-radius: 6px !important;
        border-bottom-right-radius: 6px !important;
    }
    .fc-button:hover {
        background-color: #F8FAFC !important;
        color: #0F172A !important;
    }
    .fc-button-active {
        background-color: #EFF6FF !important;
        border-color: #BFDBFE !important;
        color: #2563EB !important;
        font-weight: 600 !important;
    }
    .fc-today-button {
        margin-right: 12px !important;
        border-radius: 6px !important;
    }

    /* Grid & Cells */
    .fc-scrollgrid {
        border: none !important;
    }
    .fc-daygrid-day-top {
        flex-direction: row;
        padding: 8px;
    }
    .fc-daygrid-day-number {
        font-size: 0.9rem;
        color: #334155;
        font-weight: 500;
        width: 28px; 
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .fc-day-today .fc-daygrid-day-number {
        background-color: #2563EB;
        color: #fff;
    }
    .fc-day-today {
        background-color: transparent !important; /* Remove yellow tint */
    }
    
    /* Google Card Modal Styles */
    .google-modal-popup {
        border-radius: 28px !important;
        /* Default Desktop */
    }

    .modal-header-graphic {
        /* Placeholder pattern if no image */
        background-color: #f1f5f9;
    }

    /* Mobile Responsive Popup (Bottom Sheet) */
    @media (max-width: 768px) {
        .swal2-container {
            align-items: flex-end !important; /* Align to bottom */
            padding-bottom: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .google-modal-popup {
            width: 100% !important;
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border-top-left-radius: 24px !important;
            border-top-right-radius: 24px !important;
            margin: 0 !important;
            animation: slideUp 0.3s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
    }
    /* =========================================
       GOOGLE CALENDAR STYLE "NOW INDICATOR" 
       ========================================= */
       
    /* 1. Línea roja del día actual estilo Google */
    .fc .fc-timegrid-now-indicator-line {
        border-top: 3px solid #ea4335 !important; /* Rojo Google (Grosor Lollipop) */
        opacity: 1;
        z-index: 100;
    }

    /* 2. Bolita roja al inicio de la línea (REMOVED by User Request) */
    .fc .fc-timegrid-now-indicator-arrow {
        display: none !important;
    }

    /* 3. Círculo rojo en el día actual en vista MENSUAL */
    .fc-daygrid-day.fc-day-today {
        position: relative;
    }
    
    .fc-daygrid-day.fc-day-today .fc-daygrid-day-top {
        position: relative;
        z-index: 2; /* Ensure date number is above circle if needed? No, usually separate */
    }

    /* 3. Círculo rojo en el día actual en vista MENSUAL (REMOVED by User Request) */
    .fc-daygrid-day.fc-day-today::after {
        display: none !important;
    }    
</style>
@endpush

@push('scripts')
<script>
    function deleteAppointment(id) {
        Swal.fire({
            title: '¿Eliminar Definitivamente?',
            text: "Esta acción borrará la cita de la base de datos y NO se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, borrar para siempre',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/appointments/${id}`)
                .then(response => {
                    Swal.fire(
                        '¡Eliminado!',
                        'La cita ha sido borrada.',
                        'success'
                    ).then(() => {
                        // Refresh Calendar
                        if(window.calendarInstance) window.calendarInstance.refetchEvents();
                    });
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire(
                        'Error',
                        'No se pudo eliminar la cita.',
                        'error'
                    );
                });
            }
        })
    }
</script>
@endpush
