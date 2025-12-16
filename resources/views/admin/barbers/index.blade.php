@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Barberos - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 text-dark">
    <h2 class="fw-bold m-0" style="color: #333;">Barberos</h2>
    <div class="d-flex gap-2">
        @if(trim(auth()->user()->role) === 'admin')
        <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#createBarberModalNew">
            <i class="bi bi-person-plus-fill"></i> Nuevo Barbero
        </button>
        @endif
    </div>
</div>

<div class="row g-4 mb-5">
    @foreach($barbers as $barber)
    @php
        // Determine status display
        $isTemporaryInactive = $barber->unavailable_start && $barber->unavailable_end && now()->between($barber->unavailable_start, $barber->unavailable_end);
        $statusLabel = $barber->is_active ? ($isTemporaryInactive ? 'Temporalmente Inactivo' : 'Activo') : 'Inactivo';
        $statusColor = $barber->is_active ? ($isTemporaryInactive ? 'text-warning' : 'text-success') : 'text-muted';
        $switchChecked = $barber->is_active; 
    @endphp
    <div class="col-md-6 col-lg-4">
        <div class="card bg-white border-0 shadow-sm h-100 hover-shadow transition-all">
            <div class="card-body d-flex align-items-center p-4">
                <div class="me-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold display-6 shadow-sm border" style="width: 64px; height: 64px;">
                        {{ substr($barber->name, 0, 1) }}
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="text-dark fw-bold mb-1">{{ $barber->name }}</h5>
                    <div class="text-secondary small mb-2 d-flex align-items-center gap-1">
                        <i class="bi bi-whatsapp text-success"></i> <span class="fw-medium">{{ $barber->whatsapp_number ?? 'Sin número' }}</span>
                    </div>
                    
                <div class="mt-2">
                        <!-- Active Switch -->
                        <div class="form-check form-switch mb-2 d-flex align-items-center">
                            <input class="form-check-input me-2" type="checkbox" role="switch" id="active_switch_{{ $barber->id }}"
                                onchange="handleStatusChange({{ $barber->id }}, '{{ addslashes($barber->name) }}', this)" {{ $barber->is_active ? 'checked' : '' }}>
                            
                            <div class="d-flex flex-column">
                                <label class="form-check-label small pt-1 fw-bold {{ $statusColor }}" 
                                    id="active_label_{{ $barber->id }}" for="active_switch_{{ $barber->id }}">
                                    {{ $statusLabel }}
                                </label>
                                @if($isTemporaryInactive)
                                    <small class="text-muted" style="font-size: 0.7rem;">Hasta: {{ $barber->unavailable_end->format('d M, h:i A') }}</small>
                                @endif
                            </div>
                        </div>

                        <!-- Extra Time Switch (Restored to Card) -->
                        <div class="form-check form-switch mb-2 d-flex align-items-center">
                            <input class="form-check-input me-2 bg-warning border-warning" type="checkbox" role="switch" id="extra_switch_{{ $barber->id }}"
                                onchange="handleExtraTimeChange({{ $barber->id }}, '{{ addslashes($barber->name) }}', this)" {{ $barber->special_mode ? 'checked' : '' }}>
                            
                            <div class="d-flex flex-column">
                                <label class="form-check-label small pt-1 fw-bold text-warning" for="extra_switch_{{ $barber->id }}">
                                    <i class="bi bi-moon-stars-fill"></i> Horario Extra
                                </label>
                                @if($barber->special_mode && $barber->extra_time_start)
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        {{ \Carbon\Carbon::parse($barber->extra_time_start)->format('d/m') }} - {{ \Carbon\Carbon::parse($barber->extra_time_end)->format('d/m') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dropdown align-self-start">
                    <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu border-0 shadow-sm">
                        <li>
                            <button class="dropdown-item py-2" type="button" onclick="editBarber({{ $barber->id }}, '{{ addslashes($barber->name) }}', '{{ $barber->whatsapp_number }}', '{{ $barber->user?->email }}', '{{ $barber->user?->username }}')"><i class="bi bi-pencil me-2 text-warning"></i> Editar</button>
                        </li>
                        @if(trim(auth()->user()->role) === 'admin')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('barbers.destroy', $barber) }}" method="POST" onsubmit="confirmDelete(event, '{{ addslashes($barber->name) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> Eliminar</button>
                            </form>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Create Modal (Renamed) -->
<div class="modal fade" id="createBarberModalNew" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Nuevo Barbero</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('barbers.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">WHATSAPP (Opcional)</label>
                        <input type="tel" name="whatsapp_number" class="form-control" placeholder="+57300..." 
                               oninput="this.value = this.value.replace(/[^0-9+]/g, '')">
                    </div>

                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4">Cuenta de Acceso</h6>
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i> Se creará un usuario para que el barbero pueda ver su agenda.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">USUARIO DE ACCESO</label>
                        <input type="text" name="username" class="form-control" required placeholder="Ej: juan.perez" pattern="[a-zA-Z0-9\._\-]+" title="Solo letras, números, puntos, guiones y guiones bajos.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CORREO ELECTRÓNICO</label>
                        <input type="email" name="email" class="form-control" required placeholder="usuario@barberia.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CONTRASEÑA</label>
                        <div class="input-group">
                            <input type="password" name="password" id="create_password" class="form-control" required minlength="8" placeholder="Mínimo 8 caracteres">
                            <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('create_password').type = document.getElementById('create_password').type == 'password' ? 'text' : 'password'">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gold px-4 fw-bold">Guardar Barbero</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal (Simplified - No Extra Time Inputs) -->
<div class="modal fade" id="editBarberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Editar Barbero</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" autocomplete="off" onsubmit="confirmUpdate(event)">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">WHATSAPP</label>
                        <input type="tel" name="whatsapp_number" id="edit_whatsapp" class="form-control" autocomplete="off"
                               oninput="this.value = this.value.replace(/[^0-9+]/g, '')" pattern="[0-9+]*">
                    </div>
                    
                    <!-- REMOVED EXTRA TIME INPUTS (Moved to Card Switch) -->
                    
                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4">Cuenta de Usuario</h6>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">USUARIO DE ACCESO</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required placeholder="juan.perez" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CORREO ELECTRÓNICO (RECUPERACIÓN)</label>
                        <input type="email" name="email" id="edit_email" class="form-control" placeholder="usuario@barberia.com" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NUEVA CONTRASEÑA (OPCIONAL)</label>
                        <input type="text" name="password" id="edit_password" class="form-control" placeholder="Dejar en blanco para mantener actual" minlength="8" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" id="btnUpdateBarber" class="btn btn-gold px-4 fw-bold" disabled>Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editModal;
    document.addEventListener('DOMContentLoaded', () => {
        editModal = new bootstrap.Modal(document.getElementById('editBarberModal'));
        
        // Dirty Check Logic
        const nameInput = document.getElementById('edit_name');
        const whatsappInput = document.getElementById('edit_whatsapp');
        const emailInput = document.getElementById('edit_email');
        const usernameInput = document.getElementById('edit_username');
        const passInput = document.getElementById('edit_password');
        const btnUpdate = document.getElementById('btnUpdateBarber');

        function checkChanges() {
             btnUpdate.disabled = false;
        }

        [nameInput, whatsappInput, emailInput, usernameInput, passInput].forEach(el => el.addEventListener('input', checkChanges));
        
        // Edit Function - Adjusted arguments
        window.editBarber = function(id, name, whatsapp, email, username) {
            document.getElementById('editForm').action = `/barbers/${id}`;
            nameInput.value = name;
            whatsappInput.value = whatsapp || '';
            document.getElementById('edit_email').value = email || '';
            document.getElementById('edit_username').value = username || '';
            document.getElementById('edit_password').value = ''; 
            
            btnUpdate.disabled = true; 
            editModal.show();
        };
    });

    // Handle Extra Time Status Change
    function handleExtraTimeChange(id, name, switchEl) {
        const isTurningOn = switchEl.checked;
        
        if (isTurningOn) {
            // Turning ON: Ask for Dates
            switchEl.checked = false; // Visually revert until confirmed
            Swal.fire({
                title: 'Horario Extra',
                html: `
                    <p class="small text-muted mb-3">Define los días que ${name} trabajará horario extendido.</p>
                    <div class="text-start">
                        <label class="form-label small fw-bold">Desde</label>
                        <input type="date" id="swal_extra_start" class="form-control mb-2" min="{{ date('Y-m-d') }}">
                        <label class="form-label small fw-bold">Hasta</label>
                        <input type="date" id="swal_extra_end" class="form-control" min="{{ date('Y-m-d') }}">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Activar',
                didOpen: () => {
                    const startEl = document.getElementById('swal_extra_start');
                    const endEl = document.getElementById('swal_extra_end');
                    
                    // Enforce Logic: End >= Start
                    startEl.addEventListener('change', () => {
                         endEl.min = startEl.value;
                         if(endEl.value && endEl.value < startEl.value) {
                             endEl.value = startEl.value;
                         }
                    });
                },
                preConfirm: () => {
                    const start = document.getElementById('swal_extra_start').value;
                    const end = document.getElementById('swal_extra_end').value;
                    if(!start || !end) {
                         Swal.showValidationMessage('Ambas fechas son requeridas');
                         return false;
                    }
                    if(end < start) {
                         Swal.showValidationMessage('La fecha final no puede ser antes de la inicial');
                         return false;
                    }
                    return { start, end };
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    sendUpdate(id, { 
                        special_mode: true, 
                        extra_time_start: res.value.start, 
                        extra_time_end: res.value.end 
                    }, switchEl, true); // true = force checked if success
                }
            });
        } else {
            // Turning OFF
            sendUpdate(id, { special_mode: false }, switchEl);
        }
    }

    // Handle Status Toggle (Inactive/Active)
    function handleStatusChange(id, name, switchEl) {
         // ... existing logic ...
        const isTurningOn = switchEl.checked;
        
        // If Turning ON: Just do it (clears unavailability)
        if (isTurningOn) {
            sendUpdate(id, { is_active: 1, unavailable_start: null, unavailable_end: null }, switchEl);
            return;
        }

        // If Turning OFF: Ask Type
        switchEl.checked = true; // Revert visually while asking

        Swal.fire({
            title: 'Desactivar Barbero',
            text: `¿Deseas desactivar a ${name} permanentemente o temporalmente?`,
            icon: 'question',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Temporalmente',
            denyButtonText: 'Indefinidamente',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ffc107', // Warning/Temp
            denyButtonColor: '#EF4444' // Danger/Perm
        }).then((result) => {
            if (result.isDenied) {
                // Indefinite
                switchEl.checked = false; // Visually apply
                sendUpdate(id, { is_active: 0, unavailable_start: null, unavailable_end: null }, switchEl);
            } else if (result.isConfirmed) {
                // Temporary -> Ask Dates
                askTemporaryRange(id, name, switchEl);
            }
        });
    }

    function askTemporaryRange(id, name, switchEl) {
        Swal.fire({
            title: 'Inactividad Temporal',
            html: `
                <p class="small text-muted mb-3">Define cuándo volverá a estar disponible ${name}.</p>
                <div class="text-start">
                    <label class="form-label small fw-bold">Desde (Inicio Inactividad)</label>
                    <input type="datetime-local" id="swal_temp_start" class="form-control mb-2" 
                           min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ now()->format('Y-m-d\TH:i') }}">
                    
                    <label class="form-label small fw-bold">Hasta (Regreso)</label>
                    <input type="datetime-local" id="swal_temp_end" class="form-control" 
                           min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            didOpen: () => {
                const startEl = document.getElementById('swal_temp_start');
                const endEl = document.getElementById('swal_temp_end');
                
                // Enforce Logic: End >= Start
                startEl.addEventListener('change', () => {
                     endEl.min = startEl.value;
                     if(endEl.value && endEl.value < startEl.value) {
                         endEl.value = startEl.value;
                     }
                });
            },
            preConfirm: () => {
                const start = document.getElementById('swal_temp_start').value;
                const end = document.getElementById('swal_temp_end').value;
                
                if(!start || !end) {
                     Swal.showValidationMessage('Ambas fechas son requeridas');
                     return false;
                }
                if(end < start) {
                     Swal.showValidationMessage('La fecha de regreso no puede ser anterior al inicio');
                     return false;
                }
                return { start, end };
            }
        }).then((res) => {
            if (res.isConfirmed) {
                // Send Update: KEEP is_active = 1, but set dates
                sendUpdate(id, { 
                    is_active: 1, 
                    unavailable_start: res.value.start, 
                    unavailable_end: res.value.end 
                }, switchEl, true);
            }
        });
    }



    function sendUpdate(id, payload, switchEl) {
        axios.put(`/barbers/${id}`, { ...payload, _token: '{{ csrf_token() }}' })
            .then(() => {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
                Toast.fire({ icon: 'success', title: 'Estado actualizado' });
                // Reload to reflect Badge/Label changes accurately
                setTimeout(() => location.reload(), 1000);
            })
            .catch((error) => {
                switchEl.checked = !switchEl.checked; // Revert
                console.error(error);
                let msg = 'No se pudo actualizar';
                if (error.response && error.response.data && error.response.data.message) {
                    msg = error.response.data.message;
                }
                Swal.fire('Error', msg, 'error');
            });
    }

    // Confirmation Logic for Delete/Update Forms
    function confirmDelete(e, name) {
        e.preventDefault();
        Swal.fire({
            title: '¿Eliminar Barbero?',
            text: `Se eliminará a ${name} y su acceso.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Sí, eliminar'
        }).then((r) => { if(r.isConfirmed) e.target.submit(); });
    }

    function confirmUpdate(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Guardar Cambios?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí'
        }).then((r) => { if(r.isConfirmed) e.target.submit(); });
    }
</script>
@endpush
@endsection
