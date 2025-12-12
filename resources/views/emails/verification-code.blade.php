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
        .code-box { background-color: #f9fafb; border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; margin: 30px 0; display: inline-block; min-width: 200px; }
        .code { font-size: 32px; font-family: 'Courier New', Courier, monospace; font-weight: 800; color: #2563eb; letter-spacing: 6px; display: block; }
        .alert { background-color: #fff1f2; border-left: 4px solid #e11d48; padding: 15px; text-align: left; margin: 30px 0; border-radius: 4px; }
        .alert-text { font-size: 14px; color: #9f1239; font-weight: 600; margin: 0; }
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header with Logo -->
            <div class="header">
                <img src="{{ asset('images/logo.png') }}" alt="Barbería JR">
            </div>

            <!-- Main Content -->
            <div class="content">
                <h1>Verifica tu Identidad</h1>
                <p>Usa el siguiente código de seguridad para completar tu proceso. No lo compartas con nadie.</p>
                
                <div class="code-box">
                    <span class="code">{{ $code }}</span>
                </div>

                <!-- Critical Info (Body) -->
                <div class="alert">
                    <p class="alert-text">⚠️ <strong>Importante:</strong> Este código expira en 5 minutos.</p>
                    <p class="alert-text" style="font-weight: 400; margin-top: 5px;">Si no solicitaste este código, puedes ignorar este mensaje o contactar a soporte.</p>
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
