<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="signup-form-container">
        <!-- Encabezado del formulario -->
        <div class="form-header">
            <h1 class="form-title">Crear Cuenta</h1>
        </div>

        <!-- Cuerpo del formulario -->
        <div class="form-body">
            <!-- Mostrar mensajes de error -->
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <!-- Formulario de registro -->
            <form action="<?php echo asset('register'); ?>" method="POST" id="registerForm">
                <!-- Campo de nombre completo -->
                <div class="form-group">
                    <label for="fullname" class="form-label">Nombre Completo</label>
                    <input 
                        type="text" 
                        id="fullname" 
                        name="fullname" 
                        class="form-input" 
                        placeholder="Juan Pérez"
                        value="<?php echo htmlspecialchars($_SESSION['old_input']['fullname'] ?? ''); ?>"
                        required
                        autocomplete="name"
                    >
                </div>

                <!-- Campo de username -->
                <div class="form-group">
                    <label for="username" class="form-label">Nombre de Usuario</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="juanperez"
                        value="<?php echo htmlspecialchars($_SESSION['old_input']['username'] ?? ''); ?>"
                        required
                        autocomplete="username"
                    >
                </div>

                <!-- Campo de alias -->
                <div class="form-group">
                    <label for="alias" class="form-label">Alias</label>
                    <input 
                        type="text" 
                        id="alias" 
                        name="alias" 
                        class="form-input" 
                        placeholder="jp2024"
                        value="<?php echo htmlspecialchars($_SESSION['old_input']['alias'] ?? ''); ?>"
                        required
                        maxlength="50"
                    >
                </div>

                <!-- Campo de email -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="tu@email.com"
                        value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>"
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
                            autocomplete="new-password"
                            minlength="8"
                        >
                        <button type="button" class="password-toggle-button" onclick="togglePassword('password')">
                            <svg id="eye-icon-password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <small style="color: #6b7280; font-size: 0.875rem;">Mínimo 8 caracteres</small>
                </div>

                <!-- Campo de confirmación de contraseña -->
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                    <div class="password-field-wrapper">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input" 
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            minlength="8"
                        >
                        <button type="button" class="password-toggle-button" onclick="togglePassword('confirm_password')">
                            <svg id="eye-icon-confirm_password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Campo de alias -->
                <div class="form-group">
                    <label for="alias" class="form-label">Alias (opcional)</label>
                    <input 
                        type="text" 
                        id="alias" 
                        name="alias" 
                        class="form-input" 
                        placeholder="mi_alias"
                        value="<?php echo htmlspecialchars($_SESSION['old_input']['alias'] ?? ''); ?>"
                        pattern="[a-zA-Z0-9_-]{3,18}"
                        minlength="3"
                        maxlength="18"
                        autocomplete="off"
                    >
                    <small style="color: #6b7280; font-size: 0.875rem;">3-18 caracteres (letras, números, guiones y guiones bajos)</small>
                </div>

                <!-- Checkbox de términos y condiciones -->
                <div class="form-group">
                    <label class="checkbox-label" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input 
                            type="checkbox" 
                            id="terms" 
                            name="terms" 
                            required
                            style="width: auto; margin: 0;"
                        >
                        <span style="font-size: 0.875rem; color: #374151;">
                            Acepto los <a href="#" style="color: #4f46e5;">términos y condiciones</a>
                        </span>
                    </label>
                </div>

                <!-- reCAPTCHA -->
                <div class="form-group recaptcha-container">
                    <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY_REGISTER; ?>"></div>
                </div>

                <!-- Botón de submit -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Crear Cuenta</button>
                </div>
            </form>
        </div>

        <!-- Pie del formulario -->
        <div class="form-footer">
            <p>¿Ya tienes una cuenta? <a href="<?php echo asset('login'); ?>">Inicia sesión aquí</a></p>
        </div>
    </div>

    <?php unset($_SESSION['old_input']); ?>

    <script>
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

        /**
         * Validación del formulario
         */
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            const recaptchaResponse = grecaptcha.getResponse();

            // Validar contraseñas
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
                return false;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres.');
                return false;
            }

            // Validar términos
            if (!terms) {
                e.preventDefault();
                alert('Debes aceptar los términos y condiciones.');
                return false;
            }

            // Validar reCAPTCHA
            if (!recaptchaResponse) {
                e.preventDefault();
                alert('Por favor, completa el reCAPTCHA.');
                return false;
            }
        });
    </script>
    
    <!-- Validaciones del lado del cliente -->
    <!-- jQuery y validaciones -->
    <script src="<?php echo asset('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/jquery.validate.min.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/register-validation.js'); ?>"></script>
</body>
</html>
