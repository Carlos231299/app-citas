@extends('layouts.admin')

@section('title', 'Punto de Venta (POS) - Barbería JR')
@section('header', 'Punto de Venta')

@section('content')
<div class="row h-100 g-3">
    <!-- Product Grid -->
    <div class="col-lg-8 d-flex flex-column h-100">
        <!-- Search & Filter -->
        <div class="card border-0 shadow-sm mb-3 rounded-4">
            <div class="card-body p-2 d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="pos-search" class="form-control bg-light border-0" placeholder="Buscar producto..." onkeyup="filterPosProducts()">
                </div>
                <select id="pos-cat-filter" class="form-select bg-light border-0 w-auto" onchange="filterPosProducts()">
                    <option value="all">Todas las Categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Grid -->
        <div class="flex-grow-1 overflow-auto pe-1" style="max-height: calc(100vh - 200px);">
            <div class="row g-3" id="pos-product-grid">
                @foreach($products as $product)
                <div class="col-6 col-md-4 col-xl-3 pos-item" data-name="{{ strtolower($product->name) }}" data-category="{{ $product->category_id }}">
                    <div class="card h-100 border-0 shadow-sm hover-shadow cursor-pointer product-card-pos" onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->stock }})">
                        <div class="position-relative bg-light rounded-top-4 d-flex align-items-center justify-content-center" style="height: 120px;">
                             @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="w-100 h-100 object-fit-cover rounded-top-4">
                            @else
                                <i class="bi bi-box-seam fs-3 text-muted opacity-50"></i>
                            @endif
                            
                            @if($product->stock <= 0)
                                <div class="position-absolute w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center">
                                    <span class="badge bg-danger">Agotado</span>
                                </div>
                            @else 
                                <span class="position-absolute top-0 end-0 m-2 badge bg-dark bg-opacity-50 small">{{ $product->stock }}</span>
                            @endif
                        </div>
                        <div class="card-body p-2 text-center">
                            <h6 class="fw-bold text-dark text-truncate mb-0" style="font-size: 0.9rem;">{{ $product->name }}</h6>
                            <p class="text-primary fw-bold mb-0">${{ number_format($product->price, 0) }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Cart -->
    <div class="col-lg-4 h-100">
        <div class="card border-0 shadow rounded-4 h-100 d-flex flex-column">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-cart3 me-2"></i>Carrito</h5>
            </div>
            <div class="card-body flex-grow-1 overflow-auto" id="cart-items-container">
                <!-- Cart Items Render Here -->
                <div class="text-center text-muted mt-5 opacity-50">
                    <i class="bi bi-basket fs-1 d-block mb-2"></i>
                    <small>Carrito vacío</small>
                </div>
            </div>
            
            <div class="card-footer bg-white border-top border-light p-3">
                <div class="d-flex justify-content-between align-items-end mb-3">
                    <span class="text-secondary small">Total a Pagar</span>
                    <h3 class="mb-0 fw-bold text-dark" id="cart-total">$0</h3>
                </div>
                
                <div class="mb-3">
                     <label class="small fw-bold text-secondary mb-1">MÉTODO DE PAGO</label>
                     <div class="d-flex gap-2">
                         <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="efectivo" checked autocomplete="off">
                         <label class="btn btn-outline-primary flex-grow-1" for="pay_cash"><i class="bi bi-cash me-1"></i> Efectivo</label>

                         <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="transferencia" autocomplete="off">
                         <label class="btn btn-outline-primary flex-grow-1" for="pay_card"><i class="bi bi-credit-card me-1"></i> Transf.</label>
                     </div>
                </div>

                <button class="btn btn-primary w-100 py-3 fw-bold shadow-sm" id="btn-checkout" onclick="processSale()" disabled>
                    <i class="bi bi-check-lg me-2"></i> COBRAR
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let cart = [];

    // Filter Logic
    window.filterPosProducts = () => {
        const query = document.getElementById('pos-search').value.toLowerCase();
        const cat = document.getElementById('pos-cat-filter').value;
        const items = document.querySelectorAll('.pos-item');

        items.forEach(el => {
            const name = el.dataset.name;
            const category = el.dataset.category;
            const matchesSearch = name.includes(query);
            const matchesCat = (cat === 'all' || category == cat);

            if(matchesSearch && matchesCat) el.classList.remove('d-none');
            else el.classList.add('d-none');
        });
    };

    // Cart Logic
    window.addToCart = (id, name, price, maxStock) => {
        if(maxStock <= 0) return Swal.fire('Agotado', 'Este producto no tiene stock.', 'warning');

        const existing = cart.find(i => i.id === id);
        if(existing) {
            if(existing.qty >= maxStock) {
                return Swal.fire('Stock Límite', `Solo hay ${maxStock} unidades disponibles.`, 'info');
            }
            existing.qty++;
        } else {
            cart.push({ id, name, price, qty: 1, maxStock });
        }
        renderCart();
    };

    window.removeFromCart = (index) => {
        cart.splice(index, 1);
        renderCart();
    };

    window.updateQty = (index, delta) => {
        const item = cart[index];
        const newQty = item.qty + delta;
        if(newQty > item.maxStock) {
             return Swal.fire('Stock Límite', `Solo hay ${item.maxStock} unidades disponibles.`, 'info');
        }
        if(newQty < 1) return removeFromCart(index);
        item.qty = newQty;
        renderCart();
    };

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const totalEl = document.getElementById('cart-total');
        const btn = document.getElementById('btn-checkout');

        if(cart.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted mt-5 opacity-50">
                    <i class="bi bi-basket fs-1 d-block mb-2"></i>
                    <small>Carrito vacío</small>
                </div>`;
            totalEl.innerText = '$0';
            btn.disabled = true;
            return;
        }

        let total = 0;
        let html = '<ul class="list-group list-group-flush">';
        
        cart.forEach((item, index) => {
            const subtotal = item.price * item.qty;
            total += subtotal;
            html += `
                <li class="list-group-item px-0 py-3 border-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark text-truncate" style="max-width: 140px;">${item.name}</span>
                        <span class="fw-bold">$${new Intl.NumberFormat().format(subtotal)}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">$${new Intl.NumberFormat().format(item.price)} x ${item.qty}</small>
                        <div class="d-flex align-items-center bg-light rounded-pill p-1">
                            <button class="btn btn-sm btn-white rounded-circle shadow-sm p-0" style="width:24px;height:24px;" onclick="updateQty(${index}, -1)"><i class="bi bi-dash"></i></button>
                            <span class="mx-2 small fw-bold">${item.qty}</span>
                            <button class="btn btn-sm btn-white rounded-circle shadow-sm p-0" style="width:24px;height:24px;" onclick="updateQty(${index}, 1)"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>
                </li>
            `;
        });
        html += '</ul>';
        container.innerHTML = html;
        totalEl.innerText = '$' + new Intl.NumberFormat().format(total);
        btn.disabled = false;
    }

    // Checkout
    window.processSale = () => {
        if(!cart.length) return;
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        Swal.fire({
            title: '¿Confirmar Venta?',
            text: `Total: ${document.getElementById('cart-total').innerText} (${paymentMethod})`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, Cobrar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return axios.post("{{ route('pos.store') }}", {
                    items: cart,
                    payment_method: paymentMethod
                }).then(res => res.data)
                  .catch(err => {
                      Swal.showValidationMessage(`Error: ${err.response.data.message || 'Error desconocido'}`);
                  });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                renderCart();
                Swal.fire({
                    title: '¡Venta Exitosa!',
                    text: 'El inventario ha sido actualizado.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    // Update Stock UI locally or reload? Reload is safer to sync everything
                    location.reload(); 
                });
            }
        });
    }
</script>
<style>
    .product-card-pos { transition: transform 0.2s; }
    .product-card-pos:active { transform: scale(0.95); }
    .hover-shadow:hover { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important; }
</style>
@endpush
@endsection
