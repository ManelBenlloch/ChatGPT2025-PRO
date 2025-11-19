<?php
/**
 * Vista: Editar Rol
 */
require_once app_path('views/partials/header.php');
?>

<div class="container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
    <div class="page-header" style="margin-bottom: 30px;">
        <h1 style="margin: 0; color: #1f2937;">Editar Rol: <?= htmlspecialchars($role->display_name) ?></h1>
        <p style="margin: 10px 0 0; color: #6b7280;">Modifica la información del rol personalizado</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            <strong>✗</strong> <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="form-container" style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <form method="POST" action="<?= asset('roles/' . $role->id . '/update') ?>">
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                    Nombre del Rol (identificador)
                </label>
                <input type="text" 
                       value="<?= htmlspecialchars($role->name) ?>" 
                       disabled 
                       style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; background: #f9fafb; color: #6b7280; box-sizing: border-box;">
                <small style="color: #6b7280; font-size: 13px; display: block; margin-top: 5px;">
                    El identificador del rol no se puede modificar.
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
                       value="<?= htmlspecialchars($role->display_name) ?>"
                       style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="description" style="display: block; margin-bottom: 8px; color: #374151; font-weight: 600;">
                    Descripción
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4" 
                          style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px; resize: vertical; box-sizing: border-box;"><?= htmlspecialchars($role->description ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" 
                           name="is_active" 
                           <?= $role->is_active ? 'checked' : '' ?>
                           style="width: 20px; height: 20px; margin-right: 10px; cursor: pointer;">
                    <span style="color: #374151; font-weight: 600;">Rol activo</span>
                </label>
                <small style="color: #6b7280; font-size: 13px; display: block; margin-top: 5px; margin-left: 30px;">
                    Los roles inactivos no pueden ser asignados a nuevos usuarios.
                </small>
            </div>

            <div style="display: flex; gap: 12px; justify-content: space-between; margin-top: 30px;">
                <a href="<?= asset('roles') ?>" 
                   class="btn btn-secondary" 
                   style="padding: 12px 24px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    ← Volver
                </a>
                <div style="display: flex; gap: 12px;">
                    <a href="<?= asset('roles/' . $role->id . '/permissions') ?>" 
                       class="btn btn-secondary" 
                       style="padding: 12px 24px; background: #dbeafe; color: #1e40af; text-decoration: none; border-radius: 8px; font-weight: 600;">
                        🔑 Gestionar Permisos
                    </a>
                    <button type="submit" 
                            class="btn btn-primary" 
                            style="padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        💾 Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once app_path('views/partials/footer.php'); ?>
