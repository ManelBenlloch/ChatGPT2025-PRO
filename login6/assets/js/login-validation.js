/**
 * Validaciones para el formulario de login con jQuery Validate
 */

$(document).ready(function() {
    // Configurar jQuery Validate con mensajes en español
    $.validator.setDefaults({
        errorElement: 'span',
        errorClass: 'error-message',
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        highlight: function(element) {
            $(element).addClass('error-input');
        },
        unhighlight: function(element) {
            $(element).removeClass('error-input');
        }
    });

    // Validar formulario de login
    if ($('#loginForm').length) {
        $('#loginForm').validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 8
                }
            },
            messages: {
                email: {
                    required: 'El email es obligatorio',
                    email: 'Por favor, introduce un email válido'
                },
                password: {
                    required: 'La contraseña es obligatoria',
                    minlength: 'La contraseña debe tener al menos 8 caracteres'
                }
            },
            submitHandler: function(form) {
                // Verificar reCAPTCHA antes de enviar
                var recaptchaResponse = grecaptcha.getResponse();
                if (!recaptchaResponse) {
                    alert('Por favor, completa el reCAPTCHA.');
                    return false;
                }
                form.submit();
            }
        });
    }
});
