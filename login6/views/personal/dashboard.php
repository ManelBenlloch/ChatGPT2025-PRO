<?php
/**
 * Portada del Panel de Personal
 * 
 * Dashboard principal para usuarios con rol PERSONAL
 * Acceso a funcionalidades intermedias del sistema
 */

// Verificar que el usuario esté autenticado y sea personal o superior
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['personal', 'admin', 'root'])) {
    header('Location: ' . url('login'));
    exit;
}

$user = $_SESSION;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Personal - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= url('personal/dashboard') ?>">👤 Panel PERSONAL</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= url('personal/dashboard') ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('sessions') ?>">Mis Sesiones</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($user['fullname']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= url('logout') ?>">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1>Bienvenido, <?= htmlspecialchars($user['fullname']) ?></h1>
                <p class="text-muted">Rol: <span class="badge bg-success">PERSONAL</span></p>
            </div>
        </div>

        <!-- Información del Usuario -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Mi Información</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($user['fullname']) ?></p>
                        <p><strong>Usuario:</strong> <?= htmlspecialchars($user['username']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Rol:</strong> <?= htmlspecialchars($user['role']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?= url('sessions') ?>" class="btn btn-primary btn-block mb-2">Ver Mis Sesiones</a>
                        <a href="<?= url('logout') ?>" class="btn btn-danger btn-block">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= asset('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
