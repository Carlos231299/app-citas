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
                                    <input type="password" name="password" class="form-control bg-transparent border-secondary text-white" required autocomplete="new-password">
                                </div>
                                <div class="form-text text-white-50 small">Mínimo 8 caracteres, mayúscula, número y símbolo.</div>
                            </div>

                            <div class="mb-4 text-start">
                                <label class="form-label text-secondary small text-uppercase">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-check-circle"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control bg-transparent border-secondary text-white" required>
                                </div>
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
@endsection
