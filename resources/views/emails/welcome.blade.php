<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #1a1a1a; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #000000; padding: 40px; border-radius: 8px; border: 1px solid #c5a964; text-align: center; color: #ffffff; }
        .header img { max-height: 80px; margin-bottom: 20px; }
        h1 { color: #c5a964; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px; font-size: 24px; }
        p { color: #cccccc; line-height: 1.6; font-size: 16px; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 14px 35px; background-color: #c5a964; color: #000000; text-decoration: none; font-weight: bold; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px; }
        .btn:hover { background-color: #b09355; }
        .footer { margin-top: 40px; font-size: 12px; color: #666666; border-top: 1px solid #333; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Use absolute URL for email images -->
            <img src="{{ asset('images/logo.png') }}" alt="Barbería JR">
        </div>
        <h1>¡Bienvenido a la Familia!</h1>
        <p>Hola <strong>{{ $userName }}</strong>,</p>
        <p>Gracias por unirte a <strong>Barbería JR</strong>. Estamos listos para ofrecerte la mejor experiencia y el mejor estilo.</p>
        <p>Ya puedes acceder a tu cuenta para gestionar tus citas de manera fácil y rápida.</p>
        
        <a href="{{ route('login') }}" class="btn">Iniciar Sesión</a>
        
        <div class="footer">
            &copy; {{ date('Y') }} Barbería JR. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
