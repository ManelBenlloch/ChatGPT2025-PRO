<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="login-container">
        <!-- Encabezado del formulario -->
        <div class="form-header">
            <h1 class="form-title">Iniciar Sesión</h1>
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

            <!-- Formulario de login -->
            <form action="<?php echo asset('login'); ?>" method="POST" id="loginForm">
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

                <!-- Campo de contraseña -->
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="password-field-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle-button" onclick="togglePassword('password')">
                            <svg id="eye-icon-password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- reCAPTCHA -->
                <div class="form-group recaptcha-container">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY_LOGIN; ?>"></div>
                </div>

                <!-- Botón de submit -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>

                <!-- Enlace a reseteo de contraseña -->
                <div class="text-center mt-2">
                    <a href="<?php echo asset('reset-password'); ?>">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>

        <!-- Pie del formulario -->
        <div class="form-footer">
            <p>¿No tienes una cuenta? <a href="<?php echo asset('register'); ?>">Regístrate aquí</a></p>
        </div>
    </div>

    <script>
        /**
         * Validar reCAPTCHA antes de enviar el formulario
         */
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                e.preventDefault();
                alert('Por favor, completa el reCAPTCHA.');
                return false;
            }
        });

        /**
         * Función para mostrar/ocultar la contraseña
         */
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById('eye-icon-' + fieldId);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            } else {
                field.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
    
    <!-- jQuery y validaciones -->
    <script src="<?php echo asset('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/jquery.validate.min.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/login-validation.js'); ?>"></script>
</body>
</html>
