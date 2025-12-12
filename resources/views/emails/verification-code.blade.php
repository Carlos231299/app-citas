<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #1a1a1a; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #000000; padding: 40px; border-radius: 8px; border: 1px solid #c5a964; text-align: center; color: #ffffff; }
        .header img { max-height: 80px; margin-bottom: 20px; }
        h1 { color: #c5a964; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 2px; font-size: 24px; }
        .code { font-size: 36px; letter-spacing: 8px; color: #c5a964; font-weight: bold; margin: 30px 0; display: block; }
        p { color: #cccccc; line-height: 1.6; font-size: 16px; margin-bottom: 20px; }
        .footer { margin-top: 40px; font-size: 12px; color: #666666; border-top: 1px solid #333; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Barbería JR">
        </div>
        <h1>Código de Verificación</h1>
        <p>Estás intentando restablecer tu contraseña. Usa el siguiente código para continuar:</p>
        
        <span class="code">{{ $code }}</span>
        
        <p style="color: #ffffff; font-size: 16px; font-weight: bold; margin-bottom: 30px;">
            ⚠️ Este código expira en 5 minutos.
        </p>
        
        <p style="font-size: 14px; color: #999;">Si no solicitaste este cambio, por favor ignora este correo.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} Barbería JR. Seguridad y Estilo.
        </div>
    </div>
</body>
</html>
