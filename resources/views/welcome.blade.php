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

        <div class="text-center mb-5 mt-4">
            <img src="{{ asset('images/logo.png') }}" alt="Barbería JR Logo" class="img-fluid mb-3" style="max-height: 120px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
            <h1 class="display-6 fw-bold text-white text-shadow-sm">Barbería JR</h1>
            <p class="text-white opacity-75 lead text-shadow-sm">“Un buen corte es como un buen café, te despierta y te hace sentir mejor. ¡Ven y visítanos!”</p>
        </div>

        <!-- Wizard Container (Reverted to Tabbed Style) -->
        <div class="card border border-2 shadow-sm" style="border-radius: 12px; overflow: hidden; border-color: #E2E8F0;">
            <div class="card-header bg-white border-bottom py-3">
                <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <span class="nav-link active fw-bold text-uppercase" id="step1-tab" style="letter-spacing: 0.5px;"><i class="bi bi-scissors me-2"></i>Servicios</span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-muted text-uppercase" id="step2-tab" style="letter-spacing: 0.5px;"><i class="bi bi-calendar-event me-2"></i>Agenda</span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-muted text-uppercase" id="step3-tab" style="letter-spacing: 0.5px;"><i class="bi bi-check-circle me-2"></i>Listo</span>
                    </li>
                </ul>
            </div>

            <div class="card-body p-0">
                <!-- Step 1: Services -->


                <div id="step-services" class="p-4 p-md-5 bg-white">
                    <h5 class="mb-4 text-dark fw-bold border-bottom pb-2">Selecciona un servicio</h5>
                    <div class="list-group list-group-flush border rounded-3">
                        @foreach($services as $service)
                        <button type="button" class="list-group-item list-group-item-action p-3 d-flex align-items-center mb-2 rounded-3 shadow-sm border-0 service-item" 
                            onclick="selectService({{ $service->id }}, '{{ $service->name }}', {{ $service->price ?? 0 }}, {{ $service->extra_price ?? 0 }}, this, {{ $service->is_custom ? 'true' : 'false' }})"
                            id="service-btn-{{ $service->id }}">
                            
                            <div class="d-flex gap-1 me-3">
                                @foreach(explode(',', $service->icon) as $icon)
                                    @if(trim($icon) !== '')
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary" style="width: 45px; height: 45px;">
                                        <i class="bi bi-{{ trim($icon) }} fs-5"></i>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            
                            <div class="flex-grow-1 text-start">
                                <h6 class="mb-0 fw-bold text-dark fs-5">{{ $service->name }}</h6>
                                @if($service->is_custom || in_array(strtolower(trim($service->name)), ['otro', 'otro servicio']))
                                    <small class="text-primary fw-semibold fst-italic">Se acordará detalle &rarr;</small>
                                @endif
                            </div>
                            <div class="text-end">
                                @if($service->price > 0)
                                    <span class="fw-bold text-dark fs-5">${{ number_format($service->price, 0, ',', '.') }}</span>
                                @else
                                    <span class="badge bg-secondary text-white border">Acordar con Barbero</span>
                                @endif
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Step 2: Details Form (Hidden initially) -->
                <div id="step-details" class="d-none p-4 p-md-5 bg-white">
                    <div class="mb-5 p-3 bg-light border border-primary border-opacity-25 rounded-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Servicio Seleccionado</small>
                            <div class="fw-bold text-dark fs-5" id="summary-service-name">...</div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 rounded-pill" onclick="backToServices()">Cambiar</button>
                    </div>

                    <form action="{{ route('book') }}" method="POST" id="bookingForm">
                        @csrf
                        <input type="hidden" name="service_id" id="service_id">
                        
                        <!-- Custom Details Field (Initially Hidden) -->
                        <div id="custom-details-container" class="mb-4 d-none">
                            <label class="form-label text-dark fw-bold small">¿QUÉ TE GUSTARÍA HACERTE?</label>
                            <input type="text" name="custom_details" id="custom_details" class="form-control form-control-lg border-2 border-primary" placeholder="Ej: Trenzas, Tintura, Rayitos...">
                            <div class="form-text text-muted">El precio final será confirmado por el barbero en el local.</div>
                        </div>

                        <div class="row g-4">
                            <!-- Barber -->
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-bold small">PROFESIONAL</label>
                                <select name="barber_id" id="barber_id" class="form-select form-select-lg border shadow-sm bg-white text-dark" required onchange="onBarberChange()">
                                    <option value="" selected disabled>Elige barbero...</option>
                                    @foreach($barbers as $barber)
                                        @php
                                            $returnDate = null;
                                            $now = now();
                                            $status = 'active';

                                            // 1. Check Temporary Unavailability (Range valid now)
                                            if ($barber->unavailable_start && $barber->unavailable_end && $now->between($barber->unavailable_start, $barber->unavailable_end)) {
                                                 $returnDate = $barber->unavailable_end->format('d/m/Y h:i A');
                                                 $status = 'temporary';
                                                 
                                                 // Check if Special Mode is ALSO active during this unavailability
                                                 if ($barber->special_mode && $barber->extra_time_start && $barber->extra_time_end) {
                                                     $exStart = \Carbon\Carbon::parse($barber->extra_time_start)->startOfDay();
                                                     $exEnd = \Carbon\Carbon::parse($barber->extra_time_end)->endOfDay();
                                                     if ($now->between($exStart, $exEnd)) {
                                                         $status = 'temporary_with_extra';
                                                     }
                                                 }
                                            }
                                            // 2. If NOT temp unavailable, check if explicitly inactive
                                            elseif (!$barber->is_active) {
                                                 $status = 'indefinite';
                                                 
                                                 // 3. Check Special Mode Override
                                                 if ($barber->special_mode && $barber->extra_time_start && $barber->extra_time_end) {
                                                     $extraStart = \Carbon\Carbon::parse($barber->extra_time_start)->startOfDay();
                                                     $extraEnd = \Carbon\Carbon::parse($barber->extra_time_end)->endOfDay();
                                                     
                                                     // If today is within extra time range, we allow selection
                                                     if ($now->between($extraStart, $extraEnd)) {
                                                         $status = 'special_only';
                                                     }
                                                 }
                                            }
                                        @endphp
                                        <option value="{{ $barber->id }}" 
                                                data-status="{{ $status }}" 
                                                data-return="{{ $returnDate }}"
                                                data-extra-start="{{ $barber->extra_time_start ? \Carbon\Carbon::parse($barber->extra_time_start)->format('d/m') : '' }}"
                                                data-extra-end="{{ $barber->extra_time_end ? \Carbon\Carbon::parse($barber->extra_time_end)->format('d/m') : '' }}"
                                                data-reason="{{ $barber->deactivation_reason }}">
                                            {{ $barber->name }} 
                                            @if($barber->average_rating > 0)
                                                <span class='text-warning fw-bold small'>
                                                    ★ {{ $barber->average_rating }}
                                                </span>
                                            @endif
                                        </option>
                                    @endforeach
                                    @if($barbers->isEmpty())
                                        <option disabled>No hay barberos registrados.</option>
                                    @endif
                                </select>
                            </div>

                            <!-- Date -->
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-bold small">FECHA</label>
                                @php
                                    // Relaxed logic: Always allow today. availability is determined by slots.
                                    $minDate = now()->format('Y-m-d');
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

                            <hr class="text-secondary opacity-10 my-4">

                            <!-- Client Info -->
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-bold small">TU NOMBRE</label>
                                <input type="text" name="client_name" class="form-control form-control-lg border shadow-sm" required placeholder="Nombre completo" autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-dark fw-bold small">TELÉFONO / WHATSAPP</label>
                                <div class="input-group shadow-sm">
                                    <select name="phone_prefix" class="form-select border bg-light text-dark fw-bold" style="max-width: 110px;" required>
                                        <option value="+57" selected>🇨🇴 +57</option>
                                        <option value="+58">🇻🇪 +58</option>
                                        <option value="+51">🇵🇪 +51</option>
                                        <option value="+593">🇪🇨 +593</option>
                                        <option value="+507">🇵🇦 +507</option>
                                        <option value="+1">🇺🇸 +1</option>
                                        <option value="+34">🇪🇸 +34</option>
                                    </select>
                                    <input type="tel" name="phone_number" class="form-control form-control-lg border ps-3" required placeholder="3001234567" minlength="7" maxlength="15" autocomplete="off" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Selecciona el país e ingresa el número.</small>
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow">Confirmar Reserva</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4 text-muted small">
            &copy; {{ date('Y') }} Barbería JR - Software de Gestión de Citas
        </div>
    </div>
