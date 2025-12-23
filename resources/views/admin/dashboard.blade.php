@extends('layouts.admin')

@section('title', 'Dashboard - Barbería JR')
@section('header', 'Agenda')

@section('content')
<style>
    /* dots minimalistas for FullCalendar */
    .fc-daygrid-event {
        border: none !important;
        background: transparent !important;
        display: flex !important;
        justify-content: center !important;
        padding-top: 2px !important;
    }
    .fc-daygrid-event:hover { background: transparent !important; }
    
    .fc-daygrid-event-dot {
        background-color: var(--fc-event-bg-color, #3788d8) !important;
        border: none !important;
        border-radius: 50% !important;
        width: 6px !important;
        height: 6px !important;
        margin: 1px !important;
    }

    .fc-daygrid-day-events {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        padding: 2px !important;
        max-height: 40px !important;
        overflow: hidden !important;
    }

    .fc-event-title, .fc-event-time { display: none !important; }

    /* Calendar Grid Minimalist */
    .fc-theme-bootstrap5 .fc-scrollgrid { border: none !important; }
    .fc-col-header-cell { background: transparent !important; border: none !important; padding: 10px 0 !important; font-size: 0.75rem; color: #adb5bd; text-transform: uppercase; }
    .fc-daygrid-day { border: 1px solid #f8f9fa !important; }
    .fc-header-toolbar { display: none !important; } /* Hide default toolbar */
    .fc-daygrid-day-number { font-weight: 500; font-size: 0.9rem; color: #495057; padding: 8px !important; }
    .fc-day-today { background: rgba(37, 99, 235, 0.03) !important; }
    .fc-day-today .fc-daygrid-day-number { color: #2563eb; font-weight: 700; }
    
    /* Agenda Cards - ULTRA Compact & Vibrant */
    .agenda-card {
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 10px;
        transition: all 0.2s ease;
        border-left: 6px solid #dee2e6;
        height: 100%;
        padding: 8px 10px !important; /* Extremely narrow vertically and horizontally */
    }
    .agenda-card:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); }
    
    .agenda-card.status-completed { border-left-color: #10b981 !important; background: #ecfdf5 !important; }
    .agenda-card.status-pending { border-left-color: #f59e0b !important; background: #fff7ed !important; }
    .agenda-card.status-cancelled { border-left-color: #ef4444 !important; background: #fef2f2 !important; }
    .agenda-card.status-available { 
        border-style: dashed !important; 
        border-left-color: #3b82f6 !important; 
        background: #f0f7ff !important; 
    }

    /* Fix: Remove underlines from calendar numbers */
    .fc-daygrid-day-number { text-decoration: none !important; }
    .fc-daygrid-day-number:hover { color: #2563eb !important; }

    .barber-avatar-sm {
        width: 40px; height: 40px; border-radius: 12px; object-fit: cover;
        background: #f8f9fa; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Scrollbar */
    #daily-agenda-container::-webkit-scrollbar { width: 4px; }
    #daily-agenda-container::-webkit-scrollbar-thumb { background: #e9ecef; border-radius: 10px; }

    /* Mobile Enhancements */
    @media (max-width: 768px) {
        .fc-toolbar { flex-direction: column !important; gap: 10px !important; }
        .fc-toolbar-title { font-size: 1.1rem !important; }
        .fc-daygrid-day-number { font-size: 0.8rem; padding: 4px !important; }
        .agenda-card { padding: 12px !important; }
        .agenda-card h6 { font-size: 0.9rem; }
        .agenda-card p { font-size: 0.75rem; }
        .barber-avatar-sm { width: 32px; height: 32px; }
        #calendar { min-height: 400px !important; }
        .card-body { padding: 15px !important; }
    }
</style>
<div class="d-flex flex-column h-100">
    <!-- Stats Row Toggle -->
    <div class="position-relative mb-3">
        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2">
            <h5 class="fw-bold text-dark mb-0">Resumen del Día</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary small">{{ now()->format('d M') }}</span>
        </div>
        <div class="position-absolute top-50 end-0 translate-middle-y">
            <button class="btn btn-sm btn-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;" data-bs-toggle="collapse" data-bs-target="#statsCollapse" aria-expanded="true" aria-controls="statsCollapse" title="Mostrar/Ocultar Resumen">
                <i class="bi bi-chevron-up transition-transform" id="statsCollapseIcon"></i>
            </button>
        </div>
    </div>

    <!-- Collapsible Stats Row -->
    <div class="collapse show mb-4" id="statsCollapse">
        <div class="row g-3 animate-fade-in">
        @php
            $colClass = trim(auth()->user()->role) === 'admin' ? 'col-12 col-xl-5' : 'col-12 col-md-6';
        @endphp

        <!-- 1. Citas Hoy (Full) -->
        <div class="{{ $colClass }}">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-primary pointer" onclick="renderDailyAgenda(new Date(), true)">
                <div class="card-body p-3 text-center">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Citas Hoy</h6>
                            <h2 class="mb-0 fw-bold text-dark display-6">{{ $stats['total_today'] }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-calendar-check text-primary fs-3"></i>
                        </div>
                    </div>
                    
                    <!-- Breakdown -->
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="bg-success bg-opacity-10 p-2 rounded-3">
                                <small class="d-block text-success fw-bold" style="font-size: 0.7rem;">COMPLETADAS</small>
                                <span class="fw-bold text-dark">{{ $stats['completed_today'] }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-warning bg-opacity-10 p-2 rounded-3">
                                <small class="d-block text-warning fw-bold" style="font-size: 0.7rem;">PENDIENTES</small>
                                <span class="fw-bold text-dark">{{ $stats['pending_today'] }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-danger bg-opacity-10 p-2 rounded-3">
                                <small class="d-block text-danger fw-bold" style="font-size: 0.7rem;">CANCELADAS</small>
                                <span class="fw-bold text-dark">{{ $stats['cancelled_today'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Ingresos (Admin) -->
        @if(trim(auth()->user()->role) === 'admin')
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-success">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-secondary text-uppercase fw-bold mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">Ingresos Hoy</h6>
                        <div class="bg-success bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-currency-dollar text-success fs-3"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="mb-0 fw-bold text-dark display-6">${{ number_format($stats['revenue_today'], 0) }}</h2>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- 3. Barberos (Admin) -->
        @if(trim(auth()->user()->role) === 'admin')
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-info">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-secondary text-uppercase fw-bold mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">Barberos Activos</h6>
                        <div class="bg-info bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-people text-info fs-3"></i>
                        </div>
                    </div>

                    <div>
                        <h3 class="mb-2 fw-bold text-dark">{{ $barbers->where('is_active', true)->count() }} <span class="text-muted fs-6 fw-normal">/ {{ $barbers->count() }}</span></h3>
                    </div>

                    <div class="d-flex gap-1 overflow-visible">
                        @foreach($barbers->take(5) as $barber)
                            @php
                                $isActive = $barber->is_active;
                                $isSpecial = $barber->special_mode ?? false;
                                $rawAvatar = $barber->user->avatar;
                                $isPath = $rawAvatar && (str_contains($rawAvatar, '/') || str_contains($rawAvatar, '.'));
                                $initials = substr($barber->name, 0, 1);
                            @endphp

                            <div class="position-relative" data-bs-toggle="tooltip" title="{{ $barber->name }} {{ $isActive ? '(Activo)' : ($isSpecial ? '(Horario Extra)' : '(Inactivo)') }}">
                                @if($isPath)
                                    <img src="{{ asset('storage/' . $rawAvatar) }}" class="rounded-circle border border-2 {{ $isActive ? 'border-success' : 'border-secondary' }}" style="width: 35px; height: 35px; object-fit: cover;">
                                @elseif($rawAvatar)
                                    <div class="rounded-circle border border-2 {{ $isActive ? 'border-success' : 'border-secondary' }} bg-light d-flex align-items-center justify-content-center fw-bold text-dark fs-5" style="width: 35px; height: 35px;">
                                        {{ $rawAvatar }}
                                    </div>
                                @else
                                    <div class="rounded-circle border border-2 {{ $isActive ? 'border-success' : 'border-secondary' }} bg-light d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 35px; height: 35px;">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <span class="position-absolute bottom-0 end-0 p-1 bg-{{ $isActive ? 'success' : 'secondary' }} border border-light rounded-circle" style="transform: scale(0.6);"></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
        </div>
    </div>

    
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



    <!-- Dynamic Agenda Layout -->
    <div class="row g-4 flex-grow-1 mb-4 h-100">
        <!-- Agenda Column: Side Panel (Right) -->
        <div class="col-12">
            <div class="card border-0 shadow-sm h-100 rounded-4 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-1">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <!-- Icon Trigger for Calendar -->
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle d-flex align-items-center justify-content-center cursor-pointer" id="customCalendarTitleTrigger" style="width: 42px; height: 42px;">
                                <i class="bi bi-calendar-event fs-5 text-primary"></i>
                            </div>
                            <!-- Hidden input for AirDatepicker -->
                            <input type="text" id="agendaDatepickerInput" style="position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px;">
                            <div>
                                <h5 class="fw-bold text-dark mb-0" id="agenda-date-label">Hoy</h5>
                                <p class="text-muted small mb-0">Toca el icono para cambiar de fecha</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if(trim(auth()->user()->role) === 'admin')
                                <select id="barberFilter" class="form-select form-select-sm border-0 bg-light rounded-pill px-3" style="width: 170px; font-weight: 500;" onchange="refreshCalendar()">
                                    <option value="">Todos los Barberos</option>
                                    @foreach(\App\Models\Barber::where('is_active', true)->get() as $barber)
                                        <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" id="barberFilter" value="{{ auth()->user()->barber?->id }}">
                            @endif
                            <button class="btn btn-light btn-sm rounded-circle" style="width: 38px; height: 38px;" onclick="openSearchModal()">
                                <i class="bi bi-search"></i>
                            </button>
                            <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" onclick="openBookingModal()">
                                <i class="bi bi-plus-lg me-1"></i> Nueva Cita
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div id="daily-agenda-container" class="pe-1">
                        <!-- Cards will be injected here -->
                        <div class="text-center py-5 opacity-50">
                            <i class="bi bi-calendar2-event fs-1 d-block mb-2"></i>
                            <p class="small">Selecciona un día para ver la agenda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Buscar Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="position-relative mb-4">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="searchInput" class="form-control form-control-lg rounded-pill ps-5 bg-light border-0" placeholder="Nombre del cliente..." onkeyup="handleSearch(this.value)">
                </div>
                <div id="searchResults" style="max-height: 300px; overflow-y: auto;">
                    <div class="text-center py-4 text-muted small">
                        Escribe para buscar...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Appointment Modal (POS) -->
<div class="modal fade" id="completeAppointmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Large modal for POS -->
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-bottom-0 pb-3"> <!-- Blue header -->
                <div>
                    <h5 class="modal-title fw-bold"><i class="bi bi-cart-check-fill me-2"></i>Completar Cita & Venta</h5>
                    <p class="mb-0 text-white-50 small">Confirma el servicio y agrega productos</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="completeAppointmentForm" onsubmit='submitComplete(event)'>
                    <input type="hidden" name="appointment_id" id="pos_appointment_id">
                    
                    <div class="row g-4">
                        <!-- Left: Service & Totals -->
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-secondary mb-3">RESUMEN</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">SERVICIO</label>
                                        <input type="text" id="pos_service_name" class="form-control-plaintext fw-bold text-dark fs-5 py-0" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">PRECIO SERVICIO</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">$</span>
                                            <input type="number" id="pos_base_price" class="form-control border-start-0 fw-bold" onchange="updatePosTotal()">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">MÉTODO DE PAGO</label>
                                        <select id="pos_payment_method" class="form-select border-2 shadow-sm" required>
                                            <option value="efectivo" selected>Efectivo</option>
                                            <option value="transferencia">Transferencia</option>
                                        </select>
                                    </div>

                                    <hr class="border-secondary border-opacity-10">

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-secondary">Productos:</span>
                                        <span class="fw-bold" id="pos_products_total">$0</span>
                                    </div>

                                    <div class="alert alert-primary border-0 shadow-sm mb-0">
                                        <label class="small fw-bold text-primary-emphasis d-block mb-1">TOTAL A COBRAR</label>
                                        <span class="fs-2 fw-bold text-primary" id="pos_grand_total">$0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Product Selector -->
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-secondary mb-3">AGREGAR PRODUCTOS</h6>
                                    
                                    <div class="input-group mb-3">
                                        <select id="pos_product_select" class="form-select">
                                            <option value="" selected disabled>Buscar producto...</option>
                                            <!-- Populated via JS -->
                                        </select>
                                        <input type="number" id="pos_product_qty" class="form-control" value="1" min="1" style="max-width: 80px;">
                                        <button type="button" class="btn btn-primary fw-bold px-3" onclick="addPosProduct()">
                                            <i class="bi bi-cart-plus-fill me-1"></i> Agregar
                                        </button>
                                    </div>

                                    <!-- Cart Table -->
                                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light sticky-top">
                                                <tr>
                                                    <th class="small text-muted border-0">Producto</th>
                                                    <th class="small text-muted border-0 text-center">Cant.</th>
                                                    <th class="small text-muted border-0 text-end">Subtotal</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="pos_cart_body">
                                                <!-- Cart Items -->
                                            </tbody>
                                        </table>
                                        <div id="pos_empty_cart" class="text-center py-4 text-muted small">
                                            <i class="bi bi-basket fs-4 d-block mb-2"></i>
                                            Sin productos agregados
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" id="btnCompletePos" class="btn btn-primary btn-lg fw-bold rounded-pill shadow">
                            <i class="bi bi-check-lg me-2"></i> Finalizar y Cobrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-muted mb-0">TELÉFONO</label>
                        <button type="button" class="btn btn-sm text-primary fw-bold p-0 border-0" onclick="pickContact()" id="btnPickContact">
                            <i class="bi bi-person-rolodex"></i> Abrir Contactos
                        </button>
                    </div>
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
    window.selectSearchResult = function(id, client, title, status, barber) {
        Swal.fire({
            title: '¿Confirmar Cita?',
            html: `
                <p class="text-muted mb-3">Servicio: <b>${serviceName}</b></p>
                <div class="form-text mt-2">Se enviará el mensaje de WhatsApp al cliente indicando que la cita ha sido agendada.</div>
            `,
            // The rest of the Swal.fire content for selectSearchResult is missing in the instruction.
            // Assuming it's meant to be a placeholder or incomplete.
            // For now, I'll close the HTML tag and the Swal.fire call as provided.
        });
    };

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
    var bookingModal = new bootstrap.Modal(document.getElementById('adminBookingModal'));

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

    // Contact Picker Logic (Mobile)
    async function pickContact() {
        const props = ['name', 'tel'];
        const opts = { multiple: false };

        if (!('contacts' in navigator && 'ContactsManager' in window)) {
            Swal.fire('No soportado', 'Tu navegador no soporta el acceso a contactos.', 'warning');
            return;
        }

        try {
            const contacts = await navigator.contacts.select(props, opts);
            if (contacts.length) {
                const contact = contacts[0];
                
                // 1. Name
                if (contact.name && contact.name.length) {
                    document.querySelector('input[name="client_name"]').value = contact.name[0];
                }
                
                // 2. Phone
                if (contact.tel && contact.tel.length) {
                    let raw = contact.tel[0];
                    // Remove generic noise
                    let num = raw.replace(/[^0-9]/g, '');
                    
                    // Handle +57 or 57 prefix (Assuming Colombia default)
                    if(num.startsWith('57') && num.length > 10) {
                        num = num.substring(2);
                    }
                    
                    // Limit to expected length (optional but good for UX)
                    if(num.length > 10) num = num.substring(num.length - 10);

                    document.querySelector('input[name="phone_number"]').value = num;
                }
            }
        } catch (ex) {
            // User cancelled or error
            console.log(ex);
        }
    }

    // Show button if supported - REMOVED to show always for debugging
    // document.addEventListener('DOMContentLoaded', () => { ... });
</script>
@endpush


@push('scripts')
<script>
    // Global State for Filters
    var calendarState = {
        weekends: true,
        showRejected: true, // Cancelled
        showCompleted: true
    };
    window.calendarInstance = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Agenda
        renderDailyAgenda(new Date());

        // Selector Desplegable (AirDatepicker)
        const triggerEl = document.getElementById('customCalendarTitleTrigger');
        const inputEl = document.getElementById('agendaDatepickerInput');
        
        if (triggerEl && inputEl) {
            window._agendaPicker = new AirDatepicker(inputEl, {
                locale: typeof localeEs !== 'undefined' ? localeEs : 'es',
                selectedDates: [new Date()],
                inline: false,
                autoClose: true,
                position: 'bottom left',
                dateFormat: 'yyyy-MM-dd',
                buttons: [{
                    content: 'Hoy',
                    className: 'air-datepicker-button-custom',
                    onClick: (dp) => {
                        const today = new Date();
                        dp.selectDate(today);
                        dp.setViewDate(today);
                        renderDailyAgenda(today);
                        dp.hide();
                    }
                }],
                onSelect({date, datepicker}) {
                    const selected = Array.isArray(date) ? date[0] : date;
                    if (selected) {
                        renderDailyAgenda(selected);
                        if (datepicker && datepicker.visible) {
                            setTimeout(() => datepicker.hide(), 100);
                        }
                    }
                }
            });

            // Manual trigger on button click
            triggerEl.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (window._agendaPicker) {
                    window._agendaPicker.show();
                }
            });
        }

        // --- Persistence: Stats Collapse State ---
        const statsCollapse = document.getElementById('statsCollapse');
        const statsIcon = document.getElementById('statsCollapseIcon');
        if (statsCollapse && statsIcon) {
            const savedState = localStorage.getItem('stats_collapse_state');
            if (savedState === 'hidden') {
                statsCollapse.classList.remove('show');
                statsIcon.classList.replace('bi-chevron-up', 'bi-chevron-down');
            }
            statsCollapse.addEventListener('hidden.bs.collapse', () => {
                localStorage.setItem('stats_collapse_state', 'hidden');
                statsIcon.classList.replace('bi-chevron-up', 'bi-chevron-down');
            });
            statsCollapse.addEventListener('shown.bs.collapse', () => {
                localStorage.setItem('stats_collapse_state', 'visible');
                statsIcon.classList.replace('bi-chevron-down', 'bi-chevron-up');
            });
        }

        // Check for Notification Auto-Open
        const openApptId = "{{ request('open_appointment') }}";
        if (openApptId) {
            axios.get(`/appointments/${openApptId}`).then(res => {
                showEventDetails(res.data);
            }).catch(err => console.error(err));
        }
    });
    
    async function renderDailyAgenda(date) {
        if (!date) date = new Date();
        if (!(date instanceof Date)) date = new Date(date);

        const container = document.getElementById('daily-agenda-container');
        const label = document.getElementById('agenda-date-label');
        if(!container) return;
        
        // Format Date for label
        const options = { weekday: 'long', day: 'numeric', month: 'long' };
        const formattedDate = new Intl.DateTimeFormat('es-ES', options).format(date);
        label.innerText = formattedDate;

        // SAFE LOCAL DATE STRING
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        const dateStr = `${y}-${m}-${d}`;
        
        const barberId = document.getElementById('barberFilter') ? document.getElementById('barberFilter').value : '';

        container.innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        try {
            const [apptsRes, slotsRes] = await Promise.all([
                axios.get(`/calendar/events?start=${dateStr}&end=${dateStr}&barber_id=${barberId}`),
                axios.get(`/calendar/slots?date=${dateStr}&barber_id=${barberId}`)
            ]);

            const appointments = apptsRes.data.filter(ev => ev.extendedProps.type === 'appointment');
            const availableSlots = slotsRes.data || [];

            // Debug: Log slots data
            console.log('📅 Slots disponibles para', dateStr, ':', availableSlots);
            console.log('📋 Citas para', dateStr, ':', appointments);

            container.innerHTML = '';
            let allItems = [
                ...appointments.map(a => ({ ...a, type: 'appointment', timeNum: new Date(a.start).getTime() })),
                ...availableSlots.map(s => ({ ...s, type: 'slot', timeNum: new Date(`${dateStr}T${s.time}`).getTime() }))
            ].sort((a, b) => a.timeNum - b.timeNum);

            if (!allItems.length) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5 opacity-75">
                        <i class="bi bi-calendar-x fs-1 d-block mb-3 text-muted"></i>
                        <h6 class="fw-bold text-dark">Día no laborable o sin turnos</h6>
                        <p class="small text-muted">Asegúrate de que el barbero tenga horario configurado.</p>
                    </div>
                `;
            } else {
                // COMPACT GRID: Always 2 columns minimum, even on mobile
                container.className = 'row row-cols-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-2 pe-1';
                
                allItems.forEach(item => {
                    const col = document.createElement('div');
                    col.className = 'col animate-fade-in';
                    
                    if (item.type === 'appointment') {
                        const statusClass = `status-${item.extendedProps.status || 'pending'}`;
                        const time = new Date(item.start).toLocaleTimeString('es-ES', { hour: 'numeric', minute: '2-digit', hour12: true });
                        const barberName = item.extendedProps.barber_name || item.extendedProps.barber || 'Barbero';
                        
                        col.innerHTML = `
                            <div class="agenda-card ${statusClass} pointer" onclick="window.showEventDetails(${item.id})">
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex justify-content-between align-items-center mb-0">
                                        <h6 class="fw-bold mb-0 text-dark text-truncate small" style="max-width: 65%;">${item.extendedProps.client_name || 'Sin nombre'}</h6>
                                        <span class="badge bg-white bg-opacity-50 text-dark border extra-small" style="font-size: 0.65rem;">${time}</span>
                                    </div>
                                    <p class="text-muted mb-0 d-flex align-items-center gap-1 text-truncate" style="font-size: 0.75rem;">
                                        <i class="bi bi-scissors"></i> <span>${item.extendedProps.service || item.title}</span>
                                    </p>
                                    <div class="mt-1 pt-1 border-top" style="border-top-color: rgba(0,0,0,0.05) !important;">
                                        <small class="text-primary fw-bold" style="font-size: 0.65rem;">${barberName}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        const timeStr = new Date(`${dateStr}T${item.time}`).toLocaleTimeString('es-ES', { hour: 'numeric', minute: '2-digit', hour12: true });
                        col.innerHTML = `
                            <div class="agenda-card status-available pointer" onclick="quickBook('${dateStr}', '${item.time}', '${barberId || item.barber_id}')">
                                <div class="d-flex flex-column gap-1 h-100 justify-content-center">
                                    <div class="d-flex justify-content-between align-items-center mb-0">
                                        <h6 class="fw-bold mb-0 text-muted extra-small">Disponible</h6>
                                        <span class="badge bg-white text-secondary border extra-small" style="font-size: 0.65rem;">${timeStr}</span>
                                    </div>
                                    <p class="text-primary mb-0" style="font-size: 0.7rem;">
                                        <i class="bi bi-plus-circle me-1"></i> <strong>Agendar ahora</strong>
                                    </p>
                                </div>
                            </div>
                        `;
                    }
                    container.appendChild(col);
                });
            }
        } catch (err) {
            console.error(err);
            container.innerHTML = '<p class="text-danger small text-center py-5">Error al cargar agenda.</p>';
        }
    }
    
    // Global Event Details Viewer
    window.showEventDetails = function(input) {
        let event = null;
        
        // If input is an ID, fetch from API (since calendarInstance is now null/deprecated)
        if (typeof input === 'number' || typeof input === 'string') {
             Swal.fire({
                 title: 'Cargando detalles...',
                 allowOutsideClick: false,
                 didOpen: () => Swal.showLoading()
             });
             
             axios.get(`/appointments/${input}`).then(res => {
                window.showEventDetails(res.data);
             }).catch(err => {
                console.error(err);
                Swal.fire('Error', 'No se pudo cargar la cita', 'error');
             });
             return;
        } 
        
        // Handle raw data from API or FullCalendar event structure
        if (input.id && !input.extendedProps) {
            event = {
                id: input.id,
                title: input.title,
                start: new Date(input.scheduled_at || input.start),
                allDay: input.allDay || false,
                extendedProps: input
            };
        } else {
            event = input;
        }

        // Ensure event.start is a Date object
        if (event.start && !(event.start instanceof Date)) {
            event.start = new Date(event.start);
        }

        if (!event || !event.extendedProps) return;
        const props = event.extendedProps;

        // Format Date
        const dateOptions = { weekday: 'long', day: 'numeric', month: 'long' };
        const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
        
        const dateStr = event.start.toLocaleDateString('es-ES', dateOptions);
        const timeStr = event.allDay ? 'Todo el día' : event.start.toLocaleTimeString('es-ES', timeOptions);
        
        // Determine Styles based on Type
        let bgStyle = '';
        
        if (props.type === 'holiday') {
            // Holiday Style
            bgStyle = 'background-color: #F8FAFE; background-image: url("https://www.gstatic.com/classroom/themes/img_birthday.jpg"); background-size: cover; background-position: center;';
        } else {
            // Appointment Style
            const statusColors = {
                'completed': 'linear-gradient(135deg, #34D399 0%, #059669 100%)',
                'cancelled': 'linear-gradient(135deg, #EF4444 0%, #B91C1C 100%)',
                'scheduled': 'linear-gradient(135deg, #6366F1 0%, #3B82F6 100%)'
            };
            bgStyle = `background: ${statusColors[props.status] || statusColors['scheduled']}`;
        }

        // STANDARD SWEETALERT 2 DESIGN
        let statusBadge = '';
        if (props.status && props.type !== 'holiday') {
            const colors = { 'scheduled': 'primary', 'completed': 'success', 'cancelled': 'danger' };
            const labels = { 'scheduled': 'Programada', 'completed': 'Completada', 'cancelled': 'Cancelada' };
            const color = colors[props.status] || 'secondary';
            const label = labels[props.status] || props.status;
            statusBadge = `<span class="badge bg-${color}">${label}</span>`;
        }
        
        Swal.fire({
            title: event.title,
            html: `
                <div class="text-start fs-6">
                    <p class="mb-2"><strong><i class="bi bi-calendar-event me-2"></i>Fecha:</strong> ${dateStr}</p>
                    <p class="mb-2"><strong><i class="bi bi-clock me-2"></i>Hora:</strong> ${timeStr}</p>
                    ${props.barber ? `<p class="mb-2"><strong><i class="bi bi-person me-2"></i>Barbero:</strong> ${props.barber}</p>` : ''}
                    ${props.service ? `<p class="mb-2"><strong><i class="bi bi-scissors me-2"></i>Servicio:</strong> ${props.service} (Base: ${formatMoney(props.base_price)})</p>` : ''}
                    
                    ${props.products && props.products.length > 0 ? `
                        <div class="mb-2 ps-4 border-start border-3 border-secondary">
                            <small class="text-muted d-block fw-bold">Productos Adicionales:</small>
                            ${props.products.map(p => `
                                <div class="d-flex justify-content-between small">
                                    <span>${p.qty}x ${p.name}</span>
                                    <span class="fw-bold">${formatMoney(p.price * p.qty)}</span>
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}

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
                            <div class="mt-2 fs-5 fw-bold border-top border-secondary pt-2 text-dark">
                                Total Cobrado: ${formatMoney(props.final_price || props.price)}
                            </div>
                            ${props.completed_by ? `<div class="mt-1 small text-dark"><i class="bi bi-person-check me-1"></i>Completada por: <strong>${props.completed_by}</strong></div>` : ''}
                        </div>` : ''
                    }
                </div>
                <div class="d-flex justify-content-center mt-4 w-100">
                    ${props.status !== 'completed' ? `
                        <div class="d-flex justify-content-center gap-1 flex-wrap w-100">
                            <button onclick="completeAppointment(${event.id}, ${props.price})" class="btn btn-success flex-grow-1 fw-bold">
                                <i class="bi bi-check-lg me-1"></i> Completar
                            </button>
                            <button onclick="editAppointment(${event.id})" class="btn btn-primary flex-grow-1 fw-bold">
                                <i class="bi bi-pencil-fill me-1"></i> Editar
                            </button>
                            <button onclick="cancelAppointment(${event.id})" class="btn btn-warning flex-grow-1 fw-bold text-white">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </button>
                            <button onclick="deleteAppointment(${event.id})" class="btn btn-outline-danger flex-grow-0" title="Eliminar permanentemente">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    ` : `
                        <div class="d-flex justify-content-center gap-2 w-100">
                            <button onclick="reopenAppointment(${event.id})" class="btn btn-primary flex-grow-1 fw-bold">
                                <i class="bi bi-arrow-repeat me-1"></i> Volver a Abrir
                            </button>
                            <button onclick="deleteAppointment(${event.id})" class="btn btn-outline-danger flex-grow-0" title="Eliminar permanentemente">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                            <button onclick="Swal.close()" class="btn btn-secondary flex-grow-0 fw-bold">
                                <i class="bi bi-x mx-1"></i>
                            </button>
                        </div>
                    `}
                </div>
            `,
            icon: 'info',
            showConfirmButton: false,
            showCloseButton: true,
            showCancelButton: false,
            customClass: {
                popup: 'rounded-4 shadow'
            }
        });
    };

    // Inject Server Data for JS
    var serverData = {
        services: @json($services ?? []),
        barbers: @json($barbers ?? []),
        products: @json($products ?? [])
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
        // calendarInstance is now null, we refresh the custom agenda instead
        refreshCalendar();
    };

    // Toggle Logic
    function toggleOption(e, type) {
        e.preventDefault();
        e.stopPropagation();

        if (type === 'weekends') {
            calendarState.weekends = !calendarState.weekends;
            // No full calendar to update
        } else if (type === 'rejected') {
            calendarState.showRejected = !calendarState.showRejected;
        } else if (type === 'completed') {
            calendarState.showCompleted = !calendarState.showCompleted;
        }
        
        refreshCalendar();
        updateCheckboxes();
    }

    // Refresh Agenda (Global)
    window.refreshCalendar = function() {
        // Try to get date from AirDatepicker instance
        const date = (window._agendaPicker) ? window._agendaPicker.selectedDates[0] : new Date();
        renderDailyAgenda(date);
    };

    window.quickBook = function(date, time24, barberId) {
        // Prepare the standard booking modal but with pre-filled fields
        // time24 is "HH:mm" (e.g. "14:30")
        // We need 12h for the modal display:
        let [h, m] = time24.split(':');
        let h_int = parseInt(h);
        const ampm = h_int >= 12 ? 'PM' : 'AM';
        h_int = h_int % 12 || 12;
        const time12 = `${h_int}:${m} ${ampm}`;

        openBookingModal({
            date: date,
            time: time12,
            barber_id: barberId
        });
    };

    // Edit Appointment Logic (Enhanced)
    window.editAppointment = function(id) {
        Swal.close();

        // Show Loading while fetching fresh data
        Swal.fire({
            title: 'Cargando datos...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        axios.get(`/appointments/${id}`).then(res => {
            const data = res.data;
            const currentServiceId = data.service_id;
            const currentBarberId = data.barber_id;
            
            // Format Date & Time for the modal
            const startDt = new Date(data.scheduled_at || data.start);
            const originalDate = startDt.toISOString().split('T')[0];
            
            let hours = startDt.getHours();
            let minutes = startDt.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            const originalTime = `${hours}:${minutes} ${ampm}`;

            // Build Options
            const serviceOptions = serverData.services.map(s => 
                `<option value="${s.id}" data-custom="${s.is_custom || ['otro', 'otro servicio'].includes(s.name.toLowerCase())}" ${s.id == currentServiceId ? 'selected' : ''}>${s.name} ($${s.price})</option>`
            ).join('');

            const barberOptions = serverData.barbers.map(b => 
                `<option value="${b.id}" ${b.id == currentBarberId ? 'selected' : ''}>${b.name}</option>`
            ).join('');

            const todayStr = new Date().toLocaleDateString('en-CA');

            Swal.fire({
                title: 'Editar Cita',
                html: `
                    <div class="text-start">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">NOMBRE CLIENTE</label>
                                <input type="text" id="edit-client-name" class="form-control" value="${data.client_name || ''}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">WhatsApp</label>
                                <input type="text" id="edit-client-phone" class="form-control" value="${data.client_phone || ''}" placeholder="Ej: 3001234567">
                            </div>
                        </div>
                        <label class="form-label fw-bold small text-muted">SERVICIO</label>
                        <select id="edit-service" class="form-select mb-3">
                            ${serviceOptions}
                        </select>
                        <div id="edit-custom-details-container" class="mb-3 d-none">
                            <label class="form-label fw-bold small text-muted">DETALLE</label>
                            <input type="text" id="edit-custom-details" class="form-control" value="${data.custom_details || ''}">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">BARBERO</label>
                                <select id="edit-barber" class="form-select">
                                    ${barberOptions}
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">FECHA</label>
                                <input type="date" id="edit-date" class="form-control" min="${todayStr}" value="${originalDate}">
                            </div>
                        </div>
                        <label class="form-label fw-bold small text-muted">HORARIO</label>
                        <div id="edit-slots-container" class="d-flex flex-wrap gap-2 p-3 border rounded bg-light" style="min-height: 60px; max-height: 150px; overflow-y: auto;"></div>
                        <input type="hidden" id="edit-time" value="${originalTime}">
                        <div id="edit-time-display" class="mt-2 text-primary fw-bold small text-end">Seleccionado: ${originalTime}</div>
                    </div>
                `,
                width: '500px',
                showCancelButton: true,
                confirmButtonText: 'Guardar Cambios',
                preConfirm: () => {
                    const payload = {
                        client_name: document.getElementById('edit-client-name').value,
                        client_phone: document.getElementById('edit-client-phone').value,
                        service_id: document.getElementById('edit-service').value,
                        barber_id: document.getElementById('edit-barber').value,
                        date: document.getElementById('edit-date').value,
                        time: document.getElementById('edit-time').value,
                        custom_details: document.getElementById('edit-custom-details').value
                    };
                    if(!payload.client_name.trim()) return Swal.showValidationMessage('El nombre es obligatorio');
                    if(!payload.time) return Swal.showValidationMessage('Debes elegir un horario');
                    return payload;
                },
                didOpen: () => {
                    // Similar slot logic as before
                    const bSel = document.getElementById('edit-barber');
                    const dInp = document.getElementById('edit-date');
                    const sCont = document.getElementById('edit-slots-container');
                    const tInp = document.getElementById('edit-time');
                    const tDisp = document.getElementById('edit-time-display');

                    function fetchSlots() {
                        sCont.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';
                        axios.get(`/api/slots?barber_id=${bSel.value}&date=${dInp.value}`)
                            .then(res => {
                                let slots = res.data;
                                if (bSel.value == currentBarberId && dInp.value == originalDate && !slots.includes(originalTime)) {
                                    slots.push(originalTime);
                                    slots.sort();
                                }
                                sCont.innerHTML = '';
                                if(!slots.length) { sCont.innerHTML = '<span class="text-danger small">No hay turnos</span>'; return; }
                                slots.forEach(t => {
                                    const btn = document.createElement('button');
                                    btn.type = 'button'; btn.textContent = t;
                                    btn.className = `btn btn-sm ${tInp.value === t ? 'btn-primary' : 'btn-outline-secondary'}`;
                                    btn.onclick = () => {
                                        tInp.value = t; tDisp.textContent = 'Seleccionado: ' + t;
                                        Array.from(sCont.children).forEach(c => c.className = 'btn btn-sm btn-outline-secondary');
                                        btn.className = 'btn btn-sm btn-primary';
                                    };
                                    sCont.appendChild(btn);
                                });
                            });
                    }
                    bSel.onchange = fetchSlots;
                    dInp.onchange = fetchSlots;
                    fetchSlots();
                }
            }).then(editRes => {
                if (editRes.isConfirmed) {
                    axios.put(`/appointments/${id}`, editRes.value)
                        .then(() => {
                            Swal.fire('¡Actualizado!', '', 'success');
                            refreshCalendar();
                        }).catch(err => Swal.fire('Error', 'No se pudo guardar', 'error'));
                }
            });
        }).catch(err => {
            console.error(err);
            Swal.fire('Error', 'No se pudo cargar la cita', 'error');
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





    // Actions
    // --- POS & COMPLETION SYSTEM ---

    // 1. Currency Formatter ($ 10,000)
    var currencyFmt = new Intl.NumberFormat('en-US', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });

    function formatMoney(amount) {
        return '$ ' + currencyFmt.format(amount);
    }

    // 2. Main Entry Point
    window.completeAppointment = function(id, basePrice) {
        Swal.close(); // Close event details

        Swal.fire({
            title: '¿Incluir Productos?',
            text: '¿El cliente compró productos adicionales?',
            icon: 'question',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-cart-plus me-1"></i> Sí, Agregar',
            denyButtonText: '<i class="bi bi-scissors me-1"></i> No, Solo Servicio',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd', // Primary
            denyButtonColor: '#6c757d', // Secondary
        }).then((result) => {
            if (result.isConfirmed) {
                openPosModal(id, basePrice);
            } else if (result.isDenied) {
                quickComplete(id, basePrice);
            }
        });
    };

    // 3. Quick Complete (Old Logic)
    function quickComplete(id, basePrice) {
        Swal.fire({
            title: 'Finalizar Servicio',
            text: 'Confirma el precio final del servicio',
            input: 'number',
            inputValue: basePrice,
            inputLabel: 'Precio Total',
            showCancelButton: true,
            confirmButtonText: 'Finalizar',
            confirmButtonColor: '#10B981', // Success
            inputValidator: (value) => {
                if (!value) return 'Debes escribir el precio';
            }
        }).then((res) => {
            if (res.isConfirmed) {
                axios.patch(`/appointments/${id}/complete`, { confirmed_price: res.value })
                    .then(() => {
                        Swal.fire('¡Listo!', 'Cita completada.', 'success').then(() => location.reload());
                    })
                    .catch(() => Swal.fire('Error', 'No se pudo completar.', 'error'));
            }
        });
    }

    // 4. POS Modal Logic
    var posCart = [];
    var posModal = new bootstrap.Modal(document.getElementById('completeAppointmentModal'));

    function openPosModal(id, basePrice) {
        // Reset State
        posCart = [];
        document.getElementById('pos_appointment_id').value = id;
        document.getElementById('pos_base_price').value = basePrice;
        
        let serviceName = "Servicio Cita #" + id;
        if(window.calendarInstance) {
            const event = window.calendarInstance.getEventById(id);
            if(event) {
                if(event.extendedProps.service) {
                    serviceName = event.extendedProps.service;
                }
                // [NEW] Load Existing Products if any
                if(event.extendedProps.products && Array.isArray(event.extendedProps.products)) {
                    event.extendedProps.products.forEach(p => {
                        posCart.push({
                            id: p.id.toString(), // Ensure string for matching
                            name: p.name,
                            price: parseFloat(p.price),
                            qty: parseInt(p.qty)
                        });
                    });
                }
            }
        }
        document.getElementById('pos_service_name').value = serviceName;

        const select = document.getElementById('pos_product_select');
        select.innerHTML = '<option value="" selected disabled>Buscar producto...</option>';
        
        if (typeof serverData !== 'undefined' && serverData.products && serverData.products.length > 0) {
            serverData.products.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                // Format: Name ($ 10,000) - Stock: 5
                opt.text = `${p.name} (${formatMoney(p.price)}) - Stock: ${p.stock}`;
                opt.dataset.price = p.price;
                opt.dataset.name = p.name;
                opt.dataset.stock = p.stock;
                if(p.stock <= 0) opt.disabled = true;
                select.appendChild(opt);
            });
        }

        renderPosCart();
        posModal.show();
    }

    window.addPosProduct = function() {
        const select = document.getElementById('pos_product_select');
        const qtyInput = document.getElementById('pos_product_qty');
        const productId = select.value;
        const qty = parseInt(qtyInput.value);

        if (!productId || qty < 1) return;

        const option = select.options[select.selectedIndex];
        const price = parseFloat(option.dataset.price);
        const name = option.dataset.name;
        const stock = parseInt(option.dataset.stock);

        if (qty > stock) {
            Swal.fire('Stock Insuficiente', `Solo quedan ${stock} unidades`, 'warning');
            return;
        }

        const existing = posCart.find(i => i.id === productId);
        if (existing) {
            if (existing.qty + qty > stock) {
                Swal.fire('Stock Insuficiente', 'No puedes exceder el stock.', 'warning');
                return;
            }
            existing.qty += qty;
        } else {
            posCart.push({ id: productId, name: name, price: price, qty: qty });
        }

        select.value = "";
        qtyInput.value = 1;
        renderPosCart();
    };

    window.reopenAppointment = function(id) {
        Swal.fire({
            title: '¿Reabrir Cita?',
            text: 'La cita volverá a "Programada". Los productos se devolverán al stock y se quitarán de la venta.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, Reabrir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.patch(`/appointments/${id}/reopen`)
                .then(response => {
                    Swal.fire('¡Reabierta!', 'La cita está programada nuevamente.', 'success');
                    setTimeout(() => location.reload(), 1000); 
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo reabrir la cita.', 'error');
                });
            }
        });
    };

    window.deleteAppointment = function(id) {
        Swal.fire({
            title: '¿Eliminar Permanentemente?',
            text: 'Esta acción NO se puede deshacer. Si la cita estaba completada, el stock de productos se restaurará automáticamente.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Sí, Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/appointments/${id}`)
                .then(response => {
                    Swal.fire('¡Eliminada!', 'La cita ha sido eliminada por completo.', 'success');
                    setTimeout(() => location.reload(), 1000); // Reload to reflect stats
                })
                .catch(error => {
                    const msg = error.response?.data?.message || 'No se pudo eliminar.';
                    Swal.fire('Error', msg, 'error');
                });
            }
        });
    };

    window.removePosProduct = function(index) {
        posCart.splice(index, 1);
        renderPosCart();
    };
    // Search Logic (Global)
    window.openSearchModal = function() {
        const modal = new bootstrap.Modal(document.getElementById('searchModal'));
        modal.show();
        setTimeout(() => document.getElementById('searchInput').focus(), 500);
    };

    var searchTimeout;
    window.handleSearch = function(query) {
        const tbody = document.getElementById('pos_cart_body');
        const emptyMsg = document.getElementById('pos_empty_cart');
        const totalSpan = document.getElementById('pos_products_total');
        
        tbody.innerHTML = '';
        let productsTotal = 0;

        if (posCart.length === 0) {
            emptyMsg.classList.remove('d-none');
        } else {
            emptyMsg.classList.add('d-none');
            posCart.forEach((item, index) => {
                const subtotal = item.price * item.qty;
                productsTotal += subtotal;
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="small fw-bold">${item.name}</td>
                    <td class="small text-center">${item.qty}</td>
                    <td class="small text-end">${formatMoney(subtotal)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm text-danger p-0" onclick="removePosProduct(${index})">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        totalSpan.textContent = formatMoney(productsTotal);
        updatePosTotal(productsTotal);
    }

    function updatePosTotal(productsTotal = null) {
        if (productsTotal === null) {
             productsTotal = posCart.reduce((acc, item) => acc + (item.price * item.qty), 0);
        }
        
        const basePrice = parseFloat(document.getElementById('pos_base_price').value) || 0;
        const grandTotal = basePrice + productsTotal;
        
        document.getElementById('pos_grand_total').textContent = formatMoney(grandTotal);
    }

    window.submitComplete = function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnCompletePos');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

        const appointmentId = document.getElementById('pos_appointment_id').value;
        const basePrice = parseFloat(document.getElementById('pos_base_price').value) || 0;
        
        // Calculate Products Total
        const productsTotal = posCart.reduce((acc, item) => acc + (item.price * item.qty), 0);
        
        // Final Price = Service + Products
        const finalPrice = basePrice + productsTotal;

        const paymentMethod = document.getElementById('pos_payment_method').value;
        const payload = {
            confirmed_price: finalPrice, // Send Grand Total
            payment_method: paymentMethod, // [NEW] Added payment method
            products: posCart.map(i => ({ product_id: i.id, quantity: i.qty }))
        };

        axios.patch(`/appointments/${appointmentId}/complete`, payload)
            .then(response => {
                posModal.hide();
                
                const sale = response.data.sale;
                const saleId = sale ? sale.id : null;
                const clientPhone = response.data.client_phone || "";
                
                // Formatted WhatsApp Message
                let productsSummary = posCart.map(i => `• ${i.qty}x ${i.name} ($${currencyFmt.format(i.price * i.qty)})`).join('\n');
                let waMessage = `*Barbería JR - Tu Comprobante* ✂️\n\nHola *${sale ? sale.client_name : 'Cliente'}*, gracias por tu visita. Aquí tienes el detalle:\n\n${productsSummary}\n\n*Total: $${currencyFmt.format(finalPrice)}*\n*Método: ${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1)}*`;
                let waUrl = `https://wa.me/${clientPhone.replace(/[^0-9]/g, '')}?text=${encodeURIComponent(waMessage)}`;

                Swal.fire({
                    title: '¡Cita Completada!',
                    html: `El recibo se está enviando automáticamente por WhatsApp al cliente.<br><br>
                           <small class="text-muted">Si el cliente no recibe el mensaje, verifica la conexión del bot.</small>`,
                    icon: 'success',
                    timer: 4000,
                    showConfirmButton: true,
                    confirmButtonText: 'Entendido',
                    customClass: { popup: 'rounded-4' }
                }).then(() => {
                    location.reload();
                });
            })
            .catch(err => {
                console.error(err);
                const msg = err.response?.data?.message || 'Error desconocido al procesar.';
                Swal.fire('Error', msg, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
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

    /* AirDatepicker Custom Overrides */
    .air-datepicker {
        --adp-font-family: 'Outfit', sans-serif;
        --adp-border-radius: 16px;
        --adp-accent-color: #2563EB;
        --adp-background-color: #fff;
        --adp-cell-border-radius: 8px;
        --adp-width: 320px;
        z-index: 1050 !important;
        border: none !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }

    .air-datepicker-cell.-selected-, .air-datepicker-cell.-selected-.-current- {
        background: #2563EB !important;
    }

    .air-datepicker-button {
        color: #2563EB !important;
        font-weight: 600;
    }

    /* Force clean grid for AirDatepicker (Fixing the "shifted days" issue) */
    .air-datepicker-body--cells {
        display: grid !important;
        grid-template-columns: repeat(7, 1fr) !important;
        gap: 2px !important;
    }

    /* Agenda Card Styling Refinement */
    .agenda-card {
        border-radius: 16px;
        transition: all 0.2s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .agenda-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .status-scheduled { border-left: 5px solid #6366F1 !important; }
    .status-completed { border-left: 5px solid #10B981 !important; }
    .status-cancelled { border-left: 5px solid #EF4444 !important; opacity: 0.8; }
    .status-available { border-left: 4px solid #dee2e6 !important; background-color: #f8fafc !important; }

    .pointer { cursor: pointer; }
</style>
@endpush