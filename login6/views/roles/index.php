<?php
/**
 * Vista: Listado de Roles
 */
require_once app_path('views/partials/header.php');
?>

<div class="container" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
    <div class="page-header" style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; color: #1f2937;">Gestión de Roles</h1>
                <p style="margin: 10px 0 0; color: #6b7280;">Administra los roles del sistema y roles personalizados</p>
            </div>
            <a href="<?= asset('roles/create') ?>" class="btn btn-primary" style="padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                ➕ Crear Nuevo Rol
            </a>
        </div>
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

    <div class="roles-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        <?php foreach ($roles as $role): ?>
            <div class="role-card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 2px solid <?= $role->is_system_role ? '#fbbf24' : '#e5e7eb' ?>;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                    <div style="flex: 1;">
                        <h3 style="margin: 0; color: #1f2937; font-size: 20px;">
                            <?= htmlspecialchars($role->display_name) ?>
                        </h3>
                        <p style="margin: 5px 0 0; color: #6b7280; font-size: 14px;">
                            <code style="background: #f3f4f6; padding: 2px 8px; border-radius: 4px;"><?= htmlspecialchars($role->name) ?></code>
                        </p>
                    </div>
                    <?php if ($role->is_system_role): ?>
                        <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            🔒 SISTEMA
                        </span>
                    <?php else: ?>
                        <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            ✏️ PERSONALIZADO
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($role->description): ?>
                    <p style="color: #4b5563; font-size: 14px; line-height: 1.6; margin-bottom: 16px;">
                        <?= htmlspecialchars($role->description) ?>
                    </p>
                <?php endif; ?>

                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                    <div style="flex: 1;">
                        <span style="color: #6b7280; font-size: 13px;">Usuarios:</span>
                        <strong style="color: #1f2937; font-size: 16px; margin-left: 5px;"><?= $role->user_count ?></strong>
                    </div>
                    <div style="flex: 1;">
                        <span style="color: #6b7280; font-size: 13px;">Estado:</span>
                        <?php if ($role->is_active): ?>
                            <span style="color: #10b981; font-weight: 600; margin-left: 5px;">● Activo</span>
                        <?php else: ?>
                            <span style="color: #ef4444; font-weight: 600; margin-left: 5px;">● Inactivo</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <?php if (!$role->is_system_role): ?>
                        <a href="<?= asset('roles/' . $role->id . '/permissions') ?>" 
                           class="btn btn-secondary" 
                           style="flex: 1; text-align: center; padding: 10px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                            🔑 Permisos
                        </a>
                        <a href="<?= asset('roles/' . $role->id . '/edit') ?>" 
                           class="btn btn-secondary" 
                           style="flex: 1; text-align: center; padding: 10px; background: #dbeafe; color: #1e40af; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                            ✏️ Editar
                        </a>
                        <?php if ($role->user_count == 0): ?>
                            <form method="POST" action="<?= asset('roles/' . $role->id . '/delete') ?>" style="flex: 1;" onsubmit="return confirm('¿Estás seguro de eliminar este rol?');">
                                <button type="submit" 
                                        style="width: 100%; padding: 10px; background: #fee2e2; color: #991b1b; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= asset('roles/' . $role->id . '/permissions') ?>" 
                           class="btn btn-secondary" 
                           style="flex: 1; text-align: center; padding: 10px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                            👁️ Ver Permisos
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($roles)): ?>
        <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 64px; margin-bottom: 20px;">📋</div>
            <h3 style="color: #6b7280; margin: 0;">No hay roles disponibles</h3>
            <p style="color: #9ca3af; margin: 10px 0 20px;">Crea tu primer rol personalizado para comenzar</p>
            <a href="<?= asset('roles/create') ?>" class="btn btn-primary" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                Crear Rol
            </a>
        </div>
    <?php endif; ?>

    <div style="margin-top: 40px; padding: 20px; background: #f9fafb; border-radius: 12px; border-left: 4px solid #667eea;">
        <h4 style="margin: 0 0 10px; color: #1f2937;">ℹ️ Información sobre Roles</h4>
        <ul style="margin: 0; padding-left: 20px; color: #6b7280; line-height: 1.8;">
            <li><strong>Roles del Sistema:</strong> Son roles predefinidos (user, personal, admin, root) que no se pueden editar ni eliminar.</li>
            <li><strong>Roles Personalizados:</strong> Puedes crear roles a medida con permisos específicos según tus necesidades.</li>
            <li><strong>Permisos Granulares:</strong> Cada rol puede tener permisos específicos para diferentes áreas del sistema.</li>
            <li><strong>Usuarios Asignados:</strong> No se puede eliminar un rol que tenga usuarios asignados.</li>
        </ul>
    </div>

    <div style="margin-top: 20px; text-align: center;">
        <a href="<?= asset('root/dashboard') ?>" class="btn btn-secondary" style="display: inline-block; padding: 10px 20px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px;">
            ← Volver al Panel Root
        </a>
    </div>
</div>

<?php require_once app_path('views/partials/footer.php'); ?>
