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
                            
                            <div class="mb-3 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Email</label>
                                <input type="email" name="email" value="{{ request()->email ?? old('email') }}" class="form-control bg-transparent border-secondary text-white" readonly>
                            </div>

                            <div class="mb-3 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Código de Verificación</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-123"></i></span>
                                    <input type="text" name="code" class="form-control bg-transparent border-secondary text-white" placeholder="Ej: 123456" required autocomplete="off">
                                </div>
                            </div>
                            
                            <div class="mb-3 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Nueva Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control bg-transparent border-secondary text-white" required autocomplete="new-password">
                                </div>
                            </div>

                            <div class="mb-4 text-start">
                                <label class="form-label text-gold small text-uppercase fw-bold" style="font-size: 0.75rem;">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-secondary text-secondary"><i class="bi bi-check-circle"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control bg-transparent border-secondary text-white" required autocomplete="new-password">
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
@endsection
