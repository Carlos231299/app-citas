@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Dashboard - Barbería JR')
@section('header', 'Agenda')

@section('content')
<div class="d-flex flex-column h-100">
    <!-- Stats Row -->
    <div class="row g-2 mb-4 animate-fade-in">
        <!-- Citas Hoy -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-primary">
                <div class="card-body p-2">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-md-2 mb-1 mb-md-0">
                            <i class="bi bi-calendar-event text-primary fs-5"></i>
                        </div>
                        <div class="text-center text-md-start">
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Citas Hoy</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['total_today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingresos -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-success">
                <div class="card-body p-2">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle me-md-2 mb-1 mb-md-0">
                            <i class="bi bi-cash-stack text-success fs-5"></i>
                        </div>
                        <div class="text-center text-md-start">
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Ingresos</h6>
                            <h4 class="mb-0 fw-bold text-dark">${{ number_format($stats['revenue_today'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendientes -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-warning">
                <div class="card-body p-2">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-md-2 mb-1 mb-md-0">
                            <i class="bi bi-clock-history text-warning fs-5"></i>
                        </div>
                        <div class="text-center text-md-start">
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Pendientes</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['pending_today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barberos Disponibles -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-info">
                <div class="card-body p-2">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="bg-info bg-opacity-10 p-2 rounded-circle me-md-2 mb-1 mb-md-0">
                            <i class="bi bi-people-fill text-info fs-5"></i>
                        </div>
                        <div class="text-center text-md-start">
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Disponibles</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['active_barbers'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="card border-0 shadow-sm flex-grow-1 overflow-hidden" style="min-height: 600px;">
        <div class="card-body p-0 p-md-3 h-100 position-relative">
            <!-- Custom View Selector (Hidden in DOM, injected by JS) -->
            <div id="custom-view-selector" class="d-none">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle btn-sm fw-bold border-0 bg-transparent text-dark" type="button" id="calendarViewBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        Mes
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="calendarViewBtn">
                        <li><a class="dropdown-item" href="#" data-view="dayGridMonth">Mes</a></li>
                        <li><a class="dropdown-item" href="#" data-view="timeGridWeek">Semana</a></li>
                        <li><a class="dropdown-item" href="#" data-view="timeGridDay">Día</a></li>
                        <li><a class="dropdown-item" href="#" data-view="listWeek">Agenda</a></li>
                    </ul>
                </div>
            </div>
            
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
            themeSystem: 'bootstrap5',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: '' // Leaving empty to inject custom dropdown
            },
            navLinks: true, 
            height: '100%',
            contentHeight: 'auto',
            aspectRatio: 1.35,
            handleWindowResize: true,
            locale: 'es',
            slotMinTime: '08:00:00',
            slotMaxTime: '21:00:00',
            expandRows: true, 
            stickyHeaderDates: true,
            allDaySlot: false,
            dayMaxEvents: true,
            views: {
                dayGridMonth: { dayMaxEvents: 2 },
                timeGrid: { dayMaxEvents: true }
            },
            // Enforce 12-hour format
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
            events: '/api/calendar/events',
            
            // Lifecycle Hooks
            datesSet: function(info) {
                // Update dropdown text based on current view
                const viewNameMap = {
                    'dayGridMonth': 'Mes',
                    'timeGridWeek': 'Semana',
                    'timeGridDay': 'Día',
                    'listWeek': 'Agenda'
                };
                const btn = document.getElementById('calendarViewBtn');
                if(btn) btn.innerText = viewNameMap[info.view.type] || 'Vista';
            },
            
            eventClick: function(info) {
                const event = info.event;
                const props = event.extendedProps;
                
                // Format Date 12h
                const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
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
            }
        });
        
        calendar.render();

        // Inject Custom Dropdown into FullCalendar Toolbar
        const toolbarRight = document.querySelector('.fc-toolbar-chunk:last-child');
        const selector = document.getElementById('custom-view-selector');
        
        if (toolbarRight && selector) {
            selector.classList.remove('d-none');
            toolbarRight.appendChild(selector);
            
            // Re-bind dropdown clicks to calendar
            selector.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const view = e.target.getAttribute('data-view');
                    if(view) calendar.changeView(view);
                });
            });
        }
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
    
    // Initial Load
    document.addEventListener('DOMContentLoaded', initCalendar);
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
    
    /* Toolbar & Buttons */
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 400 !important;
        color: #1E293B;
    }
    .fc-header-toolbar {
        margin-bottom: 1.5rem !important;
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
    
    /* Clean Event Chips */
    .fc-event {
        border: none !important;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 0.85rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        margin: 2px 0;
        font-weight: 500;
    }
</style>
@endpush
