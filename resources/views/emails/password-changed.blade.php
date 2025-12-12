<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #374151; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f3f4f6; padding-bottom: 40px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-top: 40px; }
        .header { background-color: #ffffff; padding: 30px 20px; text-align: center; border-bottom: 2px solid #f3f4f6; }
        .header img { max-height: 60px; width: auto; }
        .content { padding: 40px 30px; text-align: center; }
        h1 { color: #111827; font-size: 24px; font-weight: 700; margin: 0 0 20px; letter-spacing: -0.5px; }
        p { font-size: 16px; line-height: 1.6; color: #4b5563; margin: 0 0 20px; }
        
        .alert-success { background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; text-align: left; margin: 30px 0; border-radius: 4px; }
        .alert-text { font-size: 14px; color: #047857; font-weight: 600; margin: 0; }
        
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .btn { background-color: #374151; color: #ffffff !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <img src="{{ asset('images/logo.png') }}" alt="Barbería JR">
            </div>

            <!-- Content -->
            <div class="content">
                <h1>¡Contraseña Actualizada!</h1>
                <p>Te confirmamos que tu contraseña ha sido cambiada exitosamente.</p>
                
                <p style="color: #ef4444; font-weight: bold; margin-bottom: 20px;">
                    Si no fuiste tú quien o el Administrador realizó este cambio, por favor restablezca su contraseña de inmediato.
                </p>

                <div style="margin-top: 30px;">
                    <a href="{{ route('login') }}" class="btn" style="margin-right: 10px;">Iniciar Sesión</a>
                    <a href="{{ route('password.request') }}" class="btn" style="background-color: #ef4444;">Restablecer Contraseña</a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                &copy; {{ date('Y') }} Barbería JR. Todos los derechos reservados.
            </div>
        </div>
    </div>
</body>
</html>
