@extends(request()->ajax() ? 'layouts.ajax' : 'layouts.admin')

@section('title', 'Reportes - Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 text-dark">
    <h2 class="fw-bold m-0" style="color: #333;">Reportes y Resumen</h2>
    <a href="{{ route('reports.pdf', request()->all()) }}" target="_blank" class="btn btn-outline-danger" id="btn-pdf-export">
        <i class="bi bi-file-earmark-pdf-fill"></i> Exportar PDF
    </a>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0 opacity-75">INGRESOS TOTALES</h6>
                    <i class="bi bi-cash-stack fs-4 opacity-50"></i>
                </div>
                <h3 class="fw-bold mb-0" id="total-earnings-display">${{ number_format($totalEarnings, 0, ',', '.') }}</h3>
                <small class="opacity-75">En el periodo seleccionado</small>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm bg-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0 text-secondary small fw-bold">CITAS FILTRADAS</h6>
                    <i class="bi bi-calendar-check fs-4 text-primary opacity-50"></i>
                </div>
                <h3 class="fw-bold text-dark mb-0" id="total-appointments-display">{{ $totalAppointments }}</h3>
                <small class="text-secondary">Citas encontradas</small>
            </div>
        </div>
    </div>
</div>

<div class="card bg-white border-0 shadow-sm mb-4">
    <div class="card-body">
        <form id="filter-form" class="row g-3 align-items-end">
            <!-- Status -->
            <div class="col-md-4">
                <label class="text-secondary small mb-1 fw-bold">ESTADO</label>
                <select name="status" id="status" class="form-select border-light shadow-sm text-dark bg-light" onchange="updateReports()">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Todas</option>
                    <option value="scheduled" {{ $status == 'scheduled' ? 'selected' : '' }}>Programadas</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completadas</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Canceladas</option>
                </select>
            </div>
            
            <!-- Barber -->
            <div class="col-md-4">
                <label class="text-secondary small mb-1 fw-bold">BARBERO</label>
                <select name="barber_id" id="barber_id" class="form-select border-light shadow-sm text-dark bg-light" onchange="updateReports()">
                    <option value="all">Todos</option>
                    @foreach($barbers as $barber)
                        <option value="{{ $barber->id }}" {{ request('barber_id') == $barber->id ? 'selected' : '' }}>
                            {{ $barber->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Date Range -->
            <div class="col-md-4">
                <label class="text-secondary small mb-1 fw-bold">RANGO DE FECHAS</label>
                <div class="input-group">
                    <input type="date" name="date_start" id="date_start" class="form-control border-light shadow-sm text-dark bg-light" 
                           value="{{ request('date_start') }}" onchange="updateReports()" onclick="this.showPicker()" style="cursor: pointer;">
                    <span class="input-group-text bg-white border-light text-secondary"><i class="bi bi-arrow-right"></i></span>
                    <input type="date" name="date_end" id="date_end" class="form-control border-light shadow-sm text-dark bg-light" 
                           value="{{ request('date_end') }}" onchange="updateReports()" onclick="this.showPicker()" style="cursor: pointer;">
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card bg-white border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary">
                <tr>
                    <th class="ps-4 py-3 border-0">Fecha</th>
                    <th class="py-3 border-0">Cliente</th>
                    <th class="py-3 border-0">Servicio</th>
                    <th class="py-3 border-0">Barbero</th>
                    <th class="py-3 border-0">Estado</th>
                    <th class="text-end pe-4 py-3 border-0">Precio</th>
                </tr>
            </thead>
            <tbody id="reports-table-body">
                @include('admin.reports.partials.table_rows', ['appointments' => $appointments])
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Initial Setup for Date Logic
    document.addEventListener('DOMContentLoaded', function() {
        const startInput = document.getElementById('date_start');
        const endInput = document.getElementById('date_end');

        function enforceDateRules() {
            if (startInput.value) {
                endInput.min = startInput.value;
                if (endInput.value && endInput.value < startInput.value) {
                    endInput.value = startInput.value;
                    // We might want to trigger updateReports if we changed the value programmatically
                    // checking if this causes a loop? updateReports is called onchange usually.
                    // But programmatic change doesn't trigger onchange.
                    updateReports();
                }
            } else {
                endInput.removeAttribute('min');
            }
        }

        // Run on load to set initial state
        enforceDateRules();

        // Run when start date changes
        startInput.addEventListener('change', function() {
            enforceDateRules(); 
            // Note: updateReports() is also called by param 'onchange' in HTML. 
            // This listener is specific for the EndDate constraints.
        });

        // Extra safety: When End date changes, double check (though min attribute should handle UI)
        endInput.addEventListener('change', function() {
            if (startInput.value && this.value && this.value < startInput.value) {
                this.value = startInput.value;
                // updateReports will run via HTML onchange attribute
            }
        });
    });

    function updateReports() {
        const status = document.getElementById('status').value;
        const barber_id = document.getElementById('barber_id').value;
        const date_start = document.getElementById('date_start').value;
        const date_end = document.getElementById('date_end').value;

        // Validation: If Start > End (and End is set), swap or warn?
        // The Min attribute logic handles prevention, but let's be safe.
        
        const params = { status, barber_id, date_start, date_end };

        // UI Loading State
        document.getElementById('reports-table-body').style.opacity = '0.5';

        axios.get('{{ route("reports.index") }}', { params })
            .then(response => {
                const data = response.data;
                document.getElementById('reports-table-body').innerHTML = data.html;
                document.getElementById('total-earnings-display').textContent = data.totalEarnings;
                document.getElementById('total-appointments-display').textContent = data.totalAppointments;
                
                // Update PDF Link
                const queryString = new URLSearchParams(params).toString();
                document.getElementById('btn-pdf-export').href = `{{ route('reports.pdf') }}?${queryString}`;
            })
            .catch(error => {
                console.error(error);
            })
            .finally(() => {
                document.getElementById('reports-table-body').style.opacity = '1';
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                  return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            });
    }
</script>
@endpush
@endsection
