@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Agenda - Barbería JR')
@section('header', 'Calendario de Citas')

@section('content')
<div class="card border-0 shadow-sm h-100">
    <div class="card-body p-4 h-100">
        <div id="calendar" class="h-100"></div>
    </div>
</div>

@push('scripts')
<script>
    function initCalendar() {
        var calendarEl = document.getElementById('calendar');
        if(!calendarEl) return;
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
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
            events: '/api/calendar/events',
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
            }
        });
        calendar.render();
    }

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
    initCalendar();
    document.addEventListener('DOMContentLoaded', initCalendar);
</script>
@endpush
@endsection
