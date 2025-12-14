@extends('layouts.guest')

@section('title', 'Verificación de Dos Factores')

@section('content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="max-width: 450px; width: 100%;">
    <div class="card-header bg-primary text-white text-center py-4 border-0">
        <h4 class="mb-0 fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Seguridad</h4>
        <p class="mb-0 opacity-75 small">Autenticación de Dos Factores</p>
    </div>
    <div class="card-body p-4 p-md-5 bg-white">
        <p class="text-center text-muted mb-4">
            Hemos enviado un código de verificación de 6 dígitos a tu correo electrónico <strong>{{ $email }}</strong>.
        </p>

        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
                <ul class="mb-0 list-unstyled small">
                    @foreach ($errors->all() as $error)
                        <li><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.verify') }}">
            @csrf

            <div class="mb-4">
                <label for="code" class="form-label text-secondary small fw-bold">CÓDIGO DE VERIFICACIÓN</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light border-end-0 text-primary"><i class="bi bi-123"></i></span>
                    <input id="code" type="text" 
                        class="form-control border-start-0 bg-light fw-bold text-center spacing-2" 
                        name="code" required autofocus autocomplete="one-time-code" inputmode="numeric" 
                        placeholder="######" maxlength="6" style="letter-spacing: 5px; font-size: 1.5rem;">
                </div>
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary btn-lg shadow fw-bold rounded-pill">
                    Verificar <i class="bi bi-arrow-right-short ms-1"></i>
                </button>
            </div>
        </form>
        
        <div class="text-center">
            <form method="POST" action="{{ route('2fa.resend') }}">
                @csrf
                <button type="submit" class="btn btn-link text-decoration-none text-muted small p-0">
                    ¿No recibiste el código? <span class="text-primary fw-bold">Reenviar</span>
                </button>
            </form>
            <div class="mt-3">
                 <a href="{{ route('login') }}" class="text-secondary small text-decoration-none">&larr; Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</div>
@endsection
