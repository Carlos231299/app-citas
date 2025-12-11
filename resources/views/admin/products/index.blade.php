@extends('layouts.admin')

@section('title', 'Inventario - Barbería JR')
@section('header', 'Gestión de Inventario')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
            <i class="bi bi-plus-lg me-2"></i> Nuevo Producto
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Producto</th>
                        <th>SKU</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $product->name }}</td>
                        <td class="text-muted">{{ $product->sku ?? '-' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $product->category }}</span></td>
                        <td class="fw-bold">${{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>
                            @if($product->stock <= $product->min_stock)
                                <span class="text-danger fw-bold"><i class="bi bi-exclamation-circle me-1"></i>{{ $product->stock }}</span>
                            @else
                                <span class="text-success">{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-secondary" onclick="editProduct({{ $product->id }})"><i class="bi bi-pencil"></i></button>
                            
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar producto?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">No hay productos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SKU (Opcional)</label>
                            <input type="text" name="sku" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="category" class="form-select">
                                <option value="General">General</option>
                                <option value="Cuidado Capilar">Cuidado Capilar</option>
                                <option value="Barba">Barba</option>
                                <option value="Accesorios">Accesorios</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Actual</label>
                            <input type="number" name="stock" class="form-control" required value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Mínimo</label>
                            <input type="number" name="min_stock" class="form-control" required value="5">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
