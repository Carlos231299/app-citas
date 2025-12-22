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
    
    <style>
        /* Loading Bar */
        /* Loading Bar */
        #nprogress { pointer-events: none; }
        #nprogress .bar { background: var(--gold-primary); position: fixed; z-index: 2000; top: 0; left: 0; width: 100%; height: 3px; }

        /* Sidebar Redesign - Hidden by default */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: #fff;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05); /* Lighter shadow */
            z-index: 1045;
            transform: translateX(-100%); /* Hidden by default */
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.open {
            transform: translateX(0); /* Visible */
        }
        
        /* Floating Toggle */
        .floating-toggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1050;
            width: 45px; /* Slightly larger touch target */
            height: 45px;
            border-radius: 50%;
            background: white;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .floating-toggle:hover {
            background: #f9fafb;
            transform: scale(1.05);
            color: var(--bs-primary);
        }

        /* Adjust main content to not have margin left since sidebar is hidden */
        .main-content {
            margin-left: 0 !important; /* Override explicit margin */
            padding-left: 15px; /* Add some padding if needed */
            padding-right: 15px;
        }
        
        /* Mobile Specific adjustments if needed */
        @media (min-width: 768px) {
            .main-content {
                padding-left: 80px; /* Space for the floating button visualization */
            }
        }
    </style>
