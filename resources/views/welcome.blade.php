@extends('layouts.app')

@section('title', 'Reserva Online - Barbería JR')

@section('content')
<!-- Background Image -->
<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: url('{{ asset('images/login-bg.jpg') }}') no-repeat center center; background-size: cover; z-index: -1;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);"></div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <!-- Header -->
        <div class="position-absolute top-0 end-0 p-3">
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 fw-bold backdrop-blur">
                <i class="bi bi-person-fill me-1"></i> Iniciar Sesión
            </a>
        </div>
        <div class="text-center mb-5 mt-4">
            <img src="{{ asset('images/logo.png') }}" alt="Barbería JR Logo" class="img-fluid mb-3" style="max-height: 120px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
            <h1 class="display-6 fw-bold text-white text-shadow-sm">Barbería JR</h1>
            <p class="text-white opacity-75 lead text-shadow-sm">“Un buen corte es como un buen café, te despierta y te hace sentir mejor. ¡Ven y visítanos!”</p>
        </div>

        <!-- Wizard Container -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden backdrop-blur-light">
            <div class="card-body p-0">
                <form action="{{ route('book') }}" method="POST" id="bookingForm" class="h-100">
                    @csrf
                    
                    <div class="row g-0 h-100">
                        <!-- Left Panel: Steps -->
                        <div class="col-md-4 bg-light bg-opacity-75 p-4 border-end border-light">
                            <h5 class="fw-bold text-dark mb-4">Reserva tu Cita</h5>
                            
                            <!-- Steps Indicator -->
                            <div class="position-relative ps-3 border-start border-2 border-secondary border-opacity-25 pb-5">
                                <!-- Step 1 -->
                                <div class="step-item mb-4 position-relative" data-step="1">
                                    <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-dark text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; left: -1px !important;">1</div>
                                    <div class="ms-4">
                                        <h6 class="fw-bold mb-1 text-dark">Servicio</h6>
                                        <small class="text-muted d-block opacity-75" id="summary_service">Elige tratamiento</small>
                                    </div>
                                </div>

                                <!-- Step 2 -->
                                <div class="step-item mb-4 position-relative opacity-50" data-step="2">
                                    <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; left: -1px !important;">2</div>
                                    <div class="ms-4">
                                        <h6 class="fw-bold mb-1 text-dark">Profesional</h6>
                                        <small class="text-muted d-block opacity-75" id="summary_barber">Selecciona barbero</small>
                                    </div>
                                </div>

                                <!-- Step 3 -->
                                <div class="step-item mb-4 position-relative opacity-50" data-step="3">
                                    <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; left: -1px !important;">3</div>
                                    <div class="ms-4">
                                        <h6 class="fw-bold mb-1 text-dark">Fecha y Hora</h6>
                                        <small class="text-muted d-block opacity-75" id="summary_datetime">Disponibilidad</small>
                                    </div>
                                </div>

                                <!-- Step 4 -->
                                <div class="step-item position-relative opacity-50" data-step="4">
                                    <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; left: -1px !important;">4</div>
                                    <div class="ms-4">
                                        <h6 class="fw-bold mb-1 text-dark">Confirmación</h6>
                                        <small class="text-muted d-block opacity-75">Tus datos</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Form Content -->
                        <div class="col-md-8 p-4 bg-white">
                            <!-- Step 1: Services -->
                            <div class="form-step active" id="step-1">
                                <h5 class="mb-3 fw-bold text-dark">Selecciona un Servicio</h5>
                                <div class="row g-2">
                                    @foreach($services as $service)
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="service_id" id="service_{{ $service->id }}" value="{{ $service->id }}" data-name="{{ $service->name }}" data-price="{{ $service->price }}" required onchange="nextStep(2)">
                                        <label class="btn btn-outline-light text-dark text-start w-100 p-3 h-100 border shadow-sm hover-shadow transition-all" for="service_{{ $service->id }}">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <i class="bi bi-scissors fs-4 text-dark opacity-100"></i>
                                                <span class="badge bg-dark rounded-pill">${{ number_format($service->price, 0) }}</span>
                                            </div>
                                            <span class="fw-bold d-block mb-1 text-uppercase small">{{ $service->name }}</span>
                                            <small class="text-muted" style="font-size: 0.75rem;">{{ $service->duration }} min</small>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Step 2: Barbers + DateTime -->
                            <div class="form-step d-none" id="step-2">
                                <h5 class="mb-3 fw-bold text-dark">Profesional y Horario</h5>
                                
                                <div class="row g-3">
                                    <!-- Barber -->
                                    <div class="col-12">
                                        <label class="form-label text-dark fw-bold small">PROFESIONAL</label>
                                        <select name="barber_id" id="barber_id" class="form-select form-select-lg border shadow-sm bg-white text-dark" required onchange="checkAvailability()">
                                            <option value="" selected disabled>Elige barbero...</option>
                                            @foreach($barbers as $barber)
                                                <option value="{{ $barber->id }}">{{ $barber->name }}</option>
                                            @endforeach
                                            @if($barbers->count() <= 1)
                                                <option disabled>Más barberos próximamente...</option>
                                            @endif
                                        </select>
                                    </div>

                                    <!-- Date -->
                                    <div class="col-md-6">
                                        <label class="form-label text-dark fw-bold small">FECHA</label>
                                        @php
                                            $minDate = now()->format('H') >= 18 ? now()->addDay()->format('Y-m-d') : now()->format('Y-m-d');
                                        @endphp
                                        <div onclick="checkBarberSelected()">
                                            <input type="date" name="date" id="date" class="form-control form-control-lg border shadow-sm bg-white text-dark" required min="{{ $minDate }}" onchange="checkAvailability()" disabled title="Selecciona un barbero primero" style="pointer-events: none;"> 
                                        </div>
                                    </div>

                                    <!-- Slots -->
                                    <div class="col-12">
                                        <label class="form-label text-dark fw-bold small">HORARIOS DISPONIBLES</label>
                                        <div id="slots-container" class="d-flex flex-wrap gap-2 p-3 border rounded-3 bg-white shadow-sm" style="min-height: 80px;">
                                            <span class="text-muted small align-self-center">Selecciona profesional y fecha para ver horarios...</span>
                                        </div>
                                        <input type="hidden" name="time" id="time" required>
                                    </div>
                                    
                                    <div class="col-12 mt-4 text-end">
                                        <button type="button" class="btn btn-dark rounded-pill px-4 fw-bold" onclick="nextStep(3)" id="btnToStep3" disabled>Siguiente <i class="bi bi-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Client Info -->
                            <div class="form-step d-none" id="step-3">
                                <h5 class="mb-3 fw-bold text-dark">Tus Datos</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold small">NOMBRE COMPLETO</label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                        <input type="text" name="client_name" class="form-control border-start-0 ps-0 text-dark" required placeholder="Ej. Juan Pérez">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-dark fw-bold small">TELÉFONO (WHATSAPP)</label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-whatsapp"></i></span>
                                        <input type="tel" name="client_phone" class="form-control border-start-0 ps-0 text-dark" required placeholder="Ej. 3001234567" oninput="this.value = this.value.replace(/[^0-9+]/g, '')">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-dark fw-bold small">DETALLES ADICIONALES (OPCIONAL)</label>
                                    <textarea name="custom_details" class="form-control border shadow-sm text-dark" rows="2" placeholder="Ej. Corte bajo, solo barba..."></textarea>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-dark btn-lg rounded-pill fw-bold shadow-sm" id="btnSubmit">
                                        CONFIRMAR RESERVA <i class="bi bi-check-lg ms-2"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted small">
            &copy; {{ date('Y') }} Barbería JR - Software de Gestión de Citas
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectService(id, name, price, element, isCustom) {
        // Set Data
        const serviceInput = document.getElementById('service_id');
        serviceInput.value = id;
        serviceInput.dataset.price = price; // Store base price
        
        // Logic for default text when price is 0
        const priceText = price > 0 ? '$' + new Intl.NumberFormat('es-CO').format(price) : '(Se confirmará en el sitio)';
        document.getElementById('summary-service-name').innerHTML = `${name} <span class="text-dark ms-2" style="font-weight: normal;" id="summary-price-display">${priceText}</span>`;
        
        // Custom Logic
        const detailsContainer = document.getElementById('custom-details-container');
        const detailsInput = document.getElementById('custom_details');
        
        // Clean check
        const lowerName = name.trim().toLowerCase();
        if (isCustom || lowerName === 'otro' || lowerName === 'otro servicio') {
            detailsContainer.classList.remove('d-none');
            detailsInput.required = true;
        } else {
            detailsContainer.classList.add('d-none');
            detailsInput.required = false;
        }

        // UI Transition
        document.getElementById('step-services').classList.add('d-none');
        document.getElementById('step-details').classList.remove('d-none');
        document.getElementById('step-details').classList.add('animate-fade-in');
        
        // Update Tabs
        document.getElementById('step1-tab').classList.remove('active', 'fw-bold');
        document.getElementById('step1-tab').classList.add('text-muted');
        document.getElementById('step2-tab').classList.add('active', 'fw-bold');
        document.getElementById('step2-tab').classList.remove('text-muted');
    }

    function backToServices() {
        document.getElementById('step-details').classList.add('d-none');
        document.getElementById('step-services').classList.remove('d-none');
        
        // Update Tabs
        document.getElementById('step2-tab').classList.remove('active', 'fw-bold');
        document.getElementById('step2-tab').classList.add('text-muted');
        document.getElementById('step1-tab').classList.add('active', 'fw-bold');
        document.getElementById('step1-tab').classList.remove('text-muted');
    }

    function checkAvailability() {
        const barberId = document.getElementById('barber_id').value;
        const date = document.getElementById('date').value;
        const container = document.getElementById('slots-container');

        if (!barberId || !date) return;

        container.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>';
        document.getElementById('time').value = '';

        axios.get(`/api/slots?barber_id=${barberId}&date=${date}`)
            .then(response => {
                const slots = response.data;
                container.innerHTML = '';
                if (!Array.isArray(slots) || slots.length === 0) {
                    container.innerHTML = '<small class="text-danger">Sin cupos disponibles este día.</small>';
                    return;
                }
                slots.forEach(time => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-outline-secondary btn-sm m-1 slot-btn';
                    btn.textContent = time;
                    btn.onclick = () => selectTime(time, btn);
                    container.appendChild(btn);
                });
            })
            .catch(err => {
                console.error(err);
                if (err.response) {
                    container.innerHTML = `<small class="text-danger">Error: ${err.response.statusText} (${err.response.status})</small>`;
                } else {
                    container.innerHTML = '<small class="text-danger">Error de conexión. Verifica que el servidor esté activo.</small>';
                    container.innerHTML = '<small class="text-danger">Error de conexión. Verifica que el servidor esté activo.</small>';
                }
            });
    }

    function onBarberChange() {
        const dateInput = document.getElementById('date');
        dateInput.disabled = false;
        dateInput.classList.remove('bg-light');
        dateInput.style.pointerEvents = 'auto'; // Enable clicks on input
        dateInput.focus(); 
        checkAvailability(); 
    }

    function checkBarberSelected() {
        const barberId = document.getElementById('barber_id').value;
        if (!barberId) {
            Swal.fire({
                toast: true,
                position: 'center',
                icon: 'warning',
                title: '¡Por favor elija primero al barbero!',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: false,
                background: '#333',
                color: '#fff'
            });
        }
    }

    function selectTime(time, btn) {
        document.getElementById('time').value = time;
        document.querySelectorAll('.slot-btn').forEach(el => {
            el.className = 'btn btn-outline-secondary btn-sm m-1 slot-btn';
        });
        btn.className = 'btn btn-primary btn-sm m-1 slot-btn text-white';
        
        // Special Price Logic
        const basePrice = parseInt(document.getElementById('service_id').dataset.price || 0);
        
        // Parse time (e.g. "04:30 AM")
        const [timePart, meridiem] = time.split(' ');
        let [hours, minutes] = timePart.split(':').map(Number);
        
        if (meridiem === 'PM' && hours !== 12) hours += 12;
        if (meridiem === 'AM' && hours === 12) hours = 0;
        
        // Conditions: < 8 AM or > 6 PM (18:00) OR (18:30 etc)
        // 6:00 PM is 18:00. Special starts 18:30? or 18:01? 
        // User logic: "4 am to 7:30 am" and "6:30 pm to 10 pm".
        // Early: hour < 8 (since 7:30 is 7:30, hour 7 which is < 8).
        // Late: Starts 6:30 PM (18:30). So hour > 18 OR (hour == 18 && minutes >= 30).
        
        const isEarly = hours < 8; // 4, 5, 6, 7
        const isLate = hours > 18 || (hours === 18 && minutes >= 30);
        
        const priceDisplay = document.getElementById('summary-price-display');
        
        if (basePrice > 0 && (isEarly || isLate)) {
            const finalPrice = basePrice * 2;
            priceDisplay.innerHTML = `<span class="text-danger fw-bold">$${new Intl.NumberFormat('es-CO').format(finalPrice)} (Tarifa Especial x2)</span>`;
            
            // Toast
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                timerProgressBar: true, didOpen: (toast) => { toast.onmouseenter = Swal.stopTimer; toast.onmouseleave = Swal.resumeTimer; }
            });
            Toast.fire({ icon: 'warning', title: 'Horario Especial seleccionado. La tarifa se duplica.' });
        } else if(basePrice > 0) {
            priceDisplay.innerHTML = `$${new Intl.NumberFormat('es-CO').format(basePrice)}`;
        }
        
        // Update Tab mostly for visual progress
        document.getElementById('step3-tab').classList.add('text-primary'); 
    }

    @if(session('whatsapp_url'))
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: '¡Solicitud Creada!',
            html: "Tu solicitud para <b>{{ session('client_name') }}</b> ha sido registrada. <br><br>Debes contactar al barbero para que confirme la disponibilidad y duración.",
            icon: 'info',
            confirmButtonText: '<i class="bi bi-whatsapp"></i> Contactar al Barbero',
            confirmButtonColor: '#25D366', // WhatsApp Green
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            background: '#ffffff',
            color: '#1e293b'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open("{{ session('whatsapp_url') }}", '_blank');
            }
        });
    });
    @elseif(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: '¡Cita Reservada, {{ session("client_name") }}!',
            text: 'Gracias por confiar en nosotros. En breve te compartiremos la información de tu cita por medio de WhatsApp.',
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#4F46E5', // Indigo
            background: '#ffffff',
            color: '#1e293b',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
    });
    @endif
</script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@endsection

