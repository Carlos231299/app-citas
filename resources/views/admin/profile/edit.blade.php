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
                        <label class="form-label text-secondary small fw-bold">CORREO ELECTRÓNICO</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <hr class="my-4">

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
