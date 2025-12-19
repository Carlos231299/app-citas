<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Barbería JR')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- FullCalendar CSS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Flatpickr for Mini Calendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
    
    <!-- Driver.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>

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
            <button class="btn bg-white border shadow-sm text-primary rounded-circle" id="sidebarToggle" style="width: 40px; height: 40px; align-items: center; justify-content: center;">
                <i class="bi bi-list fs-5"></i>
            </button>
        </div>

        <nav class="nav flex-column">
            <!-- Dashboard is now Agenda -->
            <a class="nav-link sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Agenda">
                <i class="bi bi-calendar-week-fill"></i> <span class="sidebar-text">Agenda</span>
            </a>
            
            @if(auth()->user()->role === 'admin')
            <a class="nav-link sidebar-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}" title="Servicios">
                <i class="bi bi-scissors"></i> <span class="sidebar-text">Servicios</span>
            </a>
            @endif

            {{-- Barbers: Visible ONLY to Admin (Barbers manage status in Profile now) --}}
            @if(trim(auth()->user()->role) === 'admin')
            <a class="nav-link sidebar-link {{ request()->routeIs('barbers.*') ? 'active' : '' }}" href="{{ route('barbers.index') }}" title="Gestionar Barberos">
                <i class="bi bi-people-fill"></i> <span class="sidebar-text">Gestionar Barberos</span>
            </a>
            @endif
            
            @if(auth()->user()->role === 'admin')
            <a class="nav-link sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}" title="Reportes">
                <i class="bi bi-file-earmark-bar-graph-fill"></i> <span class="sidebar-text">Reportes</span>
            </a>
            @endif
            
            <!-- Hidden Logout Form for Header Dropdown -->
            <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-none">
                @csrf
            </form>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="mb-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-white border shadow-sm text-primary rounded-circle" id="mobileSidebarToggle" style="width: 40px; height: 40px; align-items: center; justify-content: center;">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h2 class="h4 mb-0 fw-bold text-dark">@yield('header')</h2>
            </div>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle no-caret" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary fw-bold border border-primary-subtle transition-all hover-scale" style="width: 45px; height: 45px; font-size: 1.5rem;">
                        @if(auth()->user()->avatar)
                            {{ auth()->user()->avatar }}
                        @else
                            {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg text-small" aria-labelledby="dropdownUser1">
                    <li>
                        <div class="px-3 py-2">
                            <span class="d-block fw-bold text-dark">{{ auth()->user()->name }}</span>
                            <small class="text-secondary">{{ auth()->user()->email }}</small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2 text-primary"></i> Mi Perfil</a></li>
                    


                    <li><h6 class="dropdown-header text-uppercase small fw-bold mt-2">Sistema</h6></li>
                    <li><a class="dropdown-item" href="#" onclick="confirmLogout()"><i class="bi bi-box-arrow-right me-2 text-danger"></i> Cerrar Sesión</a></li>
                </ul>
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
    <script>
        const token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    
    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sidebar Toggle (Desktop Internal)
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const mobileToggle = document.getElementById('mobileSidebarToggle');
            
            function toggleSidebar(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Use 768 as the threshold (inclusive for ipad portrait often)
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('open');
                    sidebar.classList.remove('collapsed'); 
                } else {
                    sidebar.classList.toggle('collapsed');
                }
            }

            if(toggle && sidebar) toggle.addEventListener('click', toggleSidebar);
            if(mobileToggle && sidebar) mobileToggle.addEventListener('click', toggleSidebar);

            // Close sidebar on mobile when clicking outside (overlay)
            document.addEventListener('click', (e) => {
                // If mobile, open, and click outside sidebar and not on toggles
                if (window.innerWidth <= 768 && 
                    sidebar.classList.contains('open') && 
                    !sidebar.contains(e.target) && 
                    !mobileToggle.contains(e.target) && 
                    !toggle.contains(e.target)) {
                    
                    sidebar.classList.remove('open');
                }
            });

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
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hasSeenTour = {{ auth()->user()->has_seen_tour ? 'true' : 'false' }};
            const userRole = "{{ auth()->user()->role }}";

            // Only run if not seen and on dashboard (optional constraint)
            if (!hasSeenTour) {
                const driverObj = window.driver.js.driver({
                    showProgress: true,
                    animate: true,
                    doneBtnText: '¡Listo!',
                    nextBtnText: 'Siguiente',
                    prevBtnText: 'Atrás',
                    allowClose: false,
                    onDestroyed: () => {
                        // Mark as seen on exit (complete or close)
                        markTourAsSeen();
                    }
                });

                const steps = [];

                if (userRole === 'admin') {
                    steps.push(
                        { element: '.sidebar-header-text', popover: { title: 'Bienvenido', description: 'Este es tu nuevo panel de administración.' } },
                        { element: '.bi-calendar-week-fill', popover: { title: 'Agenda', description: 'Aquí puedes ver y gestionar todas las citas.' } },
                        { element: '.bi-scissors', popover: { title: 'Servicios', description: 'Agrega o edita los servicios y precios de la barbería.' } },
                        { element: '#barberFilter', popover: { title: 'Filtro', description: 'Filtra el calendario para ver la agenda de un barbero específico.' } },
                        { element: '.btn-primary.rounded-pill', popover: { title: 'Nueva Cita', description: 'Registra citas manualmente para clientes que llamen o lleguen al local.' } }
                    );
                } else {
                    // Barber Flow
                    steps.push(
                        { element: '.sidebar-header-text', popover: { title: 'Bienvenido', description: 'Este es tu panel de barbero.' } },
                        { element: '.bi-calendar-week-fill', popover: { title: 'Mi Agenda', description: 'Aquí verás tus citas asignadas.' } },
                        { element: '#calendar', popover: { title: 'Calendario', description: 'Toca una cita para ver detalles o cambiar su estado (Completar/Cancelar).' } }
                    );
                }

                driverObj.setSteps(steps);
                
                // Small delay to ensure UI renders
                setTimeout(() => {
                    driverObj.drive();
                }, 1000);
            }

            function markTourAsSeen() {
                axios.post("{{ route('profile.markTourSeen') }}")
                    .then(response => {
                        console.log('Tour marked as seen');
                    })
                    .catch(error => {
                        console.error('Error marking tour:', error);
                    });
            }
        });
    </script>
</body>
</html>
