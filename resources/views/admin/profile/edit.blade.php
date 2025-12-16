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

                    @if($user->barber)
                    <hr class="my-4">
                    <h5 class="fw-bold text-primary mb-4">Mi Estado y Disponibilidad</h5>

                    <!-- Status Switch -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0">
                            <label class="form-check-label fw-bold text-dark" for="is_active">
                                <i class="bi bi-circle-fill text-success me-2" id="status-icon"></i>
                                <span id="status-text">DISPONIBLE PARA RESERVAS</span>
                            </label>
                            <input class="form-check-input ms-auto" type="checkbox" id="is_active" name="is_active" value="1" {{ $user->barber->is_active ? 'checked' : '' }} style="transform: scale(1.4);">
                        </div>
                        <div id="unavailability-section" class="mt-3 {{ $user->barber->is_active ? 'd-none' : '' }}">
                            <small class="text-muted d-block mb-2">Define el periodo de ausencia (opcional):</small>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small fw-bold text-secondary">Desde</label>
                                    <input type="date" name="unavailable_start" class="form-control form-control-sm" value="{{ $user->barber->unavailable_start?->format('Y-m-d') }}">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold text-secondary">Hasta</label>
                                    <input type="date" name="unavailable_end" class="form-control form-control-sm" value="{{ $user->barber->unavailable_end?->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Extra Time Switch -->
                    <div class="mb-4 p-3 bg-warning bg-opacity-10 rounded-3 border border-warning">
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0">
                            <label class="form-check-label fw-bold text-dark" for="special_mode">
                                <i class="bi bi-moon-stars-fill text-warning me-2"></i>
                                <span>HABILITAR HORARIO EXTRA</span>
                            </label>
                            <input class="form-check-input ms-auto" type="checkbox" id="special_mode" name="special_mode" value="1" {{ $user->barber->special_mode ? 'checked' : '' }} style="transform: scale(1.4);">
                        </div>
                        <div id="extra-time-section" class="mt-3 {{ $user->barber->special_mode ? '' : 'd-none' }}">
                            <small class="text-dark d-block mb-2">Permite citas <strong>4AM-8AM</strong> y <strong>6PM-10PM</strong> en estas fechas:</small>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small fw-bold text-secondary">Desde</label>
                                    <input type="date" name="extra_time_start" class="form-control form-control-sm" value="{{ $user->barber->extra_time_start }}">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold text-secondary">Hasta</label>
                                    <input type="date" name="extra_time_end" class="form-control form-control-sm" value="{{ $user->barber->extra_time_end }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const activeSwitch = document.getElementById('is_active');
                            const unavailableSection = document.getElementById('unavailability-section');
                            const statusText = document.getElementById('status-text');
                            const statusIcon = document.getElementById('status-icon');
                            
                            const specialSwitch = document.getElementById('special_mode');
                            const extraSection = document.getElementById('extra-time-section');

                            activeSwitch.addEventListener('change', function() {
                                if(this.checked) {
                                    unavailableSection.classList.add('d-none');
                                    statusText.textContent = 'DISPONIBLE PARA RESERVAS';
                                    statusText.classList.remove('text-muted');
                                    statusIcon.className = 'bi bi-circle-fill text-success me-2';
                                } else {
                                    unavailableSection.classList.remove('d-none');
                                    statusText.textContent = 'NO DISPONIBLE (INACTIVO)';
                                    statusText.classList.add('text-muted');
                                    statusIcon.className = 'bi bi-circle-fill text-secondary me-2';
                                    
                                    // Logic: If inactive, warn users
                                    Swal.fire({
                                        title: '¿Pasar a Inactivo?',
                                        text: 'No aparecerás en la lista de reservas (excepto Horario Extra si está activo).',
                                        icon: 'warning',
                                        confirmButtonText: 'Entendido'
                                    });
                                }
                            });

                            specialSwitch.addEventListener('change', function() {
                                if(this.checked) {
                                    extraSection.classList.remove('d-none');
                                } else {
                                    extraSection.classList.add('d-none');
                                }
                            });
                        });
                    </script>
                    @endif

                    <h5 class="fw-bold text-primary mb-4">Seguridad</h5>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CONTRASEÑA ACTUAL (Solo si desea cambiarla)</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">NUEVA CONTRASEÑA</label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new_password" class="form-control" autocomplete="new-password">
                                <button class="btn btn-outline-secondary border-start-0 text-secondary" type="button" id="toggleNewPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
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
