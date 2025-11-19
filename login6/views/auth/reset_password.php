<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetear Contraseña - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
</head>
<body>
    <div class="login-container">
        <!-- Encabezado del formulario -->
        <div class="form-header">
            <h1 class="form-title">Resetear Contraseña</h1>
        </div>

        <!-- Cuerpo del formulario -->
        <div class="form-body">
            <!-- Mostrar mensajes de error -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Mostrar mensajes de éxito -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success']); 
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <p style="color: #6b7280; margin-bottom: 20px;">
                Introduce tu email y te enviaremos un enlace para resetear tu contraseña.
            </p>

            <!-- Formulario de reseteo -->
            <form action="<?php echo asset('reset-password'); ?>" method="POST" id="resetForm">
                <!-- Campo de email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="tu@email.com"
                        required
                        autocomplete="email"
                    >
                </div>

                <!-- Botón de submit -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Enviar Enlace de Reseteo</button>
                </div>
            </form>
        </div>

        <!-- Pie del formulario -->
        <div class="form-footer">
            <p>¿Recordaste tu contraseña? <a href="<?php echo asset('login'); ?>">Inicia sesión aquí</a></p>
        </div>
    </div>
</body>
</html>
