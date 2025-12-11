@extends('layouts.app')

@section('title', 'Login - Admin')

@section('content')
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-md-5 col-lg-4">
        <div class="card bg-dark border-gold shadow-lg animate-fade-in">
            <div class="card-body p-5 text-center">
                <i class="bi bi-shield-lock text-gold fs-1 mb-3"></i>
                <h3 class="mb-4 text-white">Acceso Administrativo</h3>
                
                <form action="{{ route('login') }}" method="POST" autocomplete="off">
                    @csrf
                    <!-- Fake inputs to trick browser -->
                    <input type="text" style="display:none">
                    <input type="password" style="display:none">

                    <div class="mb-3 text-start">
                        <label class="form-label text-secondary">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus 
                            autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                    </div>
                    
                    <div class="mb-4 text-start">
                        <label class="form-label text-secondary">Contraseña</label>
                        <input type="password" name="password" class="form-control" required 
                            autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');"
                            onpaste="return false">
                    </div>

                    <button type="submit" class="btn btn-gold w-100 py-2 fw-bold">Ingresar</button>
                    
                    <div class="mt-3">
                        <a href="{{ route('home') }}" class="text-secondary text-decoration-none small">
                            <i class="bi bi-arrow-left"></i> Volver al inicio
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
