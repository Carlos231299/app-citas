@extends('layouts.guest')

@section('title', 'Verificación - Barbería JR')

@section('content')
<div class="d-flex align-items-center min-vh-100" style="background: url('{{ asset('images/login-bg.jpg') }}') no-repeat center center; background-size: cover;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card bg-black bg-opacity-75 border-gold shadow-lg animate-fade-in" style="backdrop-filter: blur(8px); border: 1px solid #c5a964;">
                    <div class="card-header border-0 bg-transparent text-center pt-4 pb-0">
                        <i class="bi bi-shield-lock text-gold display-4"></i>
                        <h4 class="mt-3 text-white" style="font-weight: 300; letter-spacing: 2px;">SEGURIDAD</h4>
                    </div>
                    
                    <div class="card-body p-4 text-center">
                        <p class="text-white-50 small mb-4">
                            Enviamos un código de 6 dígitos a <br>
                            <span class="text-gold fw-bold">{{ $email }}</span>
                        </p>

                        <form method="POST" action="{{ route('2fa.verify') }}" autocomplete="off">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Código de Verificación</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-key"></i></span>
                                    <input id="code" type="text" 
                                        class="form-control bg-transparent border-secondary text-white text-center fw-bold fs-4 tracking-widest" 
                                        name="code" required autofocus autocomplete="one-time-code" inputmode="numeric" 
                                        placeholder="######" maxlength="6" style="letter-spacing: 8px;">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-gold w-100 py-3 fw-bold text-uppercase mb-3 shadow-gold-hover">
                                Verificar <i class="bi bi-arrow-right-short ms-1"></i>
                            </button>
                        </form>
                        
                        <div class="mt-3">
                            <form method="POST" action="{{ route('2fa.resend') }}">
                                @csrf
                                <button type="submit" class="btn btn-link text-white-50 text-decoration-none small hover-gold p-0">
                                    ¿No llegó el código? <span class="text-gold">Reenviar</span>
                                </button>
                            </form>
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light w-100 opacity-75 hover-opacity-100">
                                <i class="bi bi-box-arrow-left me-2"></i> Cancelar / Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
