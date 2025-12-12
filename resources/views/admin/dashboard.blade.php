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
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Barberos Disponibles</h6>
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
            <!-- Custom View Selector (Google Style) -->
            <div id="custom-view-selector" class="d-none">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle btn-sm fw-bold border-0 bg-transparent text-dark d-flex align-items-center gap-2" type="button" id="calendarViewBtn" data-bs-toggle="dropdown" aria-expanded="false" style="color: #3C4043 !important;">
                        <span>Mes</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-2" aria-labelledby="calendarViewBtn" style="min-width: 240px;">
                        <!-- Views -->
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="timeGridDay"><span>Día</span><span class="text-muted small">D</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="timeGridWeek"><span>Semana</span><span class="text-muted small">W</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="dayGridMonth"><span>Mes</span><span class="text-muted small">M</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="listYear"><span>Año</span><span class="text-muted small">Y</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="listWeek"><span>Agenda</span><span class="text-muted small">A</span></a></li>
                        <li><a class="dropdown-item d-flex justify-content-between align-items-center rounded-2 py-2" href="#" data-view="fourDay"><span>4 días</span><span class="text-muted small">X</span></a></li>
                        
                        <li><hr class="dropdown-divider my-2"></li>
                        
                        <!-- Toggles -->
                        <li>
                            <div class="dropdown-item d-flex gap-2 align-items-center rounded-2 py-2" onclick="toggleOption(event, 'weekends')">
                                <i class="bi bi-check-lg text-primary opacity-0" id="check-weekends"></i>
                                <span>Mostrar fines de semana</span>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-item d-flex gap-2 align-items-center rounded-2 py-2" onclick="toggleOption(event, 'rejected')">
                                <i class="bi bi-check-lg text-primary opacity-0" id="check-rejected"></i>
                                <span>Mostrar eventos rechazados</span>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-item d-flex gap-2 align-items-center rounded-2 py-2" onclick="toggleOption(event, 'completed')">
                                <i class="bi bi-check-lg text-primary opacity-0" id="check-completed"></i>
                                <span>Mostrar tareas completadas</span>
                            </div>
                        </li>
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
    // Global State for Filters
    let calendarState = {
        weekends: true,
        showRejected: true, // Cancelled
        showCompleted: true
    };
    let calendarInstance = null;

    function initCalendar() {
        var calendarEl = document.getElementById('calendar');
        if(!calendarEl) return;
        
        // Initialize State UI
        updateCheckboxes();

        calendarInstance = new FullCalendar.Calendar(calendarEl, {
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
            weekends: true, // Default
            firstDay: 1, // Lunes
            
            // Time Format
            slotMinTime: '08:00:00',
            slotMaxTime: '21:00:00',
            expandRows: true, 
            stickyHeaderDates: true,
            allDaySlot: false,
            dayMaxEvents: true,
            
            views: {
                dayGridMonth: { dayMaxEvents: 2 },
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
            events: '/api/calendar/events',
            
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
                const dateOptions = { weekday: 'long', month: 'long', day: 'numeric' };
                const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
                
                const dateStr = event.start.toLocaleDateString('es-ES', dateOptions);
                const timeStr = event.start.toLocaleTimeString('es-ES', timeOptions);
                const fullDateStr = `${dateStr} ⋅ ${timeStr}`;

                // Header Style (Gradient based on status or random for "Birthday" look)
                // Using a festive gradient for completed/special, standard blue for others
                const bgGradient = props.status === 'completed' 
                    ? 'linear-gradient(135deg, #34D399 0%, #059669 100%)' // Green
                    : (props.status === 'cancelled' ? 'linear-gradient(135deg, #EF4444 0%, #B91C1C 100%)' // Red
                    : 'linear-gradient(135deg, #6366F1 0%, #3B82F6 100%)'); // Blue/Indigo

                // Action Buttons (Complete/Cancel) HTML
                let actionButtons = '';
                if(props.status === 'scheduled') {
                    actionButtons = `
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button onclick="Swal.close(); cancelAppointment(${event.id})" class="btn btn-light text-danger border-0 btn-sm rounded-3 fw-bold px-3">
                                Cancelar
                            </button>
                            <button onclick="Swal.close(); completeAppointment(${event.id}, ${props.price})" class="btn btn-primary border-0 btn-sm rounded-3 fw-bold px-3">
                                Completar
                            </button>
                        </div>
                    `;
                }

                Swal.fire({
                    html: `
                        <div style="overflow: hidden; border-radius: 16px;">
                            <!-- Header -->
                            <div class="position-relative p-4 text-white d-flex align-items-start justify-content-between" style="background: ${bgGradient}; min-height: 120px;">
                                <div style="position: relative; z-index: 2;">
                                    <!-- Optional Icon watermark could go here -->
                                </div>
                                <div class="d-flex gap-2" style="position: absolute; top: 15px; right: 15px; z-index: 10;">
                                    <button class="btn btn-link text-white p-0 opacity-75 hover-opacity-100" onclick="editService(${event.id})">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-link text-white p-0 opacity-75 hover-opacity-100" onclick="Swal.close()">
                                        <i class="bi bi-x-lg fs-5"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="text-start p-4 bg-white">
                                <!-- Title -->
                                <h3 class="fw-normal mb-1" style="color: #3C4043; font-family: 'Google Sans', 'Roboto', sans-serif;">
                                    ${event.title}
                                </h3>
                                
                                <!-- Date -->
                                <div class="d-flex mt-3 mb-4">
                                    <div class="me-3" style="width: 24px;"><i class="bi bi-clock text-secondary fs-5"></i></div>
                                    <div>
                                        <div class="fs-5 text-dark">${fullDateStr}</div>
                                        ${props.duration ? `<div class="small text-muted">Duración: ${props.duration}</div>` : ''}
                                    </div>
                                </div>

                                <!-- Details List -->
                                <div class="d-flex mb-3">
                                    <div class="me-3" style="width: 24px;"><i class="bi bi-person text-secondary fs-5"></i></div>
                                    <div class="align-self-center text-secondary">${props.barber} (Barbero)</div>
                                </div>

                                <div class="d-flex mb-3">
                                    <div class="me-3" style="width: 24px;"><i class="bi bi-scissors text-secondary fs-5"></i></div>
                                    <div class="align-self-center text-secondary">${props.service}</div>
                                </div>

                                ${props.client_phone ? `
                                <div class="d-flex mb-3">
                                    <div class="me-3" style="width: 24px;"><i class="bi bi-telephone text-secondary fs-5"></i></div>
                                    <div class="align-self-center text-secondary">${props.client_phone}</div>
                                </div>` : ''}

                                ${props.custom_details ? `
                                <div class="d-flex mb-3">
                                    <div class="me-3" style="width: 24px;"><i class="bi bi-justify-left text-secondary fs-5"></i></div>
                                    <div class="align-self-center text-secondary">${props.custom_details}</div>
                                </div>` : ''}
                                
                                <div class="d-flex mb-2">
                                    <div class="me-3" style="width: 24px;"><i class="bi bi-tag text-secondary fs-5"></i></div>
                                    <div class="align-self-center">
                                        <span class="badge ${props.status === 'completed' ? 'bg-success' : (props.status === 'cancelled' ? 'bg-danger' : 'bg-primary')} rounded-pill px-3 fw-normal">
                                            ${{
                                                'scheduled': 'Programada',
                                                'completed': 'Completada',
                                                'cancelled': 'Cancelada'
                                            }[props.status] || props.status}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                ${actionButtons}
                            </div>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCloseButton: false, // Custom close button used
                    background: 'transparent', // Let container handle background
                    customClass: {
                        popup: 'rounded-4 border-0 p-0 shadow-lg' // Remove default Swal padding
                    },
                    width: '450px'
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
    .fc-event {
        border: none !important;
        border-radius: 6px !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        padding: 1px 4px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: transform 0.1s, box-shadow 0.1s;
    }
    .fc-event:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        z-index: 50;
    }
    /* TimeGrid specific */
    .fc-timegrid-event {
        border-radius: 6px !important;
    }
    .fc-event-main {
        color: white; /* Ensure text matches contrast, usually handled by server color but safe fallback */
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
