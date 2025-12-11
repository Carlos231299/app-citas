<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - Barbería JR')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- FullCalendar CSS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Loading Bar */
        #nprogress { pointer-events: none; }
        #nprogress .bar { background: var(--gold-primary); position: fixed; z-index: 2000; top: 0; left: 0; width: 100%; height: 3px; }
    </style>
</head>
<body class="admin-body">
    <div id="loading-bar" style="display:none; position:fixed; top:0; left:0; height:3px; background:#D4AF37; z-index:9999; transition:width 0.2s;"></div>
    
    <!-- Sidebar (Default Collapsed) -->
    <div class="sidebar collapsed" id="sidebar">
        <div class="px-3 py-3 mb-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="overflow-hidden sidebar-header-text">
                <h4 class="fw-bold text-primary mb-0 text-nowrap">Barbería JR</h4>
                <small class="text-secondary text-nowrap">Panel de Gestión</small>
            </div>
            <button class="btn bg-white border shadow-sm text-primary rounded-circle" id="sidebarToggle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-list fs-5"></i>
            </button>
        </div>

        <nav class="nav flex-column">
            <!-- Dashboard is now Agenda -->
            <a class="nav-link sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Agenda">
                <i class="bi bi-calendar-week-fill"></i> <span class="sidebar-text">Agenda</span>
            </a>
            
            <a class="nav-link sidebar-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}" title="Servicios">
                <i class="bi bi-scissors"></i> <span class="sidebar-text">Servicios</span>
            </a>
            
            <a class="nav-link sidebar-link {{ request()->routeIs('barbers.*') ? 'active' : '' }}" href="{{ route('barbers.index') }}" title="Barberos">
                <i class="bi bi-people-fill"></i> <span class="sidebar-text">Barberos</span>
            </a>
            
            <a class="nav-link sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}" title="Reportes">
                <i class="bi bi-file-earmark-bar-graph-fill"></i> <span class="sidebar-text">Reportes</span>
            </a>
            
            <div class="mt-auto px-3 mb-4">
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="button" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 overflow-hidden" title="Salir" onclick="confirmLogout()" style="z-index: 1002; position: relative;">
                        <i class="bi bi-box-arrow-right"></i> <span class="sidebar-text">Salir</span>
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="mb-4 d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-bold text-dark">@yield('header')</h2>
            <div class="text-secondary">
                Hola, <span class="fw-bold text-primary">{{ auth()->user()->name }}</span>
            </div>
        </header>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: '¡Excelente!',
                        text: "{{ session('success') }}",
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        confirmButtonColor: '#2563EB'
                    });
                });
            </script>
        @endif

        @if(session('welcome_user'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: '¡Bienvenido, {{ session("welcome_user") }}!',
                        text: 'Nos alegra verte de nuevo.',
                        icon: 'success',
                        confirmButtonText: 'Continuar',
                        confirmButtonColor: '#2563EB', // Blue
                        background: '#fff', 
                        color: '#333'
                    });
                });
            </script>
        @endif

        <div id="spa-content">
            @yield('content')
        </div>
    </div>

    <!-- ... scripts ... -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Toggle
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if(toggle && sidebar) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebar.classList.toggle('collapsed');
                });
            }

            // Clean Autocomplete
            document.querySelectorAll('form').forEach(form => {
                form.setAttribute('autocomplete', 'off');
            });
        });

        // Global Function for Logout
        function confirmLogout() {
            Swal.fire({
                title: '¿Cerrar Sesión?',
                text: "¿Estás seguro que deseas salir del sistema?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            })
        }
    </script>
    
</body>
</html>
