@extends('layouts.guest')

@section('title', 'Login - Admin')

@section('content')
<div class="d-flex align-items-center min-vh-100" style="background: url('{{ asset('images/login-bg.jpg') }}') no-repeat center center; background-size: cover;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card bg-black bg-opacity-75 border-gold shadow-lg animate-fade-in" style="backdrop-filter: blur(8px); border: 1px solid #c5a964;">
                    <div class="card-body p-5 text-center position-relative">
                        
                        <div class="mb-4">
                            <img src="{{ asset('images/logo.png') }}" alt="Barbería JR" class="img-fluid" style="max-height: 120px; filter: drop-shadow(0 0 5px rgba(197, 169, 100, 0.5));">
                        </div>

                        <h4 class="mb-4 text-white" style="font-weight: 300; letter-spacing: 2px;">ACCESO ADMINISTRATIVO</h4>
                        
                        <form action="{{ route('login') }}" method="POST" autocomplete="off">
                            @csrf
                            <input type="text" style="display:none">
                            <input type="password" style="display:none">
        
                            <div class="mb-3 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control bg-transparent border-secondary text-white" required autocomplete="off" placeholder="usuario@ejemplo.com">
                                </div>
                            </div>
                            
                            <div class="mb-4 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control bg-transparent border-secondary text-white" required autocomplete="new-password" placeholder="••••••••">
                                </div>
                            </div>
        
                            <button type="submit" class="btn btn-gold w-100 py-3 fw-bold text-uppercase mb-3 shadow-gold-hover">Ingresar</button>
                            
                            <div class="d-flex justify-content-between text-small mt-4">
                                <a href="{{ route('register') }}" class="text-white-50 text-decoration-none small hover-gold">
                                    Crear Cuenta
                                </a>
                                <a href="{{ route('password.request') }}" class="text-white-50 text-decoration-none small hover-gold">
                                    Olvidé mi contraseña
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
