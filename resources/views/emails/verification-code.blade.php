<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #2d3748; text-align: center; margin: 20px 0; padding: 15px; background: #edf2f7; border-radius: 4px; }
        .footer { font-size: 12px; color: #718096; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color: #4a5568; text-align: center;">Recuperación de Contraseña</h2>
        <p>Hola,</p>
        <p>Has solicitado restablecer tu contraseña en <strong>Barbería JR</strong>. Usa el siguiente código para continuar:</p>
        
        <div class="code">{{ $code }}</div>
        
        <p>Este código expirará en 15 minutos.</p>
        <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} Barbería JR. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
