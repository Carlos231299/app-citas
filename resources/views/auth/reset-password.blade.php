@extends('layouts.guest')

@section('title', 'Restablecer Contraseña')

@section('content')
<div class="d-flex align-items-center min-vh-100" style="background: url('{{ asset('images/login-bg.jpg') }}') no-repeat center center; background-size: cover;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card bg-black bg-opacity-75 border-gold shadow-lg animate-fade-in" style="backdrop-filter: blur(8px); border: 1px solid #c5a964;">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-key text-gold fs-1 mb-3"></i>
                        <h3 class="mb-3 text-white">Nueva Contraseña</h3>
                        
                        <form action="{{ route('password.update') }}" method="POST" autocomplete="off">
                            @csrf
                            <input type="text" style="display:none" autocomplete="username">
                            <input type="password" style="display:none" autocomplete="current-password">
                            
                            <!-- Hidden Fields (Passed from Verify Step) -->
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="mb-3 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Nueva Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control bg-transparent border-secondary text-white" required autocomplete="new-password" autofocus placeholder="">
                                    <button class="btn btn-outline-secondary border-start-0 text-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <!-- Checklist -->
                                <div class="mt-2">
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

                            <div class="mb-4 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-check-circle"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control bg-transparent border-secondary text-white" required autocomplete="new-password" placeholder="Repite la contraseña">
                                    <button class="btn btn-outline-secondary border-start-0 text-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
        
                            <button type="submit" class="btn btn-gold w-100 py-3 fw-bold text-uppercase mb-3 shadow-gold-hover">Actualizar Contraseña</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        
        // Checklist Elements
        const reqLength = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-upper');
        const reqLower = document.getElementById('req-lower');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');

        // Toggle Visibility
        function toggleVisibility(input, button) {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            const icon = button.querySelector('i');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }

        if(togglePassword) {
            togglePassword.addEventListener('click', function() { toggleVisibility(password, this); });
        }
        if(toggleConfirmPassword) {
            toggleConfirmPassword.addEventListener('click', function() { toggleVisibility(confirmPassword, this); });
        }

        const submitBtn = document.querySelector('button[type="submit"]');
        if(submitBtn) submitBtn.disabled = true;

        // Validation Logic
        function validateForm() {
            const val = password.value;
            const confirmVal = confirmPassword.value;
            
            const checks = [
                val.length >= 8,
                /[A-Z]/.test(val),
                /[a-z]/.test(val),
                /[0-9]/.test(val),
                /[@$!%*?&.]/.test(val)
            ];

            const allRequirementsMet = checks.every(Boolean);
            const passwordsMatch = val === confirmVal && val !== '';

            // Update Requirements UI
            updateRequirement(reqLength, checks[0]);
            updateRequirement(reqUpper, checks[1]);
            updateRequirement(reqLower, checks[2]);
            updateRequirement(reqNumber, checks[3]);
            updateRequirement(reqSpecial, checks[4]);

            // Enable/Disable Button
            if (allRequirementsMet && passwordsMatch) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.add('is-valid');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                if (confirmVal !== '' && !passwordsMatch) {
                   confirmPassword.classList.add('is-invalid');
                   confirmPassword.classList.remove('is-valid');
                } else {
                   confirmPassword.classList.remove('is-invalid');
                }
            }
        }

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

        if(password) {
            password.addEventListener('input', validateForm);
        }
        if(confirmPassword) {
            confirmPassword.addEventListener('input', validateForm);
        }
    });
</script>
@endsection
