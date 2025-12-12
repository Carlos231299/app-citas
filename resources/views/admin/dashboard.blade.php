@extends('layouts.admin')

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
            <!-- Custom View Selector -->
            <div id="custom-view-selector" class="d-none">
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
                                <span>Mostrar fines de semana</span>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-item d-flex gap-2 align-items-center rounded-2 py-2" onclick="toggleOption(event, 'rejected')">
                                <i class="bi bi-check-lg text-primary" id="check-rejected"></i>
                                <span>Mostrar eventos rechazados</span>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-item d-flex gap-2 align-items-center rounded-2 py-2" onclick="toggleOption(event, 'completed')">
                                <i class="bi bi-check-lg text-primary" id="check-completed"></i>
                                <span>Mostrar tareas completadas</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div id="calendar" class="h-100"></div>
        </div>
    </div>
    <!-- Rendered: {{ now() }} -->
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
            nowIndicator: true, 
            
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
                let actionButtons = '';
                if (props.type === 'appointment' && props.status === 'scheduled') {
                    actionButtons = `
                        <div class="d-flex justify-content-center gap-2 mt-4">
                            <button onclick="editAppointment(${event.id})" class="btn btn-primary px-4">
                                <i class="bi bi-pencil-fill me-1"></i> Editar
                            </button>
                            <button onclick="cancelAppointment(${event.id})" class="btn btn-danger px-4">
                                <i class="bi bi-trash-fill me-1"></i> Cancelar
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
                            <p class="mb-2"><strong><i class="bi bi-info-circle me-2"></i>Estado:</strong> ${statusBadge}</p>
                            ${props.custom_details ? `<p class="mb-0 text-muted small mt-3"><em>${props.custom_details}</em></p>` : ''}
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

    // Edit Appointment Logic
    window.editAppointment = function(id) {
        // Close the detail modal first
        Swal.close();

        const event = calendarInstance.getEventById(id);
        if (!event) return;

        const props = event.extendedProps;
        const currentServiceId = props.service_id;
        const currentBarberId = props.barber_id;
        
        // Build Options
        const serviceOptions = serverData.services.map(s => 
            `<option value="${s.id}" ${s.id == currentServiceId ? 'selected' : ''}>${s.name} ($${s.price})</option>`
        ).join('');

        const barberOptions = serverData.barbers.map(b => 
            `<option value="${b.id}" ${b.id == currentBarberId ? 'selected' : ''}>${b.name}</option>`
        ).join('');

        Swal.fire({
            title: 'Editar Cita',
            html: `
                <div class="text-start">
                    <label class="form-label">Fecha</label>
                    <input type="date" id="edit-date" class="form-control mb-3" value="${event.start.toISOString().split('T')[0]}">
                    
                    <label class="form-label">Hora</label>
                    <input type="time" id="edit-time" class="form-control mb-3" value="${event.start.toTimeString().substr(0,5)}">
                    
                    <label class="form-label">Servicio</label>
                    <select id="edit-service" class="form-select mb-3">
                        ${serviceOptions}
                    </select>
                    
                    <label class="form-label">Barbero</label>
                    <select id="edit-barber" class="form-select mb-3">
                        ${barberOptions}
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar Cambios',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                return {
                    date: document.getElementById('edit-date').value,
                    time: document.getElementById('edit-time').value,
                    service_id: document.getElementById('edit-service').value,
                    barber_id: document.getElementById('edit-barber').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
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

    /* NOW INDICATOR STYLING */
    .fc-now-indicator-line {
        border-color: #EA4335 !important; /* Google Red */
        border-width: 2px !important;
        box-shadow: 0 0 4px rgba(234, 67, 53, 0.4);
        z-index: 99 !important;
    }
    .fc-now-indicator-arrow {
        border-color: #EA4335 !important;
        border-width: 6px 0 6px 8px !important; /* Make it bigger */
        margin-top: -6px !important; /* Re-center */
        z-index: 99 !important;
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
</style>
@endpush
