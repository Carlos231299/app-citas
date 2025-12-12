@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Barberos - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 text-dark">
    <h2 class="fw-bold m-0" style="color: #333;">Barberos</h2>
    <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#createBarberModal">
        <i class="bi bi-person-plus-fill"></i> Nuevo Barbero
    </button>
</div>

<div class="row g-4">
    @foreach($barbers as $barber)
    <div class="col-md-6 col-lg-4">
        <div class="card bg-white border-0 shadow-sm h-100 hover-shadow transition-all">
            <div class="card-body d-flex align-items-center p-4">
                <div class="me-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold display-6 shadow-sm border" style="width: 64px; height: 64px;">
                        {{ substr($barber->name, 0, 1) }}
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="text-dark fw-bold mb-1">{{ $barber->name }}</h5>
                    <div class="text-secondary small mb-2 d-flex align-items-center gap-1">
                        <i class="bi bi-whatsapp text-success"></i> <span class="fw-medium">{{ $barber->whatsapp_number ?? 'Sin número' }}</span>
                    </div>
                    
                    <div class="mt-2">
                        <!-- is_active Switch -->
                        <div class="form-check form-switch mb-2 d-flex align-items-center">
                            <input class="form-check-input me-2" type="checkbox" role="switch" id="active_switch_{{ $barber->id }}"
                                onchange="toggleBarberStatus({{ $barber->id }}, 'is_active', '{{ addslashes($barber->name) }}')" {{ $barber->is_active ? 'checked' : '' }}>
                            <label class="form-check-label small pt-1 fw-bold {{ $barber->is_active ? 'text-success' : 'text-muted' }}" 
                                   id="active_label_{{ $barber->id }}" for="active_switch_{{ $barber->id }}">
                                {{ $barber->is_active ? 'Activo' : 'Inactivo' }}
                            </label>
                        </div>
                        
                        <!-- special_mode Switch -->
                        <div class="form-check form-switch d-flex align-items-center">
                            <input class="form-check-input me-2" type="checkbox" role="switch" id="special_switch_{{ $barber->id }}"
                                onchange="toggleBarberStatus({{ $barber->id }}, 'special_mode', '{{ addslashes($barber->name) }}')" {{ $barber->special_mode ? 'checked' : '' }}>
                            <label class="form-check-label small pt-1 fw-bold {{ $barber->special_mode ? 'text-warning' : 'text-muted' }}" 
                                   id="special_label_{{ $barber->id }}" for="special_switch_{{ $barber->id }}">
                                <i class="bi bi-moon-stars-fill me-1"></i> Extra
                            </label>
                        </div>
                    </div>
                </div>
                <div class="dropdown align-self-start">
                    <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu border-0 shadow-sm">
                        <li><button class="dropdown-item py-2" type="button" onclick="editBarber({{ $barber->id }}, '{{ addslashes($barber->name) }}', '{{ $barber->whatsapp_number }}')"><i class="bi bi-pencil me-2 text-warning"></i> Editar</button></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('barbers.destroy', $barber) }}" method="POST" onsubmit="confirmDelete(event, '{{ addslashes($barber->name) }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> Eliminar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Create Modal -->
<div class="modal fade" id="createBarberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Nuevo Barbero</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('barbers.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Juan Pérez" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">WHATSAPP</label>
                        <input type="tel" name="whatsapp_number" class="form-control" placeholder="Ej: 300..." autocomplete="off"
                               oninput="this.value = this.value.replace(/[^0-9+]/g, '')" pattern="[0-9+]*">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gold px-4 fw-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editBarberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Editar Barbero</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" autocomplete="off" onsubmit="confirmUpdate(event)">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">WHATSAPP</label>
                        <input type="tel" name="whatsapp_number" id="edit_whatsapp" class="form-control" autocomplete="off"
                               oninput="this.value = this.value.replace(/[^0-9+]/g, '')" pattern="[0-9+]*">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" id="btnUpdateBarber" class="btn btn-gold px-4 fw-bold" disabled>Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editModal;
    document.addEventListener('DOMContentLoaded', () => {
        editModal = new bootstrap.Modal(document.getElementById('editBarberModal'));
        
        // Dirty Check Logic
        const nameInput = document.getElementById('edit_name');
        const whatsappInput = document.getElementById('edit_whatsapp');
        const btnUpdate = document.getElementById('btnUpdateBarber');
        let initialName = '';
        let initialWhatsapp = '';

        function checkChanges() {
            const currentName = nameInput.value.trim();
            const currentWhatsapp = whatsappInput.value.trim();
            
            // Loose comparison to handle potential null vs empty string differences
            const hasChanges = (currentName !== initialName) || (currentWhatsapp !== initialWhatsapp);
            
            btnUpdate.disabled = !hasChanges;
        }

        nameInput.addEventListener('input', checkChanges);
        whatsappInput.addEventListener('input', checkChanges);
        
        // Hook into the open function to set initial state
        window.editBarber = function(id, name, whatsapp) {
            document.getElementById('editForm').action = `/barbers/${id}`;
            
            nameInput.value = name;
            whatsappInput.value = whatsapp || '';
            
            initialName = name; // Store raw value
            initialWhatsapp = whatsapp || ''; // Store normalized empty
            
            // Reset button state
            btnUpdate.disabled = true;
            
            editModal.show();
        };
    });

    // Confirmation Logic
    function confirmDelete(e, name) {
        e.preventDefault();
        const form = e.target;
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Esta acción eliminará a ${name} permanentemente del equipo.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#CBD5E1',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    function confirmUpdate(e) {
        e.preventDefault();
        const form = e.target;
        const name = document.getElementById('edit_name').value;
        
        Swal.fire({
            title: '¿Guardar cambios?',
            text: `Se actualizará la información de ${name}.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#D4AF37', // Gold
            cancelButtonColor: '#CBD5E1',
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    function toggleBarberStatus(id, field, name) {
        const switchEl = document.getElementById(field === 'is_active' ? `active_switch_${id}` : `special_switch_${id}`);
        const labelEl = document.getElementById(field === 'is_active' ? `active_label_${id}` : `special_label_${id}`);
        const newValue = switchEl.checked ? 1 : 0;

        // Optimistic UI update
        if (field === 'is_active') {
            labelEl.textContent = newValue ? 'Activo' : 'Inactivo';
            labelEl.className = `form-check-label small pt-1 fw-bold ${newValue ? 'text-success' : 'text-muted'}`;

            // Auto-disable Special Mode Switch in UI if Deactivating
            if (newValue === 0) {
                const specialSwitch = document.getElementById(`special_switch_${id}`);
                const specialLabel = document.getElementById(`special_label_${id}`);
                if(specialSwitch && specialSwitch.checked) {
                    specialSwitch.checked = false;
                    specialLabel.className = 'form-check-label small pt-1 fw-bold text-muted';
                }
            }
        } else {
             // For Special Mode, label text is static, just color changes
            labelEl.className = `form-check-label small pt-1 fw-bold ${newValue ? 'text-warning' : 'text-muted'}`;
        }

        // Send Request
        axios.put(`/barbers/${id}`, {
            [field]: newValue,
            _token: '{{ csrf_token() }}' // Ensure CSRF is sent if not global
        })
        .then(response => {
           // Success toast? Optional.
           const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: false
            });
            Toast.fire({
                icon: 'success',
                title: `${name} actualizado correctamente`
            });
        })
        .catch(error => {
            console.error(error);
            // Revert on failure
            switchEl.checked = !newValue;
             // Revert Label
             if (field === 'is_active') {
                labelEl.textContent = !newValue ? 'Activo' : 'Inactivo';
                labelEl.className = `form-check-label small pt-1 fw-bold ${!newValue ? 'text-success' : 'text-muted'}`;
            } else {
                labelEl.className = `form-check-label small pt-1 fw-bold ${!newValue ? 'text-warning' : 'text-muted'}`;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `No se pudo actualizar a ${name}.`,
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        });
    }
</script>
@endpush
@endsection
