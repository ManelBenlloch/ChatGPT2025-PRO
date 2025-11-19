/**
 * validations.js
 * 
 * Validaciones del Lado del Cliente
 * 
 * Proporciona validación en tiempo real de formularios para mejorar
 * la experiencia del usuario.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================================================
    // VALIDACIÓN DE FORMULARIO DE REGISTRO
    // ========================================================================
    
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Verificar que las contraseñas coincidan
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
                confirmPassword.focus();
                return false;
            }
            
            // Verificar longitud mínima de contraseña
            if (password && password.value.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres.');
                password.focus();
                return false;
            }
        });
        
        // Validación en tiempo real de contraseñas
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                const password = document.getElementById('password');
                if (password && this.value && password.value !== this.value) {
                    this.setCustomValidity('Las contraseñas no coinciden');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }
    
    // ========================================================================
    // TOGGLE DE VISIBILIDAD DE CONTRASEÑA
    // ========================================================================
    
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput) {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
                } else {
                    passwordInput.type = 'password';
                    this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
                }
            }
        });
    });
    
    // ========================================================================
    // VALIDACIÓN DE EMAIL EN TIEMPO REAL
    // ========================================================================
    
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.setCustomValidity('Por favor, introduce un email válido');
            } else {
                this.setCustomValidity('');
            }
        });
    });
    
    // ========================================================================
    // AUTO-CIERRE DE ALERTAS
    // ========================================================================
    
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        // Auto-cerrar después de 5 segundos
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
    // ========================================================================
    // PREVENIR ENVÍO MÚLTIPLE DE FORMULARIOS
    // ========================================================================
    
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Procesando...';
                
                // Re-habilitar después de 3 segundos por si hay error
                setTimeout(function() {
                    submitButton.disabled = false;
                    submitButton.textContent = submitButton.getAttribute('data-original-text') || 'Enviar';
                }, 3000);
            }
        });
    });
});
