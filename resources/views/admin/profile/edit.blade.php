@extends('layouts.admin')

@section('title', 'Mi Perfil - Admin')
@section('header', 'Mi Perfil')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card bg-white border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold text-primary mb-4">Información Personal</h5>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CORREO ELECTRÓNICO</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold text-primary mb-4">Seguridad</h5>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">CONTRASEÑA ACTUAL (Solo si desea cambiarla)</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">NUEVA CONTRASEÑA</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">CONFIRMAR NUEVA CONTRASEÑA</label>
                            <input type="password" name="new_password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bi bi-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
