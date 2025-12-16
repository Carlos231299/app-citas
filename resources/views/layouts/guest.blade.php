<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso - Barbería JR')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
    </style>
</head>
<body>
    


    @yield('content')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Kill Autocomplete (Standard)
        document.addEventListener('DOMContentLoaded', function() {
            // Forms: Autocomplete Off
            document.querySelectorAll('form').forEach(form => {
                form.setAttribute('autocomplete', 'off');
            });

            // SweetAlert for Success
            @if(session('success'))
                Swal.fire({
                    title: '¡Excelente!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    timer: 1500, // 1.5 seconds to be readable
                    timerProgressBar: true,
                    showConfirmButton: false,
                    background: '#1a1d20',
                    color: '#c5a964',
                    customClass: { popup: 'border-gold shadow-lg' }
                });
            @endif

            // SweetAlert for Errors
            @if($errors->any())
                let errorMsg = '';
                @foreach ($errors->all() as $error)
                    errorMsg += '{{ $error }}<br>';
                @endforeach
                
                Swal.fire({
                    title: 'Atención',
                    html: errorMsg,
                    icon: 'error',
                    confirmButtonColor: '#c5a964',
                    background: '#1a1d20',
                    color: '#fff'
                });
            @endif
        });
    </script>
</body>
</html>
