@extends('layouts.admin')

@section('title', 'Historial de Ventas')
@section('header', 'Reportes de Venta')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">Historial de Transacciones</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $sale->id }}</td>
                        <td>{{ $sale->created_at->format('d M Y - h:i A') }}</td>
                        <td>
                            @if($sale->user)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                    {{ $sale->user->name }}
                                </span>
                            @else
                                <span class="text-muted">Desconocido</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info text-capitalize">
                                {{ $sale->payment_method }}
                            </span>
                        </td>
                        <td class="fw-bold text-success">
                            ${{ number_format($sale->total, 0) }}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="collapse" data-bs-target="#saleDetails{{ $sale->id }}">
                                <i class="bi bi-chevron-down"></i> Ver Items
                            </button>
                        </td>
                    </tr>
                    <!-- Items Row -->
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <div class="collapse bg-light" id="saleDetails{{ $sale->id }}">
                                <div class="p-3">
                                    <h6 class="fw-bold small text-muted mb-2">PRODUCTOS VENDIDOS:</h6>
                                    <ul class="list-group list-group-flush shadow-sm rounded-3">
                                        @php
                                            $items = is_string($sale->items) ? json_decode($sale->items, true) : $sale->items;
                                        @endphp
                                        @foreach($items as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center bg-white">
                                            <div>
                                                <span class="fw-bold text-dark">{{ $item['product_name'] ?? 'Producto' }}</span>
                                                <small class="d-block text-muted">Cant: {{ $item['quantity'] }} x ${{ number_format($item['price'], 0) }}</small>
                                            </div>
                                            <span class="fw-bold text-dark">${{ number_format($item['subtotal'], 0) }}</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-3 opacity-50"></i>
                            No hay ventas registradas aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sales->hasPages())
    <div class="card-footer bg-white border-top-0 py-3">
        {{ $sales->links() }}
    </div>
    @endif
</div>
@endsection
