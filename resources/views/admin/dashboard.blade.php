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
                const props = info.event.extendedProps;
                // Simple Alert for now to verify interactions
                Swal.fire({
                    title: info.event.title,
                    text: `${props.service} - ${props.barber}`,
                    icon: 'info'
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


    document.addEventListener('DOMContentLoaded', initCalendar);
</script>
<style>
    /* Minimal Styles for functionality first */
    .fc-toolbar-title { cursor: pointer; }
    .fc-toolbar-title:hover { background-color: #f1f5f9; border-radius: 4px; }
    .fc-event { cursor: pointer; }
    
    /* Agenda View Fix */
    .fc-list-event { cursor: pointer; }
    .fc-list-event:hover td { background-color: #f8f9fa !important; }
    .fc-list-event-title, .fc-list-event-time { color: #1e293b !important; }
    .fc-list-table { color: #1e293b !important; }
</style>
@endpush
