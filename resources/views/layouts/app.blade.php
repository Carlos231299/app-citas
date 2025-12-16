<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Barbería JR')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    
    {{-- Navbar removed for complete separation. Admin accesses via /dashboard --}}

    <main class="container py-4">
        @if(session('success') && is_string(session('success')))
            <div class="alert alert-success alert-dismissible fade show bg-success text-white border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show bg-danger text-white border-0" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Global Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        } else {
            console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
        }
    </script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <!-- Global Script to Kill Autocomplete -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
            
            // SUPER AGGRESSIVE AUTOCOMPLETE KILLER
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.setAttribute('autocomplete', 'off_random_' + Math.random().toString(36).substring(7));
                
                if (!input.hasAttribute('readonly')) {
                    input.setAttribute('readonly', 'true');
                    input.style.backgroundColor = input.tagName === 'TEXTAREA' || input.type === 'text' ? '' : 'inherit';
                    
                    const removeReadonly = function() { this.removeAttribute('readonly'); };
                    input.addEventListener('focus', removeReadonly);
                    input.addEventListener('click', removeReadonly);
                }
            });
             document.querySelectorAll('form').forEach(form => form.setAttribute('autocomplete', 'off'));
        });
    </script>
    @stack('scripts')
</body>
</html>
