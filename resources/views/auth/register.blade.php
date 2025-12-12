@extends('layouts.guest')

@section('title', 'Registro - Barbería JR')

@section('content')
<div class="d-flex align-items-center min-vh-100" style="background: url('{{ asset('images/login-bg.jpg') }}') no-repeat center center; background-size: cover;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card bg-black bg-opacity-75 border-gold shadow-lg animate-fade-in" style="backdrop-filter: blur(5px);">
                    <div class="card-body p-5 text-center">
                        <h3 class="mb-4 text-white">Crear Cuenta</h3>
                        
                        <form action="{{ route('register') }}" method="POST" autocomplete="off">
                            @csrf
        
                            <div class="mb-3 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control bg-transparent border-secondary text-white" required autocomplete="off">
                                </div>
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" required autocomplete="off">
                                </div>
                            </div>
                            
                            <div class="mb-3 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" id="password" class="form-control bg-transparent border-secondary text-white" required autocomplete="off" placeholder="Mínimo 8 caracteres">
                                    <button class="btn btn-outline-secondary border-start-0 text-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <!-- Password Requirements Checklist -->
                                <div class="mt-2 text-start">
                                    <small class="text-gold fw-bold">Requisitos:</small>
                                    <ul class="list-unstyled mb-0 small ps-1" id="password-requirements">
                                        <li id="req-length" class="text-white-50"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Mínimo 8 caracteres</li>
                                        <li id="req-upper" class="text-white-50"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Una mayúscula</li>
                                        <li id="req-lower" class="text-white-50"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Una minúscula</li>
                                        <li id="req-number" class="text-white-50"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Un número</li>
                                        <li id="req-special" class="text-white-50"><i class="bi bi-circle me-1" style="font-size: 0.6rem;"></i> Un símbolo (@$!%*?&.)</li>
                                    </ul>
                                </div>
                            </div>

<script>
    // Injected Script for Checklist Logic (scoped to this block to avoid collisions if placed in main script)
    document.addEventListener('DOMContentLoaded', function() {
        const passInput = document.getElementById('password');
        
        // Requirements Elements
        const reqLength = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-upper');
        const reqLower = document.getElementById('req-lower');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');

        function updateRequirement(element, isValid) {
            const icon = element.querySelector('i');
            if (isValid) {
                element.classList.remove('text-white-50');
                element.classList.add('text-success', 'text-decoration-line-through');
                icon.classList.remove('bi-circle');
                icon.classList.add('bi-check-circle-fill');
            } else {
                element.classList.add('text-white-50');
                element.classList.remove('text-success', 'text-decoration-line-through');
                icon.classList.add('bi-circle');
                icon.classList.remove('bi-check-circle-fill');
            }
        }

        passInput.addEventListener('input', function() {
            const val = passInput.value;
            
            // 1. Length 8
            updateRequirement(reqLength, val.length >= 8);
            
            // 2. Uppercase
            updateRequirement(reqUpper, /[A-Z]/.test(val));

            // 3. Lowercase
            updateRequirement(reqLower, /[a-z]/.test(val));

            // 4. Number
            updateRequirement(reqNumber, /[0-9]/.test(val));

            // 5. Special Char (inc dot)
            updateRequirement(reqSpecial, /[@$!%*?&.]/.test(val));
        });
    });
</script>

                            <div class="mb-4 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-check-circle"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control bg-transparent border-secondary text-white" required autocomplete="off" placeholder="Repite la contraseña">
                                    <button class="btn btn-outline-secondary border-start-0 text-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div id="passwordMatchMessage" class="form-text small d-none"></div>
                            </div>
        
                            <button type="submit" class="btn btn-gold w-100 py-2 fw-bold text-uppercase mb-3">Registrarse</button>
                            
                            <div class="mt-3">
                                <span class="text-secondary small">¿Ya tienes cuenta?</span>
                                <a href="{{ route('login') }}" class="text-gold text-decoration-none small fw-bold">Ingresar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const matchMessage = document.getElementById('passwordMatchMessage');

    // Toggle Visibility Function
    function toggleVisibility(input, button) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        const icon = button.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    }

    togglePassword.addEventListener('click', function() {
        toggleVisibility(password, this);
    });

    toggleConfirmPassword.addEventListener('click', function() {
        toggleVisibility(confirmPassword, this);
    });

    // Real-time Validation
    function validateMatch() {
        const pass = password.value;
        const confirm = confirmPassword.value;

        if (confirm.length === 0) {
            matchMessage.classList.add('d-none');
            confirmPassword.classList.remove('is-valid', 'is-invalid');
            return;
        }

        matchMessage.classList.remove('d-none');

        if (pass === confirm) {
            confirmPassword.classList.remove('is-invalid');
            confirmPassword.classList.add('is-valid'); // Bootstrap green border
            matchMessage.textContent = 'Las contraseñas coinciden';
            matchMessage.className = 'form-text small text-success fw-bold';
        } else {
            confirmPassword.classList.remove('is-valid');
            confirmPassword.classList.add('is-invalid'); // Bootstrap red border
            matchMessage.textContent = 'Las contraseñas no coinciden.';
            matchMessage.className = 'form-text small text-danger fw-bold';
        }
    }

    password.addEventListener('input', validateMatch);
    confirmPassword.addEventListener('input', validateMatch);
});
</script>
@endsection
