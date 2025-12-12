@extends('layouts.guest')

@section('title', 'Recuperar Contraseña - Barbería JR')

@section('content')
<div class="d-flex align-items-center min-vh-100" style="background: url('{{ asset('images/login-bg.jpg') }}') no-repeat center center; background-size: cover;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card bg-black bg-opacity-75 border-gold shadow-lg animate-fade-in" style="backdrop-filter: blur(8px); border: 1px solid #c5a964;">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-envelope-exclamation text-gold fs-1 mb-3"></i>
                        <h3 class="mb-3 text-white">Recuperar Acceso</h3>
                        <p class="text-secondary small mb-4">Ingresa tu email y te enviaremos un enlace de recuperación.</p>
                        
                        @if (session('status'))
                            <div class="alert alert-success small mb-4 text-start">
                                <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                            </div>
                        @endif

                        <form action="{{ route('password.email') }}" method="POST" autocomplete="off">
                            @csrf
        
                            <div class="mb-4 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" required autocomplete="off">
                                </div>
                            </div>
        
                            <button type="submit" class="btn btn-gold w-100 py-3 fw-bold text-uppercase mb-3 shadow-gold-hover">Enviar Enlace</button>
                            
                            <div class="mt-3">
                                <a href="{{ route('login') }}" class="text-white-50 text-decoration-none small hover-gold">
                                    <i class="bi bi-arrow-left"></i> Volver al Login
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
