@extends('layouts.admin')

@section('title', 'Inventario - Admin')
@section('header', 'Gestión de Inventario')

@section('content')
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
            <div>
                <strong>¡Ups! Algo salió mal.</strong>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
    <!-- Filters (Dynamic) -->
    <ul class="nav nav-pills shadow-sm bg-white rounded-pill p-1" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-bold" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button" onclick="filterProducts('all')">Todos</button>
        </li>
        @foreach($categories as $cat)
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-bold" id="cat-{{ $cat->id }}-tab" data-bs-toggle="pill" data-bs-target="#cat-{{ $cat->id }}" type="button" onclick="filterProducts('{{ $cat->id }}')">{{ $cat->name }}</button>
        </li>
        @endforeach
    </ul>

    <div class="d-flex gap-2">
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary shadow-sm fw-bold rounded-pill px-4">
            <i class="bi bi-tags-fill me-2"></i> Categorías
        </a>
        <button class="btn btn-primary shadow fw-bold px-4 rounded-pill" onclick="openCreateProductModal()">
            <i class="bi bi-box-seam me-2"></i> Nuevo Producto
        </button>
    </div>
</div>

<!-- Product Grid -->
<div class="row g-4" id="productGrid">
    @foreach($products as $product)
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 product-item" data-category="{{ $product->category_id }}">
        <div class="card h-100 border-0 shadow-sm hover-shadow transition-all overflow-hidden rounded-4">
            <!-- Image Area -->
            <div class="position-relative bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" class="w-100 h-100 object-fit-cover">
                @else
                    <div class="text-center text-muted opacity-50">
                        <i class="bi bi-image fs-1 d-block mb-2"></i>
                        <small class="fw-bold">Sin Imagen</small>
                    </div>
                @endif
                
                <!-- Stock Badge -->
                <div class="position-absolute top-0 end-0 m-3">
                    @if($product->stock <= $product->min_stock)
                        <span class="badge bg-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i> Stock Bajo: {{ $product->stock }}</span>
                    @else
                        <span class="badge bg-success shadow-sm bg-opacity-75 text-white backdrop-blur">Stock: {{ $product->stock }}</span>
                    @endif
                </div>

                 <!-- Category Badge -->
                 <div class="position-absolute top-0 start-0 m-3">
                    <span class="badge bg-{{ $product->category->color ?? 'secondary' }} shadow-sm text-uppercase" style="font-size: 0.7rem;">{{ $product->category->name ?? 'Sin Categ.' }}</span>
                 </div>
            </div>

            <div class="card-body d-flex flex-column">
                <h5 class="fw-bold text-dark mb-1">{{ $product->name }}</h5>
                <p class="text-secondary small mb-3 flex-grow-1 line-clamp-2">{{ $product->description ?? 'Sin descripción' }}</p>
                
                <div class="d-flex justify-content-between align-items-end mt-2">
                    <div>
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Precio</small>
                        <h4 class="mb-0 fw-bold text-primary">${{ number_format($product->price, 0) }}</h4>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light rounded-circle shadow-sm" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu border-0 shadow-lg">
                            <li><button class="dropdown-item py-2" onclick="editProduct({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->price }}', '{{ $product->stock }}', '{{ $product->category_id }}', '{{ addslashes($product->description) }}', {{ $product->min_stock }}, '{{ $product->image }}')"><i class="bi bi-pencil me-2 text-warning"></i> Editar</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Eliminar este producto?')">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item py-2 text-danger"><i class="bi bi-trash me-2"></i> Eliminar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Create Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NOMBRE</label>
                        <input type="text" name="name" class="form-control bg-light border-0" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">PRECIO</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light text-muted">$</span>
                                <input type="number" name="price" class="form-control bg-light border-0" required>
                            </div>
                        </div>
                        <div class="col-6">
                             <label class="form-label small fw-bold text-secondary">STOCK</label>
                             <input type="number" name="stock" class="form-control bg-light border-0" required>
                        </div>
                    </div>
                     <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">CATEGORÍA</label>
                        <select name="category_id" class="form-select bg-light border-0" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">DESCRIPCIÓN</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="2"></textarea>
                    </div>
                     <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">IMAGEN (Subir)</label>
                        <input type="file" name="image" id="create_image_input" class="form-control bg-light border-0" accept="image/*" onchange="previewImage(this, 'create_preview')">
                        <div class="mt-2 text-center d-none" id="create_preview_container">
                            <img id="create_preview" src="" class="rounded shadow-sm" style="max-height: 150px; object-fit: cover;">
                        </div>
                    </div>
                    <div class="mb-3">
                         <label class="form-label small fw-bold text-secondary">STOCK MÍNIMO (Alerta)</label>
                         <input type="number" name="min_stock" class="form-control bg-light border-0" value="5" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editProductForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NOMBRE</label>
                        <input type="text" name="name" id="edit_name" class="form-control bg-light border-0" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">PRECIO</label>
                             <div class="input-group">
                                <span class="input-group-text border-0 bg-light text-muted">$</span>
                                <input type="number" name="price" id="edit_price" class="form-control bg-light border-0" required>
                            </div>
                        </div>
                        <div class="col-6">
                             <label class="form-label small fw-bold text-secondary">STOCK</label>
                             <input type="number" name="stock" id="edit_stock" class="form-control bg-light border-0" required>
                        </div>
                    </div>
                     <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">CATEGORÍA</label>
                        <select name="category_id" id="edit_category_id" class="form-select bg-light border-0" required>
                             @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">DESCRIPCIÓN</label>
                        <textarea name="description" id="edit_description" class="form-control bg-light border-0" rows="2"></textarea>
                    </div>
                     <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">CAMBIAR IMAGEN</label>
                        <input type="file" name="image" class="form-control bg-light border-0" accept="image/*" onchange="previewImage(this, 'edit_preview')">
                         <div class="mt-2 text-center" id="edit_preview_container">
                            <img id="edit_preview" src="" class="rounded shadow-sm d-none" style="max-height: 150px; object-fit: cover;">
                            <small id="current_image_msg" class="text-muted d-block mt-1">Imagen Actual</small>
                        </div>
                    </div>
                     <div class="mb-3">
                         <label class="form-label small fw-bold text-secondary">STOCK MÍNIMO</label>
                         <input type="number" name="min_stock" id="edit_min_stock" class="form-control bg-light border-0" required>
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
        createModal = new bootstrap.Modal(document.getElementById('createProductModal'));
        editModal = new bootstrap.Modal(document.getElementById('editProductModal'));

        window.openCreateProductModal = () => {
            // Reset preview
            document.getElementById('create_preview_container').classList.add('d-none');
            document.getElementById('create_image_input').value = '';
            createModal.show();
        };
        
        window.editProduct = (id, name, price, stock, catId, description, min_stock, image) => {
            document.getElementById('editProductForm').action = `/products/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_price').value = parseInt(price);
            document.getElementById('edit_stock').value = stock;
            document.getElementById('edit_category_id').value = catId;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_min_stock').value = min_stock;
            
            const prevImg = document.getElementById('edit_preview');
            if(image) {
                prevImg.src = `{{ asset('storage') }}/${image}`;
                prevImg.classList.remove('d-none');
                document.getElementById('current_image_msg').innerText = 'Imagen Actual';
            } else {
                prevImg.classList.add('d-none');
                document.getElementById('current_image_msg').innerText = 'Sin imagen actual';
            }
            editModal.show();
        };

        window.filterProducts = (cat) => {
            const items = document.querySelectorAll('.product-item');
            items.forEach(el => {
                if(cat === 'all' || el.dataset.category == cat) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });
        };

        window.previewImage = (input, imgId) => {
             const containerId = imgId + '_container';
             const imgElement = document.getElementById(imgId);
             const container = document.getElementById(containerId);

             if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgElement.src = e.target.result;
                    imgElement.classList.remove('d-none');
                    if(container) container.classList.remove('d-none');
                    // Hide "current image" text in edit modal if new preview
                    if(imgId === 'edit_preview') {
                         document.getElementById('current_image_msg').innerText = 'Nueva Selección';
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        // Check for ?category_id= param to auto-filter
        const urlParams = new URLSearchParams(window.location.search);
        const catParam = urlParams.get('category_id');
        if(catParam) {
            // Click the tab
            const tabBtn = document.querySelector(`#cat-${catParam}-tab`);
            if(tabBtn) {
                // Must wait for Bootstrap?
                setTimeout(() => { tabBtn.click(); }, 100);
            }
        }

        @if($errors->any())
            // Re-open Create Modal if it was a create attempt (checked by absence of PUT method hidden field, simpler assumption: usually create)
            // Or better, check session triggers. For now, just show error at top is enough feedback, 
            // but let's try to reopen if we can detect which one.
            // Simplified: The alert at the top is sufficient for now to debug WHY it fails.
        @endif
    });
</script>
@endpush
@endsection
