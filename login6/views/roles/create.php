<?php
/**
 * Vista: Crear Rol
 */
require_once app_path('views/partials/header.php');
?>

<div class="container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
    <div class="page-header" style="margin-bottom: 30px;">
        <h1 style="margin: 0; color: #1f2937;">Crear Nuevo Rol</h1>
        <p style="margin: 10px 0 0; color: #6b7280;">Define un nuevo rol personalizado con permisos específicos</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            <strong>✗</strong> <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="form-container" style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <form method="POST" action="<?= asset('roles/store') ?>">
            <div class="form-group" style="margin-bottom: 24px;">
                <label for="name" style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                    Nombre del Rol (identificador) <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       required 
                       pattern="[a-z_]+" 
                       placeholder="ej: editor_contenido"
                       style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
                <small style="color: #6b7280; font-size: 13px; display: block; margin-top: 5px;">
                    Solo letras minúsculas y guiones bajos. Este nombre se usará internamente.
                </small>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="display_name" style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                    Nombre para Mostrar <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" 
                       id="display_name" 
                       name="display_name" 
                       required 
                       placeholder="ej: Editor de Contenido"
                       style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
                <small style="color: #6b7280; font-size: 13px; display: block; margin-top: 5px;">
                    Este nombre se mostrará en la interfaz de usuario.
                </small>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="description" style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                    Descripción
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          placeholder="Describe las responsabilidades y alcance de este rol..."
                          style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; resize: vertical; box-sizing: border-box;"></textarea>
                <small style="color: #6b7280; font-size: 13px; display: block; margin-top: 5px;">
                    Opcional. Ayuda a otros administradores a entender el propósito del rol.
                </small>
            </div>

            <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #3b82f6;">
                <p style="margin: 0; color: #1f2937; font-size: 14px;">
                    <strong>ℹ️ Nota:</strong> Después de crear el rol, podrás asignarle permisos específicos desde la pantalla de gestión de permisos.
                </p>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <a href="<?= asset('roles') ?>" 
                   class="btn btn-secondary" 
                   style="padding: 12px 24px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Cancelar
                </a>
                <button type="submit" 
                        class="btn btn-primary" 
                        style="padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Crear Rol y Asignar Permisos →
                </button>
            </div>
        </form>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #fef3c7; border-radius: 12px; border-left: 4px solid #f59e0b;">
        <h4 style="margin: 0 0 10px; color: #92400e;">⚠️ Buenas Prácticas</h4>
        <ul style="margin: 0; padding-left: 20px; color: #78350f; line-height: 1.8;">
            <li>Usa nombres descriptivos que reflejen claramente la función del rol.</li>
            <li>Define roles específicos en lugar de roles muy amplios.</li>
            <li>Asigna solo los permisos necesarios (principio de mínimo privilegio).</li>
            <li>Documenta el propósito del rol en la descripción.</li>
        </ul>
    </div>
</div>

<?php require_once app_path('views/partials/footer.php'); ?>
