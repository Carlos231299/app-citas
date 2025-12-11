@extends('layouts.admin')

@section('title', 'Caja POS - Barbería JR')
@section('header', 'Punto de Venta')

@section('content')
<div class="row h-100">
    <!-- Left: Catalog -->
    <div class="col-md-7 h-100">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <ul class="nav nav-pills card-header-pills" id="pos-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold" id="services-tab" data-bs-toggle="pill" data-bs-target="#services-panel" type="button">
                            <i class="bi bi-scissors me-2"></i>Servicios
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" id="products-tab" data-bs-toggle="pill" data-bs-target="#products-panel" type="button">
                            <i class="bi bi-box-seam me-2"></i>Productos
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body bg-light overflow-auto" style="height: 600px;">
                <div class="tab-content" id="pos-tabContent">
                    <!-- Services Panel -->
                    <div class="tab-pane fade show active" id="services-panel" role="tabpanel">
                        <div class="row g-3">
                            @foreach($services as $service)
                            <div class="col-md-4 col-sm-6">
                                <div class="card h-100 border-0 shadow-sm service-card-pos" onclick="addToCart({ id: {{ $service->id }}, name: '{{ $service->name }}', price: {{ $service->price }}, type: 'service' })">
                                    <div class="card-body text-center p-3 cursor-pointer">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            @if(\Str::contains($service->icon, ['/', '.']))
                                                <img src="{{ asset($service->icon) }}" style="width: 30px; height: 30px; object-fit: contain;">
                                            @else
                                                <i class="bi bi-{{ $service->icon }} fs-4 text-primary"></i>
                                            @endif
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1 small">{{ $service->name }}</h6>
                                        <span class="badge bg-light text-primary border border-primary-subtle">${{ number_format($service->price, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Products Panel -->
                    <div class="tab-pane fade" id="products-panel" role="tabpanel">
                        <div class="row g-3">
                            @foreach($products as $product)
                            <div class="col-md-4 col-sm-6">
                                <div class="card h-100 border-0 shadow-sm product-card-pos" onclick="addToCart({ id: {{ $product->id }}, name: '{{ $product->name }}', price: {{ $product->price }}, type: 'product', stock: {{ $product->stock }} })">
                                    <div class="card-body text-center p-3 cursor-pointer">
                                        <div class="rounded-circle bg-success bg-opacity-10 p-3 mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-box-seam fs-4 text-success"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1 small">{{ $product->name }}</h6>
                                        <div class="d-flex justify-content-center gap-2 mt-2">
                                            <span class="badge bg-light text-success border border-success-subtle">${{ number_format($product->price, 0, ',', '.') }}</span>
                                            <span class="badge bg-secondary text-white small" style="font-size: 0.65rem;">x{{ $product->stock }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Cart -->
    <div class="col-md-5 h-100">
        <div class="card border-0 shadow-sm h-100 d-flex flex-column">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cart3 me-2"></i>Orden de Venta</h5>
            </div>
            
            <!-- Items List -->
            <div class="card-body p-0 flex-grow-1 bg-light overflow-auto" id="cart-items-container" style="height: 350px;">
                <!-- Cart items injected via JS -->
                <div id="empty-cart-msg" class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x display-4 mb-2 d-block opacity-25"></i>
                    Carrito Vacío
                </div>
                <table class="table table-sm table-hover mb-0" id="cart-table" style="display: none;">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3">Item</th>
                            <th class="text-center" width="70">Cant.</th>
                            <th class="text-end">Subtotal</th>
                            <th width="40"></th>
                        </tr>
                    </thead>
                    <tbody id="cart-tbody" class="bg-white"></tbody>
                </table>
            </div>

            <!-- Footer (Totals & Checkout) -->
            <div class="card-footer bg-white border-top p-4">
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label text-secondary fw-bold small">CLIENTE</label>
                        <select id="customer_select" class="form-select">
                            <option value="">Cliente Ocasional</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-secondary fw-bold small">MÉTODO PAGO</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="payment_method" id="cash" value="cash" checked>
                            <label class="btn btn-outline-secondary" for="cash"><i class="bi bi-cash me-1"></i>Efectivo</label>

                            <input type="radio" class="btn-check" name="payment_method" id="card" value="card">
                            <label class="btn btn-outline-secondary" for="card"><i class="bi bi-credit-card me-1"></i>Tarjeta</label>

                            <input type="radio" class="btn-check" name="payment_method" id="transfer" value="transfer">
                            <label class="btn btn-outline-secondary" for="transfer"><i class="bi bi-phone me-1"></i>Nequi/Davi</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="h5 text-secondary mb-0">Total a Pagar</span>
                    <span class="h3 fw-bold text-primary mb-0" id="cart-total">$0</span>
                </div>

                <button class="btn btn-primary w-100 py-3 fw-bold shadow-sm" onclick="processSale()" id="btn-process" disabled>
                    <i class="bi bi-check-circle-fill me-2"></i>COMPLETAR VENTA
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let cart = [];

    function addToCart(item) {
        const existing = cart.find(i => i.id === item.id && i.type === item.type);
        
        if (existing) {
            if(item.type === 'product' && existing.quantity >= item.stock) {
                alert('Stock insuficiente');
                return;
            }
            existing.quantity++;
        } else {
            cart.push({ ...item, quantity: 1 });
        }
        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('cart-tbody');
        const emptyMsg = document.getElementById('empty-cart-msg');
        const table = document.getElementById('cart-table');
        const totalEl = document.getElementById('cart-total');
        const btnProcess = document.getElementById('btn-process');

        tbody.innerHTML = '';
        let total = 0;

        if (cart.length === 0) {
            emptyMsg.style.display = 'block';
            table.style.display = 'none';
            btnProcess.disabled = true;
            totalEl.textContent = '$0';
            return;
        }

        emptyMsg.style.display = 'none';
        table.style.display = 'table';
        btnProcess.disabled = false;

        cart.forEach((item, index) => {
            const subtotal = item.price * item.quantity;
            total += subtotal;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-3 align-middle">
                    <div class="fw-bold text-dark small">${item.name}</div>
                    <small class="text-muted text-uppercase" style="font-size: 0.65rem;">${item.type == 'service' ? 'Servicio' : 'Producto'}</small>
                </td>
                <td class="align-middle text-center">
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary px-1 py-0" onclick="updateQty(${index}, -1)">-</button>
                        <span class="input-group-text bg-white px-2">${item.quantity}</span>
                        <button class="btn btn-outline-secondary px-1 py-0" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="align-middle text-end fw-bold text-dark small">$${new Intl.NumberFormat('es-CO').format(subtotal)}</td>
                <td class="align-middle text-center">
                    <button class="btn btn-link text-danger p-0" onclick="removeItem(${index})"><i class="bi bi-x-circle-fill"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        totalEl.textContent = '$' + new Intl.NumberFormat('es-CO').format(total);
    }

    function updateQty(index, change) {
        if (cart[index].quantity + change <= 0) {
            removeItem(index);
            return;
        }
        
        if(cart[index].type === 'product' && cart[index].quantity + change > cart[index].stock) {
            alert('No hay suficiente stock');
            return;
        }

        cart[index].quantity += change;
        renderCart();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function processSale() {
        if(cart.length === 0) return;
        if(!confirm('¿Confirmar venta?')) return;

        const customerId = document.getElementById('customer_select').value;
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        // Calculate total cleanly
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        axios.post('{{ route("sales.store") }}', {
            items: cart,
            customer_id: customerId,
            payment_method: paymentMethod,
            total: total
        })
        .then(response => {
            alert('Venta registrada con éxito!');
            cart = [];
            renderCart();
            // Can redirect to invoice or reload
            window.location.reload(); 
        })
        .catch(error => {
            console.error(error);
            alert('Error al procesar la venta.');
        });
    }
</script>

<style>
    .cursor-pointer { cursor: pointer; }
    .service-card-pos:hover, .product-card-pos:hover { transform: translateY(-2px); transition: transform 0.2s; border-color: var(--primary) !important; }
</style>
@endpush
@endsection
