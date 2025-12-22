@extends('layouts.admin')

@section('title', 'Categorías - Admin')
@section('header', 'Gestión de Categorías')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
         <button class="btn btn-primary shadow fw-bold rounded-pill" onclick="openCreateCatModal()">
            <i class="bi bi-plus-lg me-2"></i> Nueva Categoría
        </button>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 py-3 ps-4">Nombre</th>
                                <th class="border-0 py-3">Descripción</th>
                                <th class="border-0 py-3">Color</th>
                                <th class="border-0 py-3">Productos</th>
                                <th class="border-0 py-3 text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $cat->name }}</td>
                                <td class="text-secondary small">{{ $cat->description }}</td>
                                <td><span class="badge bg-{{ $cat->color }} text-uppercase">{{ $cat->color }}</span></td>
                                <td>{{ $cat->products_count }}</td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light text-warning rounded-circle" onclick="editCat({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description) }}', '{{ $cat->color }}')">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                     <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar categoría?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light text-danger rounded-circle" {{ $cat->products_count > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Macros for Modals -->
<div class="modal fade" id="createCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NOMBRE</label>
                        <input type="text" name="name" class="form-control bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">COLOR (Bootstrap)</label>
                        <select name="color" class="form-select bg-light border-0" required>
                            <option value="primary">Primary (Azul)</option>
                            <option value="secondary">Secondary (Gris)</option>
                            <option value="success">Success (Verde)</option>
                            <option value="danger">Danger (Rojo)</option>
                            <option value="warning">Warning (Amarillo)</option>
                            <option value="info">Info (Celeste)</option>
                            <option value="dark">Dark (Negro)</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">DESCRIPCIÓN</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCatForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NOMBRE</label>
                        <input type="text" name="name" id="edit_name" class="form-control bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">COLOR (Bootstrap)</label>
                        <select name="color" id="edit_color" class="form-select bg-light border-0" required>
                            <option value="primary">Primary (Azul)</option>
                            <option value="secondary">Secondary (Gris)</option>
                            <option value="success">Success (Verde)</option>
                            <option value="danger">Danger (Rojo)</option>
                            <option value="warning">Warning (Amarillo)</option>
                            <option value="info">Info (Celeste)</option>
                            <option value="dark">Dark (Negro)</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">DESCRIPCIÓN</label>
                        <textarea name="description" id="edit_description" class="form-control bg-light border-0" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let createModal, editModal;
    document.addEventListener('DOMContentLoaded', () => {
        createModal = new bootstrap.Modal(document.getElementById('createCatModal'));
        editModal = new bootstrap.Modal(document.getElementById('editCatModal'));
        window.openCreateCatModal = () => createModal.show();
        window.editCat = (id, name, desc, color) => {
            document.getElementById('editCatForm').action = `/categories/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = desc;
            document.getElementById('edit_color').value = color;
            editModal.show();
        };
    });
</script>
@endpush
@endsection