</head>
<body class="admin-body">
    <div id="loading-bar" style="display:none; position:fixed; top:0; left:0; height:3px; background:#D4AF37; z-index:9999; transition:width 0.2s;"></div>
    
    <!-- Floating Sidebar Toggle -->
    <button class="floating-toggle" id="floatingSidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
    
    <!-- Sidebar (Default Hidden/Off-canvas) -->
    <div class="sidebar" id="sidebar">
        <div class="px-3 py-3 mb-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                {{-- Logo always visible --}}
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                
                {{-- Text always visible in open sidebar --}}
                <div class="sidebar-text d-flex flex-column">
                    <h4 class="fw-bold text-primary mb-0 text-nowrap" style="font-size: 1.1rem;">Barbería JR</h4>
                    <small class="text-secondary text-nowrap" style="font-size: 0.75rem;">Panel de Gestión</small>
                </div>
            </div>
            <!-- REMOVED Internal Toggle Button as per request -->
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
            {{-- Barbers: Visible ONLY to Admin (Barbers manage status in Profile now) --}}
            @if(trim(auth()->user()->role) === 'admin')
            
            <!-- POS & Inventory Group -->
            <!-- POS & Inventory Group -->
            <a class="nav-link sidebar-link" data-bs-toggle="collapse" href="#posSubmenu" role="button" aria-expanded="false">
                <i class="bi bi-shop"></i> 
                <span class="sidebar-text ms-2 w-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>POS / Inventario</span>
                        <i class="bi bi-chevron-down small transition-transform" style="font-size: 0.7rem;"></i>
                    </div>
                </span>
            </a>
            <!-- Removed 'show' class logic to keep it collapsed by default -->
            <div class="collapse" id="posSubmenu">
                <nav class="nav flex-column ps-3 border-start border-2 border-light ms-3 mb-2">
                    <a class="nav-link text-secondary py-1 {{ request()->routeIs('pos.*') ? 'fw-bold text-primary' : '' }}" href="{{ route('pos.index') }}">
                        <span class="sidebar-text">Venta de Productos</span>
                    </a>
                    <a class="nav-link text-secondary py-1 {{ request()->routeIs('products.*') ? 'fw-bold text-primary' : '' }}" href="{{ route('products.index') }}">
                        <span class="sidebar-text">Inventario</span>
                    </a>
                    <a class="nav-link text-secondary py-1 {{ request()->routeIs('pos.history') ? 'fw-bold text-primary' : '' }}" href="{{ route('pos.history') }}">
                        <span class="sidebar-text">Reportes</span>
                    </a>
                </nav>
            </div>

            @endif

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
            <div class="d-flex align-items-center gap-3">
                <!-- Mobile Toggle Removed (Replaced by Floating) - Kept placeholder for spacing if needed or just remove -->

                <h2 class="h4 mb-0 fw-bold text-dark">@yield('header')</h2>
            </div>
            
            <!-- Notifications & Profile -->
            <div class="d-flex align-items-center gap-3">
                <!-- Notifications -->
                <div class="dropdown">
                    <a href="#" class="position-relative text-secondary" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1.5rem;">
                        <i class="bi bi-bell"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.6rem;">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-0" aria-labelledby="notifDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                        <li class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                            <h6 class="mb-0 fw-bold">Notificaciones</h6>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <button onclick="markAllRead()" class="btn btn-link p-0 text-decoration-none small" style="font-size: 0.8rem;">Marcar todo leído</button>
                            @endif
                        </li>
                        @forelse(auth()->user()->unreadNotifications as $notification)
                            <li>
                                <a class="dropdown-item p-3 border-bottom" href="#" onclick="markAsRead(event, '{{ $notification->id }}', '{{ $notification->data['url'] ?? '#' }}')">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="bg-{{ $notification->data['color'] ?? 'primary' }} bg-opacity-10 rounded-circle p-2 text-{{ $notification->data['color'] ?? 'primary' }}">
                                            <i class="{{ $notification->data['icon'] ?? 'bi-info-circle' }}"></i>
                                        </div>
                                        <div>
                                            <p class="mb-1 fw-bold small text-dark">{{ $notification->data['title'] }}</p>
                                            <p class="mb-1 text-secondary small text-wrap">{{ $notification->data['message'] }}</p>
                                            <span class="text-muted" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="p-4 text-center text-muted">
                                <i class="bi bi-bell-slash fs-4 d-block mb-2 opacity-50"></i>
                                <small>No tienes notificaciones nuevas</small>
                            </li>
                        @endforelse
                    </ul>
                </div>

                <!-- Profile -->
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
            const sidebar = document.getElementById('sidebar');
            const floatingToggle = document.getElementById('floatingSidebarToggle');
            const mobileToggle = document.getElementById('mobileSidebarToggle'); // Might be removed in future if floating replaces it fully?
                                                                                // keeping simply to avoid JS errors if markup remains
            
            let closeTimeout;

            // --- Toggle Logic ---
            function openSidebar() {
                if(closeTimeout) clearTimeout(closeTimeout);
                sidebar.classList.add('open');
            }

            function closeSidebar() {
                // Delay closing to allow moving mouse from button to sidebar
                closeTimeout = setTimeout(() => {
                    sidebar.classList.remove('open');
                }, 300); // 300ms grace period
            }
            
            function toggleSidebarCompat(e) {
                e.preventDefault();
                e.stopPropagation();
                if (sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                } else {
                    sidebar.classList.add('open');
                }
            }

            // --- Interactions ---

            if (floatingToggle && sidebar) {
                // Desktop Hover Interaction (Screen > 768px)
                // Use matchMedia for robust check? Or just runtime check in event?
                
                // Mouse Enter Button -> Open
                floatingToggle.addEventListener('mouseenter', () => {
                   if (window.innerWidth > 768) openSidebar();
                });
                
                // Mouse Leave Button -> Try Close
                floatingToggle.addEventListener('mouseleave', () => {
                   if (window.innerWidth > 768) closeSidebar();
                });

                // Mouse Enter Sidebar -> Keep Open
                sidebar.addEventListener('mouseenter', () => {
                   if (window.innerWidth > 768 && closeTimeout) clearTimeout(closeTimeout);
                });

                // Mouse Leave Sidebar -> Close
                sidebar.addEventListener('mouseleave', () => {
                   if (window.innerWidth > 768) closeSidebar();
                });

                // Mobile / Click Interaction
                floatingToggle.addEventListener('click', (e) => {
                    // Always toggle on click, mainly for mobile, or if user prefers clicking on desktop
                    // But if hover is active, click might be redundant on desktop.
                    // Let's allow click to toggle for mobile mainly.
                    if (window.innerWidth <= 768) {
                        toggleSidebarCompat(e);
                    }
                });
            }

            // Mobile internal toggle support if still present
            if(mobileToggle) mobileToggle.addEventListener('click', toggleSidebarCompat);

            // Close when clicking outside (Mobile/Tablet usually)
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768 && 
                    sidebar.classList.contains('open') && 
                    !sidebar.contains(e.target) && 
                    !floatingToggle.contains(e.target)) {
                    
                    sidebar.classList.remove('open');
                }
            });

            // Clean Autocomplete
            document.querySelectorAll('form').forEach(form => {
                form.setAttribute('autocomplete', 'off');
            });
        });

        // Global Function for    <script>
        function confirmLogout() {
            Swal.fire({
                title: '¿Cerrar Sesión?',
                text: "¿Estás seguro de que quieres salir?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            })
        }

        function markAllRead() {
            fetch('{{ route("notifications.markRead") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    location.reload();
                }
            });
        }

        function markAsRead(event, id, url) {
            if(event) event.preventDefault(); // Stop default link behavior
            
            // Optimistic UI could go here (hide 'li'), but since we redirect, the reload handles it.
            // If url is '#', we might want to stay on page and remove the item.
            // But usually there is a destination.
            
            axios.post(`/notifications/${id}/mark-read`)
                .then(res => {
                    if (url && url !== '#') {
                        window.location.href = url;
                    } else {
                        // Just remove from list if no URL
                         location.reload();
                    }
                })
                .catch(err => {
                    console.error('Error marking as read', err);
                    if (url && url !== '#') window.location.href = url; // Redirect anyway
                });
        }
    </script>
</body>
</html>
