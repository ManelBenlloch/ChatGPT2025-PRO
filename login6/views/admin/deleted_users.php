<?php
/**
 * admin/deleted_users.php
 * 
 * Vista de Usuarios Eliminados (Soft Delete)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Eliminados - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .users-table {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table thead {
            background-color: #f9fafb;
        }
        .users-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        .users-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .users-table tr:last-child td {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1 style="margin: 0; color: #111827;">🗑️ Usuarios Eliminados</h1>
                <p style="margin: 5px 0 0 0; color: #6b7280;">Gestión de usuarios eliminados (soft delete)</p>
            </div>
            <div>
                <a href="<?php echo asset('admin/dashboard'); ?>" class="btn btn-secondary">Volver al Dashboard</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 30px; border-radius: 4px;">
            <p style="margin: 0; color: #78350f;">
                <strong>Información:</strong> Los usuarios eliminados se conservan en el sistema con un "soft delete". Puedes restaurarlos o eliminarlos permanentemente.
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'root'): ?>
                    Solo usuarios <strong>root</strong> pueden eliminar permanentemente.
                <?php endif; ?>
            </p>
        </div>

        <div class="users-table">
            <?php if (empty($deletedUsers)): ?>
                <div style="padding: 40px; text-align: center; color: #6b7280;">
                    <p style="margin: 0; font-size: 1.1rem;">No hay usuarios eliminados.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Rol</th>
                            <th>Eliminado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedUsers as $user): ?>
                        <tr>
                            <td style="font-family: monospace; color: #6b7280;">#<?php echo $user->id; ?></td>
                            <td style="font-weight: 500; color: #111827;"><?php echo htmlspecialchars($user->fullname); ?></td>
                            <td style="font-family: monospace; color: #6b7280;"><?php echo htmlspecialchars($user->email); ?></td>
                            <td style="color: #6b7280;"><?php echo htmlspecialchars($user->username); ?></td>
                            <td>
                                <span class="badge badge-danger"><?php echo strtoupper($user->role); ?></span>
                            </td>
                            <td style="color: #6b7280; font-size: 0.875rem;">
                                <?php echo date('d/m/Y H:i', strtotime($user->deleted_at)); ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <!-- Restaurar Usuario -->
                                    <form method="POST" action="<?php echo asset('admin/restore-user'); ?>" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                        <button 
                                            type="submit" 
                                            class="btn btn-sm btn-primary" 
                                            onclick="return confirm('¿Estás seguro de que deseas restaurar este usuario?');"
                                            style="background-color: #10b981; border: none; padding: 6px 12px; border-radius: 4px; color: white; cursor: pointer; font-size: 0.875rem;"
                                        >
                                            Restaurar
                                        </button>
                                    </form>

                                    <!-- Eliminar Permanentemente (solo root) -->
                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'root'): ?>
                                    <form method="POST" action="<?php echo asset('admin/permanent-delete-user'); ?>" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                                        <button 
                                            type="submit" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="return confirm('⚠️ ADVERTENCIA: Esta acción es IRREVERSIBLE. ¿Estás seguro de que deseas eliminar PERMANENTEMENTE este usuario?');"
                                            style="background-color: #ef4444; border: none; padding: 6px 12px; border-radius: 4px; color: white; cursor: pointer; font-size: 0.875rem;"
                                        >
                                            Eliminar Permanentemente
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding: 20px; background-color: #eff6ff; border-left: 4px solid #4f46e5; border-radius: 4px;">
            <h3 style="margin: 0 0 10px 0; color: #1e40af;">Sobre el Soft Delete</h3>
            <ul style="margin: 0; padding-left: 20px; color: #1e3a8a;">
                <li>Los usuarios eliminados no pueden iniciar sesión</li>
                <li>Sus datos se conservan en el sistema</li>
                <li>Puedes restaurarlos en cualquier momento</li>
                <li>Solo usuarios root pueden eliminar permanentemente</li>
                <li>La eliminación permanente es irreversible</li>
            </ul>
        </div>
    </div>

    <script src="<?php echo asset('assets/js/validations.js'); ?>"></script>
</body>
</html>
