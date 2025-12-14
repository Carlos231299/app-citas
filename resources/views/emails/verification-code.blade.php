<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #374151; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f3f4f6; padding-bottom: 40px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-top: 40px; }
        .header { background-color: #1a1d20; padding: 30px 20px; text-align: center; border-bottom: 2px solid #c5a964; }
        .header img { max-height: 80px; width: auto; }
        .content { padding: 40px 30px; text-align: center; }
        h1 { color: #111827; font-size: 24px; font-weight: 700; margin: 0 0 20px; letter-spacing: -0.5px; }
        p { font-size: 16px; line-height: 1.6; color: #4b5563; margin: 0 0 20px; }
        
        .code-box { background-color: #f3f4f6; border: 2px dashed #9ca3af; border-radius: 8px; padding: 20px; margin: 30px 0; display: inline-block; }
        .code { font-family: 'Courier New', monospace; font-size: 36px; font-weight: 800; color: #1a1d20; letter-spacing: 8px; display: block; }
        
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .text-gold { color: #c5a964; font-weight: bold; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <!-- Ideally use absolute URL or embedded CID if asset helper fails in mail, but asset usually works if APP_URL is set -->
                <img src="{{ asset('images/logo.png') }}" alt="Barbería JR">
            </div>

            <!-- Content -->
            <div class="content">
                <h1>Código de Verificación</h1>
                <p>Hola,</p>
                <p>Para continuar con el inicio de sesión, ingresa el siguiente código:</p>
                
                <div class="code-box">
                    <span class="code">{{ $code }}</span>
                </div>

                <p>Este código expira en <span class="text-gold">10 minutos</span>.</p>
                <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
                    Si no intentaste iniciar sesión, puedes ignorar este mensaje de forma segura.
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                &copy; {{ date('Y') }} Barbería JR. Todos los derechos reservados.
            </div>
        </div>
    </div>
</body>
</html>
