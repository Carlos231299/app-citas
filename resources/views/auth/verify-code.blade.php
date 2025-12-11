@extends('layouts.guest')

@section('title', 'Verificar Código - Barbería JR')

@section('content')
<div class="d-flex align-items-center min-vh-100" style="background: url('{{ asset('images/login-bg.jpg') }}') no-repeat center center; background-size: cover;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card bg-black bg-opacity-75 border-gold shadow-lg animate-fade-in" style="backdrop-filter: blur(8px); border: 1px solid #c5a964;">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-shield-check text-gold fs-1 mb-3"></i>
                        <h3 class="mb-3 text-white">Verificar Identidad</h3>
                        <p class="text-secondary small mb-4">Ingresa el código de 6 dígitos enviado a <strong>{{ $email }}</strong>.</p>
                        
                        <form action="{{ route('password.verify.check') }}" method="POST" autocomplete="off">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
        
                            <div class="mb-4 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Código de Verificación</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-123"></i></span>
                                    <input type="text" name="code" class="form-control bg-transparent border-secondary text-white text-center fw-bold letter-spacing-2" placeholder="######" required autocomplete="off" autofocus maxlength="6" style="letter-spacing: 5px;" inputmode="numeric" pattern="\d*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" readonly onfocus="this.removeAttribute('readonly');">
                                </div>
                            </div>
        
                            <button type="submit" class="btn btn-gold w-100 py-3 fw-bold text-uppercase mb-3 shadow-gold-hover">Verificar Código</button>
                            
                            <div class="mt-4">
                                <div id="timer-container" class="text-white small mb-2">
                                    El código expira en: <span id="timer" class="text-gold fw-bold">05:00</span>
                                </div>

                                <form action="{{ route('password.verify.resend') }}" method="POST" class="d-inline" autocomplete="off">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ $email }}">
                                    <button type="submit" id="resend-btn" class="btn btn-link text-white-50 text-decoration-none small hover-gold" disabled>
                                        <i class="bi bi-arrow-repeat"></i> Reenviar Código
                                    </button>
                                </form>
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
        let timeLeft = 300; // 5 minutes in seconds
        const timerElement = document.getElementById('timer');
        const resendBtn = document.getElementById('resend-btn');
        const timerContainer = document.getElementById('timer-container');

        const countdown = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            timerElement.textContent = `${minutes}:${seconds}`;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerElement.textContent = "Expirado";
                timerElement.classList.remove('text-gold');
                timerElement.classList.add('text-danger');
                
                // Enable Resend Button
                resendBtn.removeAttribute('disabled');
                resendBtn.classList.remove('text-white-50');
                resendBtn.classList.add('text-gold', 'fw-bold');
            } else {
                timeLeft--;
            }
        }, 1000);
    });
</script>
@endsection
