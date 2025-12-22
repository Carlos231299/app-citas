import os

file_path = r'c:\Users\Carlos\Desktop\PRACTICAS PY\app citas\resources\views\admin\dashboard.blade.php'

# The NEW HTML content for the 3-card layout
new_content = """    <div class="row g-3 mb-4 animate-fade-in">
        @php
            $colClass = trim(auth()->user()->role) === 'admin' ? 'col-12 col-xl-5' : 'col-12 col-md-6';
        @endphp

        <!-- 1. RESUMEN CITAS HOY (Unified) -->
        <div class="{{ $colClass }}">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Citas Hoy</h6>
                            <h2 class="mb-0 fw-bold text-dark display-6">{{ $stats['total_today'] }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-calendar-check text-primary fs-3"></i>
                        </div>
                    </div>
                    
                    <!-- Breakdown -->
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="bg-success bg-opacity-10 p-2 rounded-3">
                                <small class="d-block text-success fw-bold" style="font-size: 0.7rem;">COMPLETADAS</small>
                                <span class="fw-bold text-dark">{{ $stats['completed_today'] }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-warning bg-opacity-10 p-2 rounded-3">
                                <small class="d-block text-warning fw-bold" style="font-size: 0.7rem;">PENDIENTES</small>
                                <span class="fw-bold text-dark">{{ $stats['pending_today'] }}</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-danger bg-opacity-10 p-2 rounded-3">
                                <small class="d-block text-danger fw-bold" style="font-size: 0.7rem;">CANCELADAS</small>
                                <span class="fw-bold text-dark">{{ $stats['cancelled_today'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. INGRESOS (Admin) -->
        @if(trim(auth()->user()->role) === 'admin')
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-success">
                <div class="card-body p-3 d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Ingresos Hoy</h6>
                            <h2 class="mb-0 fw-bold text-dark display-6">${{ number_format($stats['revenue_today'], 0) }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-currency-dollar text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- 3. BARBEROS (Admin) -->
        @if(trim(auth()->user()->role) === 'admin')
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm bg-white h-100 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-secondary text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Barberos</h6>
                            <h3 class="mb-0 fw-bold text-dark">{{ $barbers->where('is_active', true)->count() }} <span class="text-muted fs-6 fw-normal">/ {{ $barbers->count() }}</span></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-4">
                            <i class="bi bi-people text-info fs-3"></i>
                        </div>
                    </div>

                    <div class="d-flex gap-1 overflow-visible">
                         @foreach($barbers->take(5) as $barber)
                            @php
                                $isActive = $barber->is_active;
                                $isSpecial = $barber->special_mode ?? false;
                                $rawAvatar = $barber->user->avatar;
                                $isImage = $rawAvatar && (
                                    str_starts_with($rawAvatar, 'users/') || 
                                    str_ends_with(strtolower($rawAvatar), '.jpg') || 
                                    str_ends_with(strtolower($rawAvatar), '.jpeg') || 
                                    str_ends_with(strtolower($rawAvatar), '.png') || 
                                    str_ends_with(strtolower($rawAvatar), '.webp')
                                );
                                $initials = substr($barber->name, 0, 1);
                            @endphp

                            <div class="position-relative" data-bs-toggle="tooltip" title="{{ $barber->name }} {{ $isActive ? '(Activo)' : ($isSpecial ? '(Horario Extra)' : '(Inactivo)') }}">
                                @if($rawAvatar && $isImage)
                                    <img src="{{ asset('storage/' . $rawAvatar) }}" class="rounded-circle border border-2 {{ $isActive ? 'border-success' : 'border-secondary' }}" style="width: 35px; height: 35px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle border border-2 {{ $isActive ? 'border-success' : 'border-secondary' }} bg-light d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 35px; height: 35px;">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <span class="position-absolute bottom-0 end-0 p-1 bg-{{ $isActive ? 'success' : 'secondary' }} border border-light rounded-circle" style="transform: scale(0.6);"></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>"""

try:
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Define the markers to find the block
    start_marker = '<div class="row g-2 mb-4 animate-fade-in">'
    
    # We will find the start, and then find the closing div of this row.
    # Since it's hard to count divs with regex, we'll assume the NEXT comment "<!-- Calendar Container -->" is the safe end point, 
    # and strip the whitespace back to the last closing div.
    
    # Or better, we know the previous content structure from the `view_file`.
    # Let's target the exact string from the failed replace tool if we can... no, that failed.
    
    # Let's find the Start Marker
    start_idx = content.find(start_marker)
    if start_idx == -1:
        raise Exception("Start marker not found!")

    # Find the End Marker: The Calendar Container comment
    end_marker = "<!-- Calendar Container -->"
    end_idx = content.find(end_marker)
    
    if end_idx == -1:
         # Fallback search if comment is missing
         raise Exception("End marker not found!")
    
    # Extract the chunk to be replaced (from start of row to just before Calendar)
    # We need to be careful not to delete the Calendar Container comment itself, but delete the </div> closing the row before it.
    
    # The structure is:
    # <div class="row ..."> ... </div> [whitespace] <!-- Calendar Container -->
    
    # So we replace from start_idx up to end_idx.
    # AND we verify that new_content ends with a closing div?
    # No, new_content HAS the closing div for the row.
    
    # So:
    updated_content = content[:start_idx] + new_content + "\n\n    " + content[end_idx:]
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(updated_content)

    print("SUCCESS: Dashboard file updated successfully.")

except Exception as e:
    print(f"ERROR: {e}")
