<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte Financiero - Barbería JR</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: 'Helvetica', 'Arial', sans-serif; color: #1e293b; background: #fff; }
        
        .header-stripe { height: 10px; background: #4F46E5; width: 100%; }
        .container { padding: 40px 50px; }
        
        /* Table Layout for Header to be DomPDF safe */
        .header-table { width: 100%; margin-bottom: 40px; }
        .header-table td { border: none; padding: 0; vertical-align: top; }
        
        .logo h1 { margin: 0; font-size: 28px; color: #0F172A; text-transform: uppercase; letter-spacing: 1px; }
        .logo p { margin: 5px 0 0; color: #64748B; font-size: 14px; }
        
        .report-info { text-align: right; }
        .report-label { color: #64748B; font-size: 10px; text-transform: uppercase; font-weight: bold; margin-bottom: 2px; }
        .report-value { font-size: 14px; font-weight: 600; margin-bottom: 10px; color: #0F172A; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; font-size: 12px; }
        table.data-table th { background: #F8FAFC; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; font-size: 10px; font-weight: 700; padding: 10px 12px; text-align: left; border-bottom: 2px solid #E2E8F0; }
        table.data-table td { padding: 10px 12px; border-bottom: 1px solid #E2E8F0; color: #334155; }
        table.data-table tr:last-child td { border-bottom: none; }
        
        .status-badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .status-scheduled { background: #EFF6FF; color: #3B82F6; }
        .status-completed { background: #DCFCE7; color: #166534; }
        .status-cancelled { background: #FEF2F2; color: #B91C1C; }
        
        .summary-box { background: #F8FAFC; padding: 20px; border-radius: 8px; float: right; width: 250px; }
        .summary-row { padding-bottom: 8px; margin-bottom: 8px; border-bottom: 1px solid #E2E8F0; font-size: 13px; color: #64748B; }
        .summary-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .summary-row span { display: inline-block; }
        .summary-row span:first-child { width: 50%; }
        .summary-row span:last-child { width: 45%; text-align: right; font-weight: bold; color: #0F172A; }
        
        .footer { position: fixed; bottom: 40px; left: 50px; right: 50px; text-align: center; color: #94A3B8; font-size: 10px; border-top: 1px solid #E2E8F0; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header-stripe"></div>
    
    <div class="container">
        
        <table class="header-table">
            <tr>
                <td>
                    <div class="logo">
                        <h1>Barbería JR</h1>
                        <p>Reporte de Actividad y Finanzas</p>
                    </div>
                </td>
                <td style="text-align: right;">
                    <div class="report-info">
                        <div class="report-label">Fecha de Emisión</div>
                        <div class="report-value">{{ now()->format('d M, Y') }}</div>
                        
                        <div class="report-label">Filtro Estado</div>
                        <div class="report-value">
                            @if($status == 'all') Todos
                            @elseif($status == 'scheduled') Programadas
                            @elseif($status == 'completed') Completadas
                            @else {{ ucfirst($status) }}
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Profesional</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Precio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $appointment)
                <tr>
                    <td>
                        <span style="font-weight: 600;">{{ $appointment->scheduled_at->format('d/m/Y') }}</span><br>
                        <span style="color: #94A3B8; font-size: 10px;">{{ $appointment->scheduled_at->format('g:i A') }}</span>
                    </td>
                    <td>{{ $appointment->client_name }}</td>
                    <td>{{ $appointment->service->name }}</td>
                    <td>{{ $appointment->barber->name }}</td>
                    <td>
                        <span class="status-badge status-{{ $appointment->status }}">
                            {{ $appointment->status_label }}
                        </span>
                    </td>
                    <td style="text-align: right; font-family: monospace;">
                        ${{ number_format($appointment->confirmed_price ?? $appointment->service->price, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-row">
                <span>Citas Reportadas</span>
                <span>{{ $appointments->count() }}</span>
            </div>
            <div class="summary-row" style="font-size: 16px; color: #0F172A;">
                <span>Total Ingresos</span>
                <span>${{ number_format($total, 0, ',', '.') }}</span>
            </div>
        </div>
        
        <div class="footer">
            Reporte generado automáticamente por Barbería JR System.
        </div>
    </div>
</body>
</html>
