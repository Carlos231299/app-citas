<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura #{{ $sale->id }}</title>
    <style>
        @page { size: 80mm 200mm; margin: 0; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #1a1a1a; 
            margin: 0; 
            padding: 15px;
            width: 70mm;
            line-height: 1.4;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .logo-img { width: 60px; height: auto; margin-bottom: 10px; border-radius: 10px; }
        .business-name { font-size: 18px; font-weight: bold; color: #1e3a8a; letter-spacing: 1px; margin-bottom: 2px; }
        .business-tagline { font-size: 9px; color: #6b7280; font-style: italic; }
        
        .title-box { 
            background-color: #f8fafc; 
            border-top: 2px solid #1e3a8a; 
            border-bottom: 2px solid #1e3a8a; 
            padding: 8px 0; 
            margin: 15px 0; 
            text-align: center; 
        }
        .title { font-size: 12px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; }
        
        .info-section { margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; border-bottom: 1px solid #f1f5f9; padding-bottom: 2px; }
        .info-label { font-weight: bold; color: #4b5563; font-size: 9px; }
        .info-value { text-align: right; color: #111827; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { 
            text-align: left; 
            background: #1e3a8a; 
            color: white; 
            padding: 6px 4px; 
            font-size: 9px; 
            text-transform: uppercase; 
        }
        .items-table td { 
            padding: 8px 4px; 
            border-bottom: 1px solid #e5e7eb; 
            vertical-align: middle;
        }
        
        .total-container { 
            background: #1e3a8a; 
            color: white; 
            padding: 10px; 
            border-radius: 4px; 
            text-align: right; 
            margin-top: 10px;
        }
        .total-label { font-size: 10px; opacity: 0.9; }
        .total-amount { font-size: 16px; font-weight: bold; }
        
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #9ca3af; border-top: 1px dashed #e5e7eb; padding-top: 15px; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-blue { color: #1e3a8a; }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = public_path('images/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
            
            // Calculate Totals
            $servicePrice = 0;
            if($sale->appointment) {
                $servicePrice = $sale->appointment->price; // Use stored price (includes extra time)
            }
            
            $grandTotal = $sale->total + $servicePrice;
        @endphp
        
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
        @endif
        
        <div class="business-name">BARBERÍA JR</div>
        <div class="business-tagline">Excelencia en cada corte • Estilo Único</div>
    </div>

    <div class="title-box">
        <div class="title">Recibo de Pago #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="info-section">
        <div class="info-row"><span class="info-label">FECHA:</span> <span class="info-value">{{ $sale->created_at->format('d/m/Y h:i A') }}</span></div>
        <div class="info-row"><span class="info-label">CLIENTE:</span> <span class="info-value fw-bold text-blue">{{ $sale->client_name }}</span></div>
        <div class="info-row"><span class="info-label">BARBERO:</span> <span class="info-value">{{ $sale->appointment->barber->name ?? 'Sistema' }}</span></div>
        <div class="info-row"><span class="info-label">PAGO:</span> <span class="info-value text-capitalize">{{ $sale->payment_method }}</span></div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 55%;">Descripción</th>
                <th class="text-right" style="width: 15%;">Cant.</th>
                <th class="text-right" style="width: 30%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            {{-- Service from Appointment --}}
            @if($sale->appointment && $sale->appointment->service)
            <tr>
                <td style="font-size: 10px;" class="fw-bold">{{ $sale->appointment->service->name }}</td>
                <td class="text-right">1</td>
                <td class="text-right fw-bold">${{ number_format($servicePrice, 0) }}</td>
            </tr>
            @endif
            
            {{-- Products --}}
            @foreach($sale->items as $item)
            <tr>
                <td style="font-size: 10px;">{{ $item['product_name'] }}</td>
                <td class="text-right">{{ $item['quantity'] }}</td>
                <td class="text-right fw-bold">${{ number_format($item['subtotal'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-container">
        <div class="total-label">VALOR TOTAL PAGADO</div>
        <div class="total-amount">${{ number_format($grandTotal, 0) }}</div>
    </div>

    <div class="footer">
        <p class="fw-bold">¡Gracias por elegirnos!</p>
        <p>Este es un comprobante electrónico oficial de la Barbería JR.</p>
        <p>&copy; {{ date('Y') }} Barbería JR</p>
    </div>
</body>
</html>
