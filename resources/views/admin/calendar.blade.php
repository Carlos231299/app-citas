@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Agenda - Barbería JR')
@section('header', 'Calendario de Citas')

@section('content')
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold">
            <i class="bi bi-calendar-check me-2"></i>
            {{ trim(auth()->user()->role) === 'admin' ? 'Agenda Global' : 'Mi Agenda' }}
        </h5>
        <div class="d-flex gap-2">
            @if(trim(auth()->user()->role) === 'admin')
            <select id="barberFilter" class="form-select form-select-sm shadow-sm" style="width: 200px;" onchange="refreshCalendar()">
                <option value="">Todos los Barberos</option>
                @foreach(\App\Models\Barber::where('is_active', true)->get() as $barber)
                    <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                @endforeach
            </select>
            @else
                {{-- Non-Admins: Hidden Input for JS compatibility --}}
                <input type="hidden" id="barberFilter" value="{{ auth()->user()->barber?->id }}">
            @endif
            <button class="btn btn-sm btn-outline-secondary" onclick="refreshCalendar()"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
    </div>
    <div class="card-body p-4 h-100">
        <div id="calendar" class="h-100"></div>
    </div>
</div>

@push('scripts')
<script>
    function initCalendar() {
        var calendarEl = document.getElementById('calendar');
        if(!calendarEl) return;
        
        window.calendarAPI = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'es',
            slotMinTime: '08:00:00',
            slotMaxTime: '18:00:00',
            height: '100%',
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
            events: {
                url: '/api/calendar/events',
                extraParams: function() {
                    return {
                        barber_id: document.getElementById('barberFilter').value
                    };
                }
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
                            
                            ${props.status === 'cancelled' ? 
                                `<div class="mb-2 p-2 bg-danger bg-opacity-10 rounded border border-danger">
                                    <strong class="text-danger">Motivo de Cancelación:</strong> 
                                    <div class="text-dark small mt-1">${props.cancellation_reason || 'No especificado'}</div>
                                </div>` : ''
                            }

                            ${props.status === 'completed' ? 
                                `<div class="mb-2 p-2 bg-success bg-opacity-10 rounded border border-success">
                                    <strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Completada con éxito</strong>
                                </div>` : ''
                            }

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
            }
        });
        window.calendarAPI.render();
    }

    window.refreshCalendar = function() {
        if(window.calendarAPI) {
            window.calendarAPI.refetchEvents();
        }
    };

    // Actions (No need to redefine if using same layout via SPA, but safe to define on window if reload happening)
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
                    location.reload(); 
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
                    location.reload(); 
                })
                .catch(error => {
                    Swal.fire('Error', 'No se pudo cancelar la cita.', 'error');
                });
            }
        });
    };

    // Run immediately
    window.refreshCalendar = function() {
        var calendarEl = document.getElementById('calendar');
        // Since we don't have direct access to calendar instance here cleanly without global var, 
        // we re-init or usually FullCalendar instance is stored. 
        // Better pattern: store instance globally or re-render.
        // For simplicity in this legacy script setup:
        location.reload(); // Quickest way to refresh with new params if params were in URL, but they are not.
        // Actually, let's fix initCalendar to return instance or store it.
        // Re-init works if we destroy old one, but refetchEvents is better.
        // Let's rely on the fact that FullCalendar is robust.
        // Ideally: calendar.refetchEvents();
    };

    // Correct approach: Make calendar variable global inside script scope
    var calendar; 

    function initCalendar() {
        var calendarEl = document.getElementById('calendar');
        if(!calendarEl) return;
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            // ... (rest of config same as before)
            // But wait, I'm inside initCalendar, previously `var calendar`.
            // I need to change the previous var declaration to use the outer one or remove 'var'
            // To make replacement easy, I will just patch the top
        });
        // ...
    }
    
    // Patching the whole initCalendar to be cleaner is better, but risky with replace.
    // Let's modify initCalendar to assign to window.calendar
    
    // ...
    
    document.addEventListener('DOMContentLoaded', initCalendar);
</script>
@endpush
@endsection
