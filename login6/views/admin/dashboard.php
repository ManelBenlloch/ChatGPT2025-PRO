<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-top: 4px solid #4f46e5;
        }
        .stat-card h3 {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0 0 10px 0;
        }
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4f46e5;
        }
        .users-table {
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f4f4f4;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #333;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-root { background: #dc2626; color: #ffffff; }
        .badge-admin { background: #f59e0b; color: #ffffff; }
        .badge-personal { background: #3b82f6; color: #ffffff; }
        .badge-user { background: #6b7280; color: #ffffff; }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Encabezado -->
        <div class="admin-header">
            <div>
                <h1>Panel de Administración</h1>
                <p style="color: #6b7280;">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_fullname']); ?></p>
            </div>
            <div>
                <a href="<?php echo asset('dashboard'); ?>" class="btn btn-secondary" style="margin-right: 10px;">Ir al Dashboard</a>
                <a href="<?php echo asset('logout'); ?>" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total de Usuarios</h3>
                <div class="number"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Usuarios Activos</h3>
                <div class="number"><?php echo $stats['active_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Usuarios Verificados</h3>
                <div class="number"><?php echo $stats['verified_users']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Administradores</h3>
                <div class="number"><?php echo $stats['admin_users']; ?></div>
            </div>
        </div>

        <!-- Usuarios Recientes -->
        <div class="users-table">
            <h2 style="margin-bottom: 20px;">Usuarios Recientes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha de Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo $user->id; ?></td>
                            <td><?php echo htmlspecialchars($user->fullname); ?></td>
                            <td><?php echo htmlspecialchars($user->email); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user->role; ?>">
                                    <?php echo strtoupper($user->role); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user->created_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
