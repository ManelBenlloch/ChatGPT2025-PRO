<?php
/**
 * Vista: Gestionar Permisos del Rol
 */
require_once app_path('views/partials/header.php');
?>

<div class="container" style="max-width: 1000px; margin: 40px auto; padding: 20px;">
    <div class="page-header" style="margin-bottom: 30px;">
        <h1 style="margin: 0; color: #1f2937;">
            Permisos: <?= htmlspecialchars($role->display_name) ?>
        </h1>
        <p style="margin: 10px 0 0; color: #6b7280;">
            <?php if ($role->is_system_role): ?>
                Visualiza los permisos del rol del sistema
            <?php else: ?>
                Selecciona los permisos que tendrá este rol
            <?php endif; ?>
        </p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            <strong>✓</strong> <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error" style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            <strong>✗</strong> <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if ($role->is_system_role): ?>
        <div style="background: #fef3c7; padding: 16px; border-radius: 8px; margin-bottom: 24px; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; color: #92400e; font-size: 14px;">
                <strong>⚠️ Rol del Sistema:</strong> Los permisos de los roles del sistema están predefinidos y no se pueden modificar.
            </p>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= asset('roles/' . $role->id . '/save-permissions') ?>">
        <div class="permissions-container" style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            
            <?php if (!$role->is_system_role): ?>
                <div style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 2px solid #e5e7eb;">
                    <button type="button" 
                            onclick="selectAll()" 
                            style="padding: 10px 20px; background: #dbeafe; color: #1e40af; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 10px;">
                        ✓ Seleccionar Todos
                    </button>
                    <button type="button" 
                            onclick="deselectAll()" 
                            style="padding: 10px 20px; background: #f3f4f6; color: #374151; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                        ✗ Deseleccionar Todos
                    </button>
                </div>
            <?php endif; ?>

            <?php foreach ($permissionsByCategory as $category => $permissions): ?>
                <div class="category-section" style="margin-bottom: 30px;">
                    <h3 style="margin: 0 0 16px; color: #1f2937; font-size: 18px; text-transform: capitalize; border-bottom: 2px solid #667eea; padding-bottom: 8px;">
                        📁 <?= htmlspecialchars(ucfirst($category)) ?>
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px;">
                        <?php foreach ($permissions as $permission): ?>
                            <label style="display: flex; align-items: start; padding: 12px; background: #f9fafb; border-radius: 8px; cursor: <?= $role->is_system_role ? 'default' : 'pointer' ?>; border: 2px solid <?= in_array($permission->id, $rolePermissionIds) ? '#667eea' : '#e5e7eb' ?>; transition: all 0.3s;">
                                <input type="checkbox" 
                                       name="permissions[]" 
                                       value="<?= $permission->id ?>" 
                                       <?= in_array($permission->id, $rolePermissionIds) ? 'checked' : '' ?>
                                       <?= $role->is_system_role ? 'disabled' : '' ?>
                                       class="permission-checkbox"
                                       style="width: 20px; height: 20px; margin-right: 12px; margin-top: 2px; cursor: <?= $role->is_system_role ? 'default' : 'pointer' ?>;">
                                <div style="flex: 1;">
                                    <div style="color: #1f2937; font-weight: 600; margin-bottom: 4px;">
                                        <?= htmlspecialchars($permission->display_name) ?>
                                    </div>
                                    <div style="color: #6b7280; font-size: 13px; line-height: 1.4;">
                                        <?= htmlspecialchars($permission->description) ?>
                                    </div>
                                    <div style="margin-top: 4px;">
                                        <code style="background: #e5e7eb; color: #374151; padding: 2px 6px; border-radius: 4px; font-size: 11px;">
                                            <?= htmlspecialchars($permission->name) ?>
                                        </code>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($permissionsByCategory)): ?>
                <div style="text-align: center; padding: 40px; color: #6b7280;">
                    <div style="font-size: 48px; margin-bottom: 16px;">🔒</div>
                    <p style="margin: 0; font-size: 16px;">No hay permisos disponibles en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 12px; justify-content: space-between; margin-top: 24px;">
            <a href="<?= asset('roles') ?>" 
               class="btn btn-secondary" 
               style="padding: 12px 24px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600;">
                ← Volver a Roles
            </a>
            
            <?php if (!$role->is_system_role): ?>
                <button type="submit" 
                        class="btn btn-primary" 
                        style="padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    💾 Guardar Permisos
                </button>
            <?php endif; ?>
        </div>
    </form>

    <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 12px; border-left: 4px solid #3b82f6;">
        <h4 style="margin: 0 0 10px; color: #1f2937;">ℹ️ Sobre los Permisos</h4>
        <ul style="margin: 0; padding-left: 20px; color: #6b7280; line-height: 1.8;">
            <li><strong>Permisos Granulares:</strong> Cada permiso controla una acción específica en el sistema.</li>
            <li><strong>Categorías:</strong> Los permisos están organizados por áreas funcionales del sistema.</li>
            <li><strong>Herencia:</strong> Los usuarios heredan todos los permisos del rol asignado.</li>
            <li><strong>Root:</strong> El rol root tiene todos los permisos automáticamente.</li>
        </ul>
    </div>
</div>

<script>
function selectAll() {
    document.querySelectorAll('.permission-checkbox:not([disabled])').forEach(checkbox => {
        checkbox.checked = true;
        checkbox.parentElement.style.borderColor = '#667eea';
    });
}

function deselectAll() {
    document.querySelectorAll('.permission-checkbox:not([disabled])').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.parentElement.style.borderColor = '#e5e7eb';
    });
}

// Actualizar borde al cambiar checkbox
document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            this.parentElement.style.borderColor = '#667eea';
        } else {
            this.parentElement.style.borderColor = '#e5e7eb';
        }
    });
});
</script>

<?php require_once app_path('views/partials/footer.php'); ?>
