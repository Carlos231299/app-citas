@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Servicios - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 text-dark">
    <h2 class="fw-bold m-0" style="color: #1e293b;">Servicios</h2>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createServiceModal">
        <i class="bi bi-plus-lg"></i> Nuevo Servicio
    </button>
</div>

<div class="card bg-white border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary">
                <tr class="text-uppercase small fw-bold">
                    <th class="ps-4 py-3 border-0 rounded-start">Icono</th>
                    <th class="py-3 border-0">Nombre</th>
                    <th class="py-3 border-0">Precio</th>
                    <th class="text-end pe-4 py-3 border-0 rounded-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                <tr>
                    <td class="ps-4">
                        @php
                            $icons = array_filter(explode(',', $service->icon));
                            $firstIcon = trim($icons[0] ?? 'scissors');
                            $count = count($icons);
                        @endphp
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary" style="width: 40px; height: 40px;">
                                <i class="bi bi-{{ $firstIcon }} fs-5"></i>
                            </div>
                            @if($count > 1)
                                <span class="badge rounded-pill bg-light text-secondary border ms-2">+{{ $count - 1 }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark mb-1">{{ $service->name }}</div>
                        <div class="text-muted small text-truncate description-cell">
                            {{ $service->description }}
                        </div>
                    </td>
                    <td class="text-dark">
                        <div class="fw-bold">${{ number_format($service->price, 0, ',', '.') }}</div>
                        @if($service->extra_price > 0)
                            <small class="badge bg-warning text-dark border border-dark border-opacity-25" title="Tarifa Horario Extra">
                                <i class="bi bi-clock-history me-1"></i>Extra: ${{ number_format($service->extra_price, 0, ',', '.') }}
                            </small>
                        @else
                            <small class="text-muted fst-italic" style="font-size: 0.75rem;">Sin tarifa extra</small>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-light text-primary border me-1 mb-1" onclick='editService(@json($service))' title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar servicio?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-light text-danger border mb-1" title="Eliminar"><i class="bi bi-trash-fill"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Nuevo Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE</label>
                        <input type="text" name="name" class="form-control" required autocomplete="off">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">PRECIO NORMAL</label>
                            <input type="number" name="price" class="form-control" required autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-warning small fw-bold">VALOR EN HORARIO EXTRA</label>
                            <input type="number" name="extra_price" class="form-control" placeholder="Opcional" autocomplete="off">
                        </div>
                    </div>
                    
                    <!-- Icon Logic: Image or Text -->
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">ICONO (BOOTSTRAP)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border text-secondary"><i class="bi bi-scissors"></i></span>
                            <input type="text" name="icon" id="create_icon" class="form-control" placeholder="Ej: scissors, star" required autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" onclick="openIconSelector('create_icon')">
                                <i class="bi bi-grid"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted">
                            Separa con comas para múltiples iconos. Ej: <code>scissors, person</code>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">DESCRIPCIÓN</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Editar Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data" autocomplete="off">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">NOMBRE</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required autocomplete="off">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-secondary small fw-bold">PRECIO NORMAL</label>
                            <input type="number" name="price" id="edit_price" class="form-control" required autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-warning small fw-bold">VALOR EN HORARIO EXTRA</label>
                            <input type="number" name="extra_price" id="edit_extra_price" class="form-control" placeholder="Opcional" autocomplete="off">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">ICONO (BOOTSTRAP)</label>
                        <div class="d-flex align-items-center mb-2">
                            <div id="current_icon_display" class="me-2 text-primary fs-4"></div>
                            <div class="input-group">
                                <input type="text" name="icon" id="edit_icon" class="form-control" required autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" onclick="openIconSelector('edit_icon')">
                                    <i class="bi bi-grid"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">DESCRIPCIÓN</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Icon Selector Modal -->
<div class="modal fade" id="iconSelectorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Seleccionar Icono</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="iconSearch" class="form-control mb-3" placeholder="Buscar icono..." autocomplete="off">
                <div class="row g-2" id="iconGrid" style="max-height: 400px; overflow-y: auto;">
                    <!-- Icons injected via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let editModal;
    let iconModal;
    let currentInputId = null;

    const icons = [
        'scissors', 'person', 'person-fill', 'person-lines-fill', 'emoji-smile',
        'stars', 'star-fill', 'heart-fill', 'lightning-fill', 'gem',
        'cup-hot', 'droplet-fill', 'palette', 'brush', 'toggle-on',
        'clock', 'calendar-check', 'cash', 'credit-card', 'tiktok',
        'instagram', 'whatsapp', 'facebook', 'geo-alt', 'house',
        'shop', 'bag', 'cart', 'box-seam', 'gear', 
        'person-circle', 'person-video', 'person-square', 'incognito', 'eyeglasses'
    ];

    document.addEventListener('DOMContentLoaded', () => {
        editModal = new bootstrap.Modal(document.getElementById('editServiceModal'));
        iconModal = new bootstrap.Modal(document.getElementById('iconSelectorModal'));
        
        loadIcons();

        document.getElementById('iconSearch').addEventListener('keyup', (e) => {
            loadIcons(e.target.value.toLowerCase());
        });
    });

    function loadIcons(filter = '') {
        const grid = document.getElementById('iconGrid');
        grid.innerHTML = '';
        
        icons.filter(i => i.includes(filter)).forEach(icon => {
            const col = document.createElement('div');
            col.className = 'col-3 col-md-2 text-center';
            col.innerHTML = `
                <div class="p-3 border border-secondary rounded cursor-pointer hover-primary icon-option" onclick="selectIcon('${icon}')">
                    <i class="bi bi-${icon} fs-3 text-secondary"></i>
                    <div class="small text-secondary mt-1 text-truncate">${icon}</div>
                </div>
            `;
            grid.appendChild(col);
        });
    }

    function openIconSelector(inputId) {
        currentInputId = inputId;
        iconModal.show();
    }

    function selectIcon(icon) {
        const input = document.getElementById(currentInputId);
        const currentVal = input.value.trim();
        
        const finalize = (newValue) => {
            input.value = newValue;
            input.dispatchEvent(new Event('input'));
            iconModal.hide();
        };

        if (currentVal) {
            Swal.fire({
                title: 'Selección de Icono',
                text: `¿Deseas agregar "${icon}" a los existentes o reemplazar la selección actual?`,
                icon: 'question',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '<i class="bi bi-plus-lg"></i> Agregar',
                denyButtonText: '<i class="bi bi-arrow-repeat"></i> Reemplazar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0d6efd', // Bootstrap Primary
                denyButtonColor: '#fd7e14',   // Bootstrap Orange
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    finalize(currentVal + ', ' + icon);
                } else if (result.isDenied) {
                    finalize(icon);
                }
            });
        } else {
            finalize(icon);
        }
    }

    function editService(service) {
        document.getElementById('editForm').action = `/services/${service.id}`;
        document.getElementById('edit_name').value = service.name;
        document.getElementById('edit_name').value = service.name;
        document.getElementById('edit_price').value = service.price;
        document.getElementById('edit_extra_price').value = service.extra_price || '';
        document.getElementById('edit_description').value = service.description || '';
        document.getElementById('edit_icon').value = service.icon || 'scissors';

        const display = document.getElementById('current_icon_display');
        const icons = (service.icon || 'scissors').split(',');

        let html = '';
        icons.forEach(icon => {
            if(icon.trim()) {
                html += `<i class="bi bi-${icon.trim()} me-2 text-primary"></i>`;
            }
        });
        display.innerHTML = html;

        editModal.show();
    }
</script>
@endpush
@endsection
