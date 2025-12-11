<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Acceso - Barbería JR')</title>
    
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
    
    @if(session('success'))
        <div class="position-fixed top-0 start-50 translate-middle-x mt-4 z-3" style="min-width: 300px;">
            <div class="alert alert-success alert-dismissible fade show bg-success text-white border-0 shadow" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="position-fixed top-0 start-50 translate-middle-x mt-4 z-3" style="min-width: 300px;">
            <div class="alert alert-danger alert-dismissible fade show bg-danger text-white border-0 shadow" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @yield('content')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kill Autocomplete
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.setAttribute('autocomplete', 'off_random_' + Math.random().toString(36).substring(7));
            });
        });
    </script>
</body>
</html>
