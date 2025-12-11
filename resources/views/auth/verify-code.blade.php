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
                                    <input type="text" name="code" class="form-control bg-transparent border-secondary text-white text-center fw-bold letter-spacing-2" placeholder="######" required autocomplete="off" autofocus maxlength="6" style="letter-spacing: 5px;">
                                </div>
                            </div>
        
                            <button type="submit" class="btn btn-gold w-100 py-3 fw-bold text-uppercase mb-3 shadow-gold-hover">Verificar Código</button>
                            
                            <div class="mt-3">
                                <a href="{{ route('password.request') }}" class="text-white-50 text-decoration-none small hover-gold">
                                    <i class="bi bi-arrow-left"></i> Reenviar Código
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
