@forelse($appointments as $appointment)
<tr>
    <td class="ps-4">
        <div class="fw-bold text-dark">{{ $appointment->scheduled_at->format('d M, Y') }}</div>
        <small class="text-muted">{{ $appointment->scheduled_at->format('g:i A') }}</small>
    </td>
    <td>
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;">
                <i class="bi bi-person-fill"></i>
            </div>
            <span class="text-dark fw-medium">{{ $appointment->client_name }}</span>
        </div>
    </td>
    <td class="text-secondary">{{ $appointment->service->name ?? 'N/A' }}</td>
    <td class="text-secondary">{{ $appointment->barber->name ?? 'Cualquiera' }}</td>
    <td>
        @if($appointment->status == 'scheduled')
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">{{ $appointment->status_label }}</span>
        @elseif($appointment->status == 'completed')
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ $appointment->status_label }}</span>
        @else
            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ $appointment->status_label }}</span>
        @endif
    </td>
    <td class="text-end pe-4 text-dark fw-bold">
        @if($appointment->status === 'completed' && $appointment->confirmed_price)
            ${{ number_format($appointment->confirmed_price, 0, ',', '.') }}
            @if($appointment->confirmed_price != $appointment->service->price)
                <i class="bi bi-info-circle-fill text-warning small ms-1" data-bs-toggle="tooltip" title="Precio ajustado (Base: {{ number_format($appointment->service->price ?? 0) }})"></i>
            @endif
        @else
            ${{ number_format($appointment->service->price ?? 0, 0, ',', '.') }}
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 mb-2 d-block opacity-25"></i>
        No se encontraron citas.
    </td>
</tr>
@endforelse
