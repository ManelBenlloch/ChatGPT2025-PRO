<?php
/**
 * 2fa_setup.php
 * 
 * Vista de Configuración de Autenticación de Dos Factores (2FA)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar 2FA - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Configurar Autenticación de Dos Factores</h1>
            <p>Escanea el código QR con Google Authenticator</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="2fa-setup-container" style="text-align: center; padding: 20px;">
            <div class="qr-code" style="margin: 20px 0;">
                <img src="<?php echo htmlspecialchars($qrCodeUrl); ?>" alt="Código QR para 2FA" style="max-width: 300px; border: 2px solid #e5e7eb; border-radius: 8px; padding: 10px;">
            </div>

            <div class="secret-key" style="margin: 20px 0; padding: 15px; background-color: #f9fafb; border-radius: 8px;">
                <p style="margin: 0 0 10px 0; font-weight: 600; color: #374151;">Clave Secreta (manual):</p>
                <code style="font-size: 1.1rem; color: #4f46e5; font-weight: 600; letter-spacing: 2px;"><?php echo htmlspecialchars($secret); ?></code>
            </div>

            <div class="instructions" style="margin: 20px 0; text-align: left; padding: 15px; background-color: #eff6ff; border-left: 4px solid #4f46e5; border-radius: 4px;">
                <h3 style="margin: 0 0 10px 0; color: #1e40af;">Instrucciones:</h3>
                <ol style="margin: 0; padding-left: 20px; color: #1e3a8a;">
                    <li>Descarga Google Authenticator en tu móvil (iOS/Android)</li>
                    <li>Abre la aplicación y toca "Escanear código QR"</li>
                    <li>Escanea el código QR mostrado arriba</li>
                    <li>Introduce el código de 6 dígitos que aparece en la app</li>
                </ol>
            </div>
        </div>

        <form method="POST" action="<?php echo asset('2fa/verify'); ?>" class="login-form">
            <div class="form-group">
                <label for="code" class="form-label">Código de Verificación</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    class="form-input" 
                    placeholder="123456"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    required
                    autocomplete="off"
                    style="text-align: center; font-size: 1.5rem; letter-spacing: 5px; font-weight: 600;"
                >
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Verificar y Activar 2FA
            </button>
        </form>

        <div class="login-footer" style="margin-top: 20px;">
            <a href="<?php echo asset('dashboard'); ?>" class="text-link">Cancelar y volver al dashboard</a>
        </div>
    </div>

    <script src="<?php echo asset('assets/js/validations.js'); ?>"></script>
    <script>
        // Auto-focus en el campo de código
        document.getElementById('code').focus();

        // Permitir solo números en el campo de código
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
