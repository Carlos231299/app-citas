@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Dashboard - Barbería JR')
@section('header', 'Agenda')

@section('content')
<div class="d-flex flex-column h-100">
    <!-- Stats Row -->
    <div class="row g-3 mb-4 animate-fade-in">
        <!-- Citas Hoy -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-calendar-event text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold">Citas Hoy</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['total_today'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingresos -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-success">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-cash-stack text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold">Ingresos (Hoy)</h6>
                            <h3 class="mb-0 fw-bold text-dark">${{ number_format($stats['revenue_today'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendientes -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-clock-history text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold">Pendientes</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['pending_today'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barberos -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-dark">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-dark bg-opacity-10 p-2 rounded-circle me-3">
                            <i class="bi bi-people-fill text-dark fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold">Barberos Activos</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $stats['active_barbers'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="card border-0 shadow-sm flex-grow-1 overflow-hidden">
        <div class="card-body p-0 h-100">
            <div id="calendar" class="h-100"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function initCalendar() {
        var calendarEl = document.getElementById('calendar');
        if(!calendarEl) return;
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'es',
            slotMinTime: '08:00:00',
            slotMaxTime: '18:00:00',
            height: '100%',
            expandRows: true, 
            stickyHeaderDates: true,
            allDaySlot: false,
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short',
                hour12: true
            },
            events: '/api/calendar/events',
            eventDidMount: function(info) {
                // Tooltip logic
                const event = info.event;
                const props = event.extendedProps;
                const tooltipContent = `
                    <div class="text-start">
                        <strong>${event.title}</strong><br>
                        <small>${props.service}</small><br>
                        <small class="text-warning">${props.barber}</small>
                    </div>
                `;
                
                new bootstrap.Tooltip(info.el, {
                    title: tooltipContent,
                    html: true,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
            eventClick: function(info) {
                const event = info.event;
                const props = event.extendedProps;
                
                // Format Date
                const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                const dateStr = event.start.toLocaleDateString('es-ES', dateOptions);

                let actionButtons = '';
                if(props.status === 'scheduled') {
                    actionButtons = `
                        <div class="d-grid gap-2 mt-4">
                            <button onclick="completeAppointment(${event.id}, ${props.price})" class="btn btn-success fw-bold">
                                <i class="bi bi-check-circle-fill me-2"></i>Marcar como Completada
                            </button>
                            <button onclick="cancelAppointment(${event.id})" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-2"></i>Cancelar Cita
                            </button>
                        </div>
                    `;
                }

                Swal.fire({
                    title: `<h4 class="mb-0 text-start">${event.title}</h4>`,
                    html: `
                        <div class="text-start mt-3">
                            <div class="mb-2"><strong class="text-secondary">Fecha:</strong> <span class="text-dark">${dateStr}</span></div>
                            <div class="mb-2"><strong class="text-secondary">Barbero:</strong> <span class="text-dark">${props.barber}</span></div>
                            <div class="mb-2"><strong class="text-secondary">Servicio:</strong> <span class="text-dark">${props.service}</span></div>
                            <div class="mb-2"><strong class="text-secondary">Teléfono:</strong> <span class="text-dark">${props.client_phone}</span></div>
                            <div class="mb-2"><strong class="text-secondary">Detalles:</strong> <span class="fst-italic text-dark">${props.custom_details || 'N/A'}</span></div>
                            <div class="mb-2"><strong class="text-secondary">Estado:</strong> 
                                <span class="badge ${props.status === 'completed' ? 'bg-success' : (props.status === 'cancelled' ? 'bg-danger' : 'bg-primary')}">
                                    ${{
                                        'scheduled': 'PROGRAMADA',
                                        'completed': 'COMPLETADA',
                                        'cancelled': 'CANCELADA'
                                    }[props.status] || props.status.toUpperCase()}
                                </span>
                            </div>
                            ${actionButtons}
                        </div>
                    `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    background: '#fff',
                    customClass: {
                        popup: 'rounded-4 shadow-lg'
                    }
                });
            },
            windowResize: function(view) {
                calendar.updateSize();
            }
        });
        calendar.render();
    }

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
                    // Refresh
                    initCalendar(); // OR location.reload() 
                    // location.reload() might be safer to ensure fresh state
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
            inputPlaceholder: 'Ej: Cliente no asistió...',
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

    // Run immediately
    initCalendar();
    document.addEventListener('DOMContentLoaded', initCalendar);
</script>
<style>
    /* Force Full Height for Calendar container */
    #spa-content { height: 100%; display: flex; flex-direction: column; }
    .fc { height: 100%; }
    .fc-view-harness { height: 100% !important; }
    
    /* Clean Event Styling */
    .fc-event {
        cursor: pointer;
        border: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 4px !important;
        transition: transform 0.1s;
    }
    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        z-index: 50;
    }
</style>
@endpush
