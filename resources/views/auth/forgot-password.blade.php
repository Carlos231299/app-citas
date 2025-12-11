@extends('layouts.app')

@section('title', 'Recuperar Contraseña - Barbería JR')

@section('content')
<div class="d-flex align-items-center min-vh-100" style="background: url('{{ asset('images/login-bg.jpg') }}') no-repeat center center; background-size: cover;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card bg-black bg-opacity-75 border-gold shadow-lg animate-fade-in" style="backdrop-filter: blur(5px);">
                    <div class="card-body p-5 text-center">
                        <i class="bi bi-envelope-exclamation text-gold fs-1 mb-3"></i>
                        <h3 class="mb-3 text-white">Recuperar Acceso</h3>
                        <p class="text-secondary small mb-4">Ingresa tu email y te enviaremos un código de verificación.</p>
                        
                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf
        
                            <div class="mb-4 text-start">
                                <label class="form-label text-secondary small text-uppercase">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" required>
                                </div>
                            </div>
        
                            <button type="submit" class="btn btn-gold w-100 py-2 fw-bold text-uppercase mb-3">Enviar Código</button>
                            
                            <div class="mt-3">
                                <a href="{{ route('login') }}" class="text-secondary text-decoration-none small">
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
