@extends('layouts.admin')

@section('title', 'Dashboard - Barbería JR')
@section('header', 'Agenda')

@section('content')
<div class="container mt-5">
    <div class="alert alert-success p-5 text-center">
        <h1 class="display-4 fw-bold">✅ DASHBOARD MINIMALISTA</h1>
        <p class="lead">Si ves esto, el archivo funciona. Ahora agregaremos componentes uno a uno.</p>
        <hr>
        <p>Datos recibidos: {{ count($stats ?? []) }} items</p>
    </div>
</div>
@endsection
