<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #ddd; padding-bottom: 20px; }
        .logo { width: 80px; height: auto; margin-bottom: 10px; }
        h1 { margin: 0; color: #1e3a8a; /* Dark Blue institutional */ text-transform: uppercase; letter-spacing: 1px; }
        .meta { margin-top: 5px; color: #666; font-size: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8f9fa; text-transform: uppercase; font-size: 10px; padding: 10px; border-bottom: 2px solid #1e3a8a; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        
        .total-row td { border-top: 2px solid #1e3a8a; font-weight: bold; font-size: 14px; background: #fff; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 9px; text-transform: capitalize; }
        .bg-cash { background: #e8f5e9; color: #2e7d32; }
        .bg-card { background: #e3f2fd; color: #1565c0; }
        .bg-other { background: #f5f5f5; color: #616161; }
        
        .footer { position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Barbería JR</h1>
        <div class="meta">Reporte de Ventas generado el {{ now()->format('d/m/Y h:i A') }}</div>
        @if(request('date_from') || request('date_to'))
            <div class="meta">
                Periodo: 
                {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') : 'Inicio' }} 
                - 
                {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') : 'Fin' }}
            </div>
        @endif
        <div class="meta">Generado por: {{ auth()->user()->name }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Vendedor</th>
                <th>Método</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($sales as $sale)
            @php $grandTotal += $sale->total; @endphp
            <tr>
                <td>{{ $sale->created_at->format('d/m/Y - h:i A') }}</td>
                <td>{{ $sale->user ? $sale->user->name : 'N/A' }}</td>
                <td>
                    <span class="badge {{ $sale->payment_method == 'efectivo' ? 'bg-cash' : ($sale->payment_method == 'tarjeta' ? 'bg-card' : 'bg-other') }}">
                        {{ $sale->payment_method }}
                    </span>
                </td>
                <td style="text-align: right;">${{ number_format($sale->total, 0) }}</td>
            </tr>
            @endforeach
            
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">GRAN TOTAL:</td>
                <td style="text-align: right;">${{ number_format($grandTotal, 0) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Barbería JR - Reporte interno - {{ now()->format('Y') }}
    </div>

</body>
</html>
