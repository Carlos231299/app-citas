<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Venta #{{ $sale->id }}</title>
    <style>
        @page { size: 80mm 200mm; margin: 0; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 10px; 
            color: #333; 
            margin: 0; 
            padding: 10px;
            width: 70mm;
        }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px; }
        .logo { font-size: 16px; font-weight: bold; color: #1e3a8a; margin-bottom: 2px; }
        .sub-header { font-size: 8px; color: #666; }
        .title { font-size: 12px; font-weight: bold; margin: 10px 0; border-top: 1px dashed #ddd; border-bottom: 1px dashed #ddd; padding: 5px 0; text-align: center; }
        
        .info-table { width: 100%; margin-bottom: 10px; font-size: 9px; }
        .info-table td { padding: 2px 0; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { text-align: left; border-bottom: 1px solid #333; padding: 5px 0; font-size: 8px; text-transform: uppercase; }
        .items-table td { padding: 5px 0; border-bottom: 1px solid #eee; }
        
        .total-section { border-top: 2px solid #333; padding-top: 10px; text-align: right; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .grand-total { font-size: 14px; font-weight: bold; color: #1e3a8a; }
        
        .footer { text-align: center; margin-top: 20px; font-size: 8px; color: #888; border-top: 1px dashed #ddd; padding-top: 10px; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">BARBERÍA JR</div>
        <div class="sub-header">Experiencia y Estilo</div>
        <div class="sub-header">Gracias por tu preferencia</div>
    </div>

    <div class="title">COMPROBANTE DE PAGO #{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>

    <table class="info-table">
        <tr>
            <td class="fw-bold">Fecha:</td>
            <td class="text-right">{{ $sale->created_at->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Cliente:</td>
            <td class="text-right">{{ $sale->client_name ?? 'Cliente Genérico' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Atendido por:</td>
            <td class="text-right">{{ $sale->user->name ?? 'Sistema' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Método:</td>
            <td class="text-right text-capitalize">{{ $sale->payment_method }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 60%;">Descripción</th>
                <th class="text-right" style="width: 15%;">Cant.</th>
                <th class="text-right" style="width: 25%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item['product_name'] }}</td>
                <td class="text-right">{{ $item['quantity'] }}</td>
                <td class="text-right">${{ number_format($item['subtotal'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="grand-total">TOTAL: ${{ number_format($sale->total, 0) }}</div>
    </div>

    <div class="footer">
        <p>Este es un comprobante digital de compra.</p>
        <p>JR BARBERÍA &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
