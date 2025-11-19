<?php
/**
 * root/gestion_usuarios.php
 * 
 * Vista de Gestión de Usuarios Avanzada
 * Tabla completa con permisos granulares y CRUD
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/bootstrap.min.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .root-navbar {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .root-navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .root-navbar h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .root-navbar .btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.3s;
        }
        .root-navbar .btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container-fluid {
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-header h2 {
            margin: 0;
            color: #111827;
            font-size: 1.875rem;
            font-weight: 700;
        }
        .btn-primary {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }
        .search-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9375rem;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1400px;
        }
        thead {
            background: #f9fafb;
        }
        th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }
        td {
            padding: 16px;
            font-size: 0.875rem;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }
        tbody tr:hover {
            background: #f9fafb;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .role-badge {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .role-root {
            background: #fef3c7;
            color: #92400e;
        }
        .role-admin {
            background: #dbeafe;
            color: #1e40af;
        }
        .role-personal {
            background: #e0e7ff;
            color: #3730a3;
        }
        .role-user {
            background: #e5e7eb;
            color: #374151;
        }
        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8125rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 5px;
            white-space: nowrap;
        }
        .btn-edit {
            background: #dbeafe;
            color: #1e40af;
        }
        .btn-edit:hover {
            background: #bfdbfe;
        }
        .btn-delete {
            background: #fee2e2;
            color: #991b1b;
        }
        .btn-delete:hover {
            background: #fecaca;
        }
        .checkbox-permission {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navbar ROOT -->
    <div class="root-navbar">
        <div class="container">
            <h1>👥 Gestión de Usuarios</h1>
            <a href="<?php echo asset('root/dashboard'); ?>" class="btn">← Volver al Panel ROOT</a>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h2>Gestión de Usuarios</h2>
            <a href="#" class="btn-primary" onclick="alert('Funcionalidad de crear usuario próximamente'); return false;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Usuario
            </a>
        </div>

        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="🔍 Buscar por nombre, email, username..." onkeyup="filterTable()">
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>2FA</th>
                        <th>Gestión</th>
                        <th>Borrar</th>
                        <th>Cambiar PW</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Obtener todos los usuarios
                    $users = $users ?? [];
                    
                    if (empty($users)) {
                        echo '<tr><td colspan="12" style="text-align: center; padding: 40px; color: #9ca3af;">No hay usuarios registrados</td></tr>';
                    } else {
                        foreach ($users as $user) {
                            // Determinar el color del rol
                            $roleClass = 'role-user';
                            switch ($user->role) {
                                case 'root':
                                    $roleClass = 'role-root';
                                    break;
                                case 'admin':
                                    $roleClass = 'role-admin';
                                    break;
                                case 'personal':
                                    $roleClass = 'role-personal';
                                    break;
                            }
                            
                            // Estado
                            $statusBadge = $user->deleted_at ? '<span class="badge badge-danger">Inactivo</span>' : '<span class="badge badge-success">Activo</span>';
                            
                            // 2FA
                            $has2FA = false; // TODO: Verificar si tiene 2FA habilitado
                            $twoFABadge = $has2FA ? '<span class="badge badge-info">Sí</span>' : '<span class="badge badge-danger">No</span>';
                            
                            // Permisos (ejemplo, se pueden personalizar)
                            $canManage = in_array($user->role, ['root', 'admin']);
                            $canDelete = in_array($user->role, ['root', 'admin']);
                            $canChangePassword = in_array($user->role, ['root', 'admin']);
                            
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($user->id) . '</td>';
                            echo '<td><strong>' . htmlspecialchars($user->fullname) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($user->email) . '</td>';
                            echo '<td>' . htmlspecialchars($user->username) . '</td>';
                            echo '<td><span class="role-badge ' . $roleClass . '">' . strtoupper($user->role) . '</span></td>';
                            echo '<td>' . $statusBadge . '</td>';
                            echo '<td>' . $twoFABadge . '</td>';
                            echo '<td><input type="checkbox" class="checkbox-permission" ' . ($canManage ? 'checked' : '') . ' onclick="updatePermission(' . $user->id . ', \'manage\', this.checked)"></td>';
                            echo '<td><input type="checkbox" class="checkbox-permission" ' . ($canDelete ? 'checked' : '') . ' onclick="updatePermission(' . $user->id . ', \'delete\', this.checked)"></td>';
                            echo '<td><input type="checkbox" class="checkbox-permission" ' . ($canChangePassword ? 'checked' : '') . ' onclick="updatePermission(' . $user->id . ', \'change_password\', this.checked)"></td>';
                            echo '<td>' . date('d/m/Y', strtotime($user->created_at)) . '</td>';
                            echo '<td>';
                            echo '<button class="btn-action btn-edit" onclick="editUser(' . $user->id . ')">Editar</button>';
                            echo '<button class="btn-action btn-delete" onclick="deleteUser(' . $user->id . ')">Eliminar</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="<?php echo asset('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <script>
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }

        function editUser(userId) {
            alert('Funcionalidad de editar usuario #' + userId + ' próximamente');
            // TODO: Abrir modal de edición
        }

        function deleteUser(userId) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                alert('Funcionalidad de eliminar usuario #' + userId + ' próximamente');
                // TODO: Implementar eliminación
            }
        }

        function updatePermission(userId, permission, value) {
            alert('Actualizar permiso "' + permission + '" para usuario #' + userId + ': ' + (value ? 'Activado' : 'Desactivado'));
            // TODO: Implementar actualización de permisos
        }
    </script>
</body>
</html>
