<?php
/**
 * 2fa_challenge.php
 * 
 * Vista de Verificación de 2FA durante el Login
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación 2FA - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Verificación de Dos Factores</h1>
            <p>Introduce el código de 6 dígitos de tu aplicación de autenticación</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo asset('2fa/validate'); ?>" class="login-form">
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
                    style="text-align: center; font-size: 2rem; letter-spacing: 8px; font-weight: 600;"
                >
                <small class="form-hint">Abre Google Authenticator y copia el código de 6 dígitos</small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Verificar Código
            </button>
        </form>

        <div class="login-footer" style="margin-top: 20px;">
            <p>¿Problemas con el código? <a href="<?php echo asset('login'); ?>" class="text-link">Volver al login</a></p>
        </div>
    </div>

    <script src="<?php echo asset('assets/js/validations.js'); ?>"></script>
    <script>
        // Auto-focus en el campo de código
        document.getElementById('code').focus();

        // Permitir solo números en el campo de código
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Auto-submit cuando se completan los 6 dígitos
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
