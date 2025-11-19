/**
 * Validaciones para el formulario de registro con jQuery Validate
 */

$(document).ready(function() {
    // Configurar jQuery Validate con mensajes en español
    $.validator.setDefaults({
        errorElement: 'span',
        errorClass: 'error-message',
        errorPlacement: function(error, element) {
            if (element.attr('type') === 'checkbox') {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element) {
            $(element).addClass('error-input');
        },
        unhighlight: function(element) {
            $(element).removeClass('error-input');
        }
    });

    // Método personalizado para validar que las contraseñas coincidan
    $.validator.addMethod('passwordMatch', function(value, element) {
        return value === $('#password').val();
    }, 'Las contraseñas no coinciden');

    // Método personalizado para validar el alias
    $.validator.addMethod('validAlias', function(value, element) {
        if (value === '') return true; // Opcional
        return /^[a-zA-Z0-9_-]{3,18}$/.test(value);
    }, 'El alias debe tener entre 3 y 18 caracteres (letras, números, guiones y guiones bajos)');

    // Validar formulario de registro
    if ($('#registerForm').length) {
        $('#registerForm').validate({
            rules: {
                fullname: {
                    required: true,
                    minlength: 3,
                    maxlength: 255
                },
                username: {
                    required: true,
                    minlength: 3,
                    maxlength: 100
                },
                alias: {
                    validAlias: true
                },
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 8
                },
                confirm_password: {
                    required: true,
                    minlength: 8,
                    passwordMatch: true
                },
                terms: {
                    required: true
                }
            },
            messages: {
                fullname: {
                    required: 'El nombre completo es obligatorio',
                    minlength: 'El nombre debe tener al menos 3 caracteres',
                    maxlength: 'El nombre no puede exceder 255 caracteres'
                },
                username: {
                    required: 'El nombre de usuario es obligatorio',
                    minlength: 'El nombre de usuario debe tener al menos 3 caracteres',
                    maxlength: 'El nombre de usuario no puede exceder 100 caracteres'
                },
                email: {
                    required: 'El email es obligatorio',
                    email: 'Por favor, introduce un email válido'
                },
                password: {
                    required: 'La contraseña es obligatoria',
                    minlength: 'La contraseña debe tener al menos 8 caracteres'
                },
                confirm_password: {
                    required: 'Debes confirmar la contraseña',
                    minlength: 'La contraseña debe tener al menos 8 caracteres'
                },
                terms: {
                    required: 'Debes aceptar los términos y condiciones'
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