</div>

@push('scripts')
<script>
    function selectService(id, name, price, extraPrice, element, isCustom) {
        // Set Data
        const serviceInput = document.getElementById('service_id');
        serviceInput.value = id;
        serviceInput.dataset.price = price; // Store base price
        serviceInput.dataset.extraPrice = extraPrice; // Store extra price
        
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
                if (slots.length === 0) {
                    container.innerHTML = '<small class="text-danger">Sin cupos disponibles este día (o barbero no disponible).</small>';
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
                container.innerHTML = '<small class="text-danger">Error al cargar horarios.</small>';
            });
    }

    function onBarberChange() {
        const select = document.getElementById('barber_id');
        const selectedOption = select.options[select.selectedIndex];
        const status = selectedOption.getAttribute('data-status');
        const returnDate = selectedOption.getAttribute('data-return');
        const reason = selectedOption.getAttribute('data-reason');
        
        const dateInput = document.getElementById('date');
        const container = document.getElementById('slots-container');

        // Check Status
        if (status === 'indefinite') {
            let msg = 'Este barbero no está disponible por ahora.';
            if(reason && reason !== 'null' && reason.trim() !== '') {
                msg = `<b>Motivo:</b> ${reason}`;
            }

            Swal.fire({
                icon: 'warning',
                title: 'No disponible',
                html: msg,
                confirmButtonColor: '#3085d6',
            });
            // Reset Selection
            select.value = "";
            dateInput.disabled = true;
            dateInput.style.pointerEvents = 'none';
            container.innerHTML = '<span class="text-muted small align-self-center">Selecciona profesional y fecha...</span>';
            return;
        } else if (status === 'temporary') {
            Swal.fire({
                icon: 'info',
                title: 'Aviso de Disponibilidad',
                text: `Este barbero tiene una pausa temporal hasta el ${returnDate}. Podrás agendar citas que comiencen después de esa hora.`,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Entendido'
            });
        } else if (status === 'temporary_with_extra') {
             const extraStart = selectedOption.getAttribute('data-extra-start');
             const extraEnd = selectedOption.getAttribute('data-extra-end');
             
             Swal.fire({
                icon: 'warning',
                title: 'Disponibilidad Limitada',
                html: `Este barbero está <b>temporalmente inactivo</b> (hasta el ${returnDate}), PERO tiene habilitado su <b>Horario Extra</b> desde el ${extraStart} hasta el ${extraEnd}.<br><br>Solo verás cupos especiales (ej. Noche/Madrugada).`,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Entendido'
            });
        } else if (status === 'special_only') {
            const extraStart = selectedOption.getAttribute('data-extra-start');
            const extraEnd = selectedOption.getAttribute('data-extra-end');
            
            Swal.fire({
                icon: 'info',
                title: 'Horario Extra Activado',
                text: `Este profesional solo tiene habilitado su Horario Extra (Madrugada/Noche) del ${extraStart} al ${extraEnd}.`,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Entendido'
            });
        }

        dateInput.disabled = false;
        dateInput.classList.remove('bg-light');
        dateInput.style.pointerEvents = 'auto'; 
        dateInput.focus(); 
        checkAvailability(); 
    }

    function checkBarberSelected() {
        // ... unchanged ...
    }

    function selectTime(time, btn) {
        document.getElementById('time').value = time;
        document.querySelectorAll('.slot-btn').forEach(el => {
            el.className = 'btn btn-outline-secondary btn-sm m-1 slot-btn';
        });
        btn.className = 'btn btn-primary btn-sm m-1 slot-btn text-white';
        
        // Special Price Logic
        const serviceInput = document.getElementById('service_id');
        const basePrice = parseInt(serviceInput.dataset.price || 0);
        const extraPrice = parseInt(serviceInput.dataset.extraPrice || 0);
        
        // Parse time (e.g. "04:30 AM")
        const [timePart, meridiem] = time.split(' ');
        let [hours, minutes] = timePart.split(':').map(Number);
        
        if (meridiem === 'PM' && hours !== 12) hours += 12;
        if (meridiem === 'AM' && hours === 12) hours = 0;
        
        const isEarly = hours < 8; // 4, 5, 6, 7
        const isLate = hours >= 22; // 22:00 onwards
        
        const priceDisplay = document.getElementById('summary-price-display');
        
        if ((isEarly || isLate) && extraPrice > 0) {
            priceDisplay.innerHTML = `<span class="text-danger fw-bold">$${new Intl.NumberFormat('es-CO').format(extraPrice)} (Tarifa Especial)</span>`;
            
            // Toast
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                timerProgressBar: true, didOpen: (toast) => { toast.onmouseenter = Swal.stopTimer; toast.onmouseleave = Swal.resumeTimer; }
            });
            Toast.fire({ icon: 'warning', title: 'Horario Especial seleccionado. Aplica tarifa extra.' });
        } else if(basePrice > 0) {
            priceDisplay.innerHTML = `$${new Intl.NumberFormat('es-CO').format(basePrice)}`;
        }
        
        // Update Tab mostly for visual progress
        document.getElementById('step3-tab').classList.add('text-primary'); 
    }

    // AJAX Submission Handling
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault(); 

        // 1. Manual Validation for Hidden Time Field
        const timeInput = document.getElementById('time');
        if (!timeInput.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta el horario',
                text: 'Por favor selecciona una hora disponible para tu cita.',
                confirmButtonColor: '#3085d6'
            });
            document.getElementById('slots-container').classList.add('border-danger');
            setTimeout(() => document.getElementById('slots-container').classList.remove('border-danger'), 2000);
            return; 
        }

        // 2. Check standard validity
        if (!this.checkValidity()) {
             e.stopPropagation();
             this.classList.add('was-validated');
             const firstInvalid = this.querySelector(':invalid');
             if(firstInvalid) firstInvalid.focus();
             return;
        }

        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

        const formData = new FormData(this);

        axios.post(this.action, formData)
             .then(response => {
                  const data = response.data;
                  
                  if (data.is_request) {
                      Swal.fire({
                          title: '¡Solicitud Recibida!',
                          html: "Tu solicitud para <b>" + formData.get('client_name') + "</b> ha sido registrada.<br><br>Tu cita está en <b>ESPERA DE CONFIRMACIÓN</b> para ser apartada.<br>Te notificaremos por WhatsApp.",
                          icon: 'info',
                          showCancelButton: true,
                          confirmButtonText: '¿Quieres agendar una nueva cita?',
                          cancelButtonText: 'Cerrar',
                          confirmButtonColor: '#3B82F6',
                          cancelButtonColor: '#6c757d',
                          allowOutsideClick: false
                      }).then((result) => {
                          location.reload(); 
                      });
                  } else {
                      let waUrl = data.whatsapp_url;
                      Swal.fire({
                          title: '¡Cita Confirmada!',
                          html: "Hola <b>" + formData.get('client_name') + "</b>, tu cita ha sido agendada exitosamente.<br><br>Te contactaremos por WhatsApp con los detalles.",
                          icon: 'success',
                          showCancelButton: true,
                          confirmButtonText: '¿Quieres agendar una nueva cita?',
                          cancelButtonText: 'Cerrar',
                          confirmButtonColor: '#10B981',
                          cancelButtonColor: '#6c757d',
                          allowOutsideClick: false
                      }).then((result) => {
                          if (waUrl) {
                              window.open(waUrl, '_blank');
                          }
                          location.reload();
                      });
                  }
             })
             .catch(error => {
                  console.error(error);
                  let errorMsg = 'No se pudo procesar la reserva.';
                   if (error.response && error.response.data && error.response.data.message) {
                       errorMsg = error.response.data.message;
                   }
                  Swal.fire({
                       icon: 'error',
                       title: 'Atención',
                       html: errorMsg,
                       confirmButtonColor: '#3085d6'
                  });
             })
             .finally(() => {
                  btn.disabled = false;
                  btn.innerHTML = originalText;
             });
    });
</script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@endsection

