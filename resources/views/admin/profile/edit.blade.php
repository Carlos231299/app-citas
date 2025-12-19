@extends('layouts.admin')

@section('title', 'Mi Perfil - Admin')
@section('header', 'Mi Perfil')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card bg-white border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4">
                            <ul class="mb-0 small fw-bold">
                                @foreach ($errors->all() as $error)
                                    <li><i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h5 class="fw-bold text-primary mb-4">Información Personal</h5>
                    
                    <!-- Avatar Selection -->
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold mb-3">SELECCIONAR AVATAR</label>
                        <input type="hidden" name="avatar" id="avatar_input" value="{{ old('avatar', $user->avatar ?? 'person') }}">
                        <div class="d-flex flex-wrap gap-3">
                            @php
                                $avatars = ['👨‍💼', '👩‍💼', '🧔', '👱‍♀️', '👨‍🦱', '👩‍🦱', '🦁', '🦊'];
                            @endphp
                            @foreach($avatars as $av)
                                <div class="avatar-option rounded-circle border d-flex align-items-center justify-content-center cursor-pointer shadow-sm position-relative {{ (old('avatar', $user->avatar ?? '👨‍💼') == $av) ? 'active-avatar border-primary bg-primary bg-opacity-10' : 'bg-white' }}" 
                                     style="width: 50px; height: 50px; transition: all 0.2s; font-size: 1.5rem;"
                                     onclick="selectAvatar('{{ $av }}', this)">
                                    {{ $av }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE DE USUARIO (LOGIN)</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required {{ $user->id === 1 ? 'readonly' : '' }}>
                        @if($user->id === 1) <small class="text-muted">El usuario admin no puede cambiarse.</small> @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CORREO ELECTRÓNICO</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    @if($user->barber)
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NÚMERO DE WHATSAPP (Con código país, ej: +57...)</label>
                        <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $user->barber->whatsapp_number) }}" placeholder="+573001234567">
                        <small class="text-muted">Necesario para recibir códigos de verificación cuando inicies sesión desde dispositivos nuevos.</small>
                    </div>
                    @endif

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-save me-2"></i> GUARDAR CAMBIOS
                        </button>
                    </div>

                <!-- End Personal Info Section -->

                <!-- SECCIÓN DE DISPONIBILIDAD (AJAX - Estilo Admin) -->
                @if($user->barber)
                <div class="mt-5 pt-4 border-top">
                    <h5 class="fw-bold mb-3 text-dark">Gestionar Disponibilidad</h5>
                    <div class="card bg-white border-0 shadow-sm">
                        <div class="card-body p-4">
                            
                            <!-- Active Switch -->
                            <div class="form-check form-switch mb-4 d-flex align-items-center">
                                <input class="form-check-input me-3" type="checkbox" role="switch" id="active_switch"
                                    onchange="handleStatusChange(this)" {{ $user->barber->is_active ? 'checked' : '' }} style="transform: scale(1.4);">
                                
                                <div class="d-flex flex-column">
                                    <label class="form-check-label fw-bold {{ $user->barber->is_active ? 'text-success' : 'text-muted' }}" 
                                        id="active_label" for="active_switch">
                                        {{ $user->barber->is_active ? 'DISPONIBLE PARA RESERVAS' : 'NO DISPONIBLE (INACTIVO)' }}
                                    </label>
                                    @if($user->barber->unavailable_start && $user->barber->unavailable_end)
                                        <small class="text-muted">Hasta: {{ $user->barber->unavailable_end->format('d M, h:i A') }}</small>
                                    @endif
                                </div>
                            </div>

                            <!-- Extra Time Switch -->
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input me-3 bg-warning border-warning" type="checkbox" role="switch" id="extra_switch"
                                    onchange="handleExtraTimeChange(this)" {{ $user->barber->special_mode ? 'checked' : '' }} style="transform: scale(1.4);">
                                
                                <div class="d-flex flex-column">
                                    <label class="form-check-label fw-bold text-warning" for="extra_switch">
                                        <i class="bi bi-moon-stars-fill"></i> HABILITAR HORARIO EXTRA
                                    </label>
                                    <small class="text-muted">Permite citas 4AM-8AM y 6PM-10PM</small>
                                    @if($user->barber->special_mode && $user->barber->extra_time_start)
                                        <small class="text-dark fw-bold mt-1">
                                            {{ \Carbon\Carbon::parse($user->barber->extra_time_start)->format('d/m') }} - {{ \Carbon\Carbon::parse($user->barber->extra_time_end)->format('d/m') }}
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <!-- Lunch Break Switch -->
                            <div class="form-check form-switch d-flex align-items-center mt-4 border-top pt-3">
                                <input class="form-check-input me-3 bg-success border-success" type="checkbox" role="switch" id="lunch_switch"
                                    onchange="sendUpdate({ work_during_lunch: this.checked ? 1 : 0 }, this)" {{ $user->barber->work_during_lunch ? 'checked' : '' }} style="transform: scale(1.4);">
                                
                                <div class="d-flex flex-column">
                                    <label class="form-check-label fw-bold text-success" for="lunch_switch">
                                        <i class="bi bi-cup-hot-fill"></i> TRABAJAR EN HORA DE ALMUERZO
                                    </label>
                                    <small class="text-muted">Habilita citas de 12:00 PM a 1:00 PM (Mediodía).</small>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                @push('scripts')
                <script>
                    // Logic Adapted from Admin/Barbers/Index
                    
                    // Handle Extra Time Status Change
                    function handleExtraTimeChange(switchEl) {
                        const isTurningOn = switchEl.checked;
                        
                        if (isTurningOn) {
                            // Turning ON: Ask for Dates
                            switchEl.checked = false; // Visually revert until confirmed
                            
                            // Fix Timezone: Use format YYYY-MM-DD based on local time
                            const now = new Date();
                            const today = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().split('T')[0];

                            Swal.fire({
                                title: 'Horario Extra',
                                html: `
                                    <p class="small text-muted mb-3">Define los días que trabajarás horario extendido.</p>
                                    <div class="text-start">
                                        <label class="form-label small fw-bold">Desde</label>
                                        <input type="date" id="swal_extra_start" class="form-control mb-2" min="${today}">
                                        <label class="form-label small fw-bold">Hasta</label>
                                        <input type="date" id="swal_extra_end" class="form-control" min="${today}">
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
                                    sendUpdate({ 
                                        special_mode: true, 
                                        extra_time_start: res.value.start, 
                                        extra_time_end: res.value.end 
                                    }, switchEl, true); // true = force checked if success
                                }
                            });
                        } else {
                            // Turning OFF
                            sendUpdate({ special_mode: false }, switchEl);
                        }
                    }

                    // Handle Status Toggle (Inactive/Active)
                    function handleStatusChange(switchEl) {
                        const isTurningOn = switchEl.checked;
                        
                        // Timezone fix for DateTime inputs
                        const now = new Date();
                        const nowString = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().slice(0,16);

                        // If Turning ON: Just do it (clears unavailability)
                        if (isTurningOn) {
                            sendUpdate({ is_active: 1, unavailable_start: null, unavailable_end: null }, switchEl);
                            return;
                        }

                        // If Turning OFF: Ask Type
                        switchEl.checked = true; // Revert visually while asking

                        Swal.fire({
                            title: 'Desactivar Perfil',
                            text: `¿Deseas desactivarte permanentemente o temporalmente?`,
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
                                sendUpdate({ is_active: 0, unavailable_start: null, unavailable_end: null }, switchEl);
                            } else if (result.isConfirmed) {
                                // Temporary -> Ask Dates
                                askTemporaryRange(switchEl, nowString);
                            }
                        });
                    }

                    function askTemporaryRange(switchEl, nowString) {
                        Swal.fire({
                            title: 'Inactividad Temporal',
                            html: `
                                <p class="small text-muted mb-3">Define cuándo volverás a estar disponible.</p>
                                <div class="text-start">
                                    <label class="form-label small fw-bold">Desde (Inicio Inactividad)</label>
                                    <input type="datetime-local" id="swal_temp_start" class="form-control mb-2" 
                                           min="${nowString}" value="${nowString}">
                                    
                                    <label class="form-label small fw-bold">Hasta (Regreso)</label>
                                    <input type="datetime-local" id="swal_temp_end" class="form-control" 
                                           min="${nowString}">
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
                                // Send Update: KEEP is_active = 1, but set dates (as per Admin logic preference)
                                sendUpdate({ 
                                    is_active: 1, 
                                    unavailable_start: res.value.start, 
                                    unavailable_end: res.value.end 
                                }, switchEl, true);
                            }
                        });
                    }

                    function sendUpdate(payload, switchEl, forceCheck = false) {
                        axios.put('{{ route("profile.updateStatus") }}', { ...payload, _token: '{{ csrf_token() }}' })
                            .then((response) => {
                                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
                                Toast.fire({ icon: 'success', title: 'Estado actualizado' });
                                
                                if(forceCheck) {
                                    switchEl.checked = true;
                                }

                                // Update Labels Visually if needed (or reload)
                                // Reloading is safest to sync server state labels/colors
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
                </script>
                @endpush
                @endif

                    <h5 class="fw-bold text-primary mb-4">Seguridad</h5>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CONTRASEÑA ACTUAL (Solo si desea cambiarla)</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">NUEVA CONTRASEÑA</label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" autocomplete="new-password">
                                <button class="btn btn-outline-secondary border-start-0 text-secondary" type="button" id="toggleNewPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('new_password')
                                    <span class="invalid-feedback d-block ps-2"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <!-- Checklist -->
                            <div class="mt-2">
                                <small class="text-secondary fw-bold">Requisitos:</small>
                                <ul class="list-unstyled mb-0 small ps-1" id="profile-password-requirements">
                                    <li data-req="length" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Mínimo 8 caracteres</li>
                                    <li data-req="upper" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Una mayúscula</li>
                                    <li data-req="lower" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Una minúscula</li>
                                    <li data-req="number" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Un número</li>
                                    <li data-req="special" class="text-muted"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Un símbolo (@$!%*?&.)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">CONFIRMAR NUEVA CONTRASEÑA</label>
                            <div class="input-group">
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" autocomplete="new-password">
                                <button class="btn btn-outline-secondary border-start-0 text-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4 fw-bold">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bi bi-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function selectAvatar(avatar, element) {
        document.getElementById('avatar_input').value = avatar;
        
        // Reset classes
        document.querySelectorAll('.avatar-option').forEach(el => {
            el.className = 'avatar-option rounded-circle border d-flex align-items-center justify-content-center cursor-pointer shadow-sm position-relative bg-white';
        });

        // Set Active class
        element.className = 'avatar-option rounded-circle border d-flex align-items-center justify-content-center cursor-pointer shadow-sm position-relative active-avatar border-primary bg-primary bg-opacity-10';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('new_password');
        const confirmPassword = document.getElementById('new_password_confirmation');
        const togglePass = document.getElementById('toggleNewPassword');
        const toggleConfirm = document.getElementById('toggleConfirmPassword');
        const checklist = document.getElementById('profile-password-requirements');

        // Toggle Function
        function toggleVisibility(input, button) {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            const icon = button.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }

        if(togglePass) togglePass.addEventListener('click', () => toggleVisibility(password, togglePass));
        if(toggleConfirm) toggleConfirm.addEventListener('click', () => toggleVisibility(confirmPassword, toggleConfirm));

        // Validation Logic
        if(password && checklist) {
            password.addEventListener('input', function() {
                const val = password.value;
                if(val.length === 0) {
                     // Reset to gray if empty
                     checklist.querySelectorAll('li').forEach(li => updateItem(li, false, true));
                     return;
                }
                
                checkReq(checklist, 'length', val.length >= 8);
                checkReq(checklist, 'upper', /[A-Z]/.test(val));
                checkReq(checklist, 'lower', /[a-z]/.test(val));
                checkReq(checklist, 'number', /[0-9]/.test(val));
                checkReq(checklist, 'special', /[@$!%*?&.]/.test(val));
            });
        }

        function checkReq(list, type, isValid) {
            const li = list.querySelector(`[data-req="${type}"]`);
            if(li) updateItem(li, isValid);
        }

        function updateItem(element, isValid, reset = false) {
            const icon = element.querySelector('i');
            if (reset) {
                element.className = 'text-muted';
                icon.className = 'bi bi-circle me-1';
                return;
            }

            if (isValid) {
                element.className = 'text-success text-decoration-line-through fw-bold';
                icon.className = 'bi bi-check-circle-fill me-1';
            } else {
                element.className = 'text-muted';
                icon.className = 'bi bi-circle me-1';
            }
        }
    });
</script>
            </div>
        </div>
    </div>
</div>
@endsection
