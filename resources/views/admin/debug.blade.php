@extends('layouts.admin')

@section('title', 'DEBUGGING MODE')
@section('header', 'DEBUG HEADER')

@section('content')
<div class="container mt-5">
    <div class="alert alert-danger p-5 text-center">
        <h1 class="display-4 fw-bold">⚠️ MODO DEBUG ⚠️</h1>
        <p class="lead">Si estás viendo esto, el sistema de plantillas funciona correctamente.</p>
        <hr>
        <p>Esto confirma que el archivo <code>dashboard.blade.php</code> original tiene un error oculto o corrupción.</p>
        <p>Datos del controlador recibidos:</p>
        <pre class="text-start bg-light p-3 rounded">{{ json_encode($stats ?? 'NO DATA', JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
@endsection
