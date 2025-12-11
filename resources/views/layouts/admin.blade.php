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
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="px-3 py-3 mb-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="overflow-hidden sidebar-header-text">
                <h4 class="fw-bold text-primary mb-0 text-nowrap">Barbería JR</h4>
                <small class="text-secondary text-nowrap">Panel de Gestión</small>
            </div>
            <button class="btn btn-sm btn-light border" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <nav class="nav flex-column">
            <!-- Dashboard is now Agenda -->
            <a class="nav-link sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Agenda">
                <i class="bi bi-calendar-check"></i> <span class="sidebar-text">Agenda</span>
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
            
            <div class="mt-5 px-3">
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="button" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 overflow-hidden" title="Salir" onclick="confirmLogout()">
                        <i class="bi bi-box-arrow-right"></i> <span class="sidebar-text">Salir</span>
                    </button>
                </form>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar logic for mobile typically goes here, keeping it simple for now -->
        <header class="mb-4 d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-bold">@yield('header')</h2>
            <div class="text-secondary">
                Hola, <span class="fw-bold text-dark">{{ auth()->user()->name }}</span>
            </div>
        </header>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('welcome_user'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: '¡Bienvenido, {{ session("welcome_user") }}!',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                        background: '#1a1d20', // Dark
                        color: '#7e6b2dff',      // Gold
                        customClass: {
                            popup: 'rounded-4 shadow-lg border border-secondary'
                        }
                    });
                });
            </script>
        @endif

        <div id="spa-content">
            @yield('content')
        </div>
    </div>

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
                    e.stopPropagation(); // prevent header click?
                    sidebar.classList.toggle('collapsed');
                });
            }

            // SPA Router
            document.body.addEventListener('click', async (e) => {
                const link = e.target.closest('a');
                if (!link) return;
                
                const url = link.href;
                
                // Allow external links or specific ignores
                if(link.target === '_blank' || link.hasAttribute('data-no-spa')) return;
                if(!url.startsWith(window.location.origin)) return;
                
                // Only handle admin routes logic for now
                if(!url.includes('/admin') && !url.includes('/dashboard') && !url.includes('/services') && !url.includes('/barbers') && !url.includes('/reports') && !url.includes('/calendar')) return;

                e.preventDefault();
                loadPage(url);
            });

            window.addEventListener('popstate', () => {
                loadPage(window.location.href, false);
            });
        });

        async function loadPage(url, push = true) {
            const loading = document.getElementById('loading-bar');
            loading.style.width = '10%';
            loading.style.display = 'block';
            
            try {
                // Highlight sidebar immediately
                document.querySelectorAll('.sidebar-link, .nav-link').forEach(l => {
                    l.classList.remove('active');
                    if(l.href === url) l.classList.add('active');
                });

                loading.style.width = '60%';
                
                const res = await axios.get(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                
                if (push) history.pushState(null, '', url);
                
                const contentDiv = document.getElementById('spa-content');
                
                // Parse and Insert HTML
                // We receive raw HTML from layout.ajax (content + scripts)
                // We need to carefully strip scripts to execute them
                const parser = new DOMParser();
                const doc = parser.parseFromString(res.data, 'text/html');
                
                // Fade out old content
                contentDiv.style.opacity = '0.5';
                
                setTimeout(() => {
                    contentDiv.innerHTML = res.data; // This inserts HTML but doesn't run scripts
                    
                    // Execute Scripts manually
                    const scripts = contentDiv.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    // Re-init generic plugins if needed
                    // (Bootstrap modals might need re-init but usually they are created on demand in script)
                    
                    contentDiv.style.opacity = '1';
                    loading.style.width = '100%';
                    setTimeout(() => loading.style.display = 'none', 200);
                }, 100);

            } catch (error) {
                console.error('SPA Error:', error);
                window.location.href = url; // Fallback to full reload
            }
        }
    </script>
    <!-- Global Script to Kill Autocomplete -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // SUPER AGGRESSIVE AUTOCOMPLETE KILLER
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                // 1. Random autocomplete attribute
                input.setAttribute('autocomplete', 'off_random_' + Math.random().toString(36).substring(7));
                
                // 2. Readonly Hack: Start readonly, remove on focus
                // This prevents the browser from suggesting immediately on load
                if (!input.hasAttribute('readonly')) {
                    input.setAttribute('readonly', 'true');
                    input.style.backgroundColor = input.tagName === 'TEXTAREA' || input.type === 'text' ? '' : 'inherit'; // Prevent gray background flash if possible
                    
                    input.addEventListener('focus', function() {
                        this.removeAttribute('readonly');
                    });
                    
                    // Safety: if clicked, remove readonly immediately
                    input.addEventListener('click', function() {
                        this.removeAttribute('readonly');
                    });
                }
            });
            
            // 3. Form level off
            document.querySelectorAll('form').forEach(form => {
                form.setAttribute('autocomplete', 'off');
            });
        });

        function confirmLogout() {
            Swal.fire({
                title: '¿Cerrar Sesión?',
                text: "¿Estás seguro que deseas salir del sistema?",
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
    </script>
    
</body>
</html>
