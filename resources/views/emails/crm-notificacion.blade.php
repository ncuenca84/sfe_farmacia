<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #1e3a5f, #2563eb); padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 600; }
        .header p { color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 13px; }
        .body { padding: 32px; }
        .body h2 { font-size: 18px; color: #1f2937; margin-top: 0; }
        .body p { margin-bottom: 12px; color: #4b5563; }
        .body ul { color: #4b5563; }
        .footer { padding: 20px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SistemSFE</h1>
            <p>Sistema de Facturación Electrónica</p>
        </div>
        <div class="body">
            {!! $mensajeHtml !!}
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SistemSFE. Todos los derechos reservados.</p>
            <p>Este es un mensaje automático, por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
