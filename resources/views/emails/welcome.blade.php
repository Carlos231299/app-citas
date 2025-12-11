<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #1a1a1a; padding: 40px 20px; color: #ffffff; }
        .container { max-width: 600px; margin: 0 auto; background: #000000; padding: 40px; border-radius: 8px; border: 1px solid #c5a964; text-align: center; }
        h1 { color: #c5a964; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px; }
        p { color: #cccccc; line-height: 1.6; font-size: 16px; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 12px 30px; background-color: #c5a964; color: #000000; text-decoration: none; font-weight: bold; border-radius: 4px; text-transform: uppercase; }
        .footer { margin-top: 40px; font-size: 12px; color: #666666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Bienvenido a la Familia!</h1>
        <p>Hola {{ $userName }},</p>
        <p>Gracias por registrarte en <strong>Barbería JR</strong>. Estamos listos para brindarte el mejor estilo y atención.</p>
        <p>Ya puedes acceder a tu cuenta para gestionar tus citas.</p>
        
        <a href="{{ route('login') }}" class="btn">Iniciar Sesión</a>
        
        <div class="footer">
            &copy; {{ date('Y') }} Barbería JR. Estilo y Calidad.
        </div>
    </div>
</body>
</html>
