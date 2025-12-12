@extends('layouts.admin')

@section('title', 'Dashboard - Barbería JR')
@section('header', 'Agenda')

@section('content')
<div class="d-flex flex-column h-100">
    <!-- Stats Row -->
    <div class="row g-2 mb-4 animate-fade-in">
        <!-- Citas Hoy -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-primary">
                <div class="card-body p-2">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-md-2 mb-1 mb-md-0">
                            <i class="bi bi-calendar-event text-primary fs-5"></i>
                        </div>
                        <div class="text-center text-md-start">
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Citas Hoy</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['total_today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingresos -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-success">
                <div class="card-body p-2">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle me-md-2 mb-1 mb-md-0">
                            <i class="bi bi-cash-stack text-success fs-5"></i>
                        </div>
                        <div class="text-center text-md-start">
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Ingresos</h6>
                            <h4 class="mb-0 fw-bold text-dark">${{ number_format($stats['revenue_today'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendientes -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-warning">
                <div class="card-body p-2">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-md-2 mb-1 mb-md-0">
                            <i class="bi bi-clock-history text-warning fs-5"></i>
                        </div>
                        <div class="text-center text-md-start">
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Pendientes</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['pending_today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barberos Disponibles -->
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-info">
                <div class="card-body p-2">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <div class="bg-info bg-opacity-10 p-2 rounded-circle me-md-2 mb-1 mb-md-0">
                            <i class="bi bi-people-fill text-info fs-5"></i>
                        </div>
                        <div class="text-center text-md-start">
                            <h6 class="text-secondary small text-uppercase mb-0 fw-bold" style="font-size: 0.7rem;">Barberos Disponibles</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['active_barbers'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Test Footer -->
    <div class="alert alert-success p-2 text-center small">
        ✅ Fase 2: Stats Cards Restauradas
    </div>
</div>
@endsection
