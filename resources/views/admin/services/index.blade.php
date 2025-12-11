@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Servicios - Admin')

@section('content')
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
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">PRECIO</label>
                        <input type="number" name="price" class="form-control" required autocomplete="off">
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
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">PRECIO</label>
                        <input type="number" name="price" id="edit_price" class="form-control" required autocomplete="off">
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

@push('scripts')
<script>
    let editModal;
    let iconModal;
    let currentInputId = null;

    // Curated list of relevant icons (Bootstrap Icons)
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
        
        // Filter Logic
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
        
        if (currentVal) {
            if (confirm('¿Agregar a los iconos existentes? (Cancelar para reemplazar)')) {
                input.value = currentVal + ', ' + icon;
            } else {
                input.value = icon;
            }
        } else {
            input.value = icon;
        }
        input.dispatchEvent(new Event('input'));
        iconModal.hide();
    }

    function editService(service) {
        document.getElementById('editForm').action = `/services/${service.id}`;
        document.getElementById('edit_name').value = service.name;
        document.getElementById('edit_price').value = service.price;
        document.getElementById('edit_description').value = service.description || '';
        document.getElementById('edit_icon').value = service.icon || 'scissors';
        
        const display = document.getElementById('current_icon_display');
        const icons = (service.icon || 'scissors').split(',');
        
        let htmlHtml = '';
        icons.forEach(icon => {
            if(icon.trim()) {
                htmlHtml += `<i class="bi bi-${icon.trim()} me-2 text-primary"></i>`;
            }
        });
        display.innerHTML = htmlHtml;
        
        editModal.show();
    }
</script>

<style>
    .cursor-pointer { cursor: pointer; }
    .icon-option { transition: all 0.2s; }
    .icon-option:hover { background-color: #EFF6FF !important; border-color: var(--primary) !important; }
    .icon-option:hover i { color: var(--primary) !important; }
    .description-cell { max-width: 250px; }
</style>
@endpush
@endsection
