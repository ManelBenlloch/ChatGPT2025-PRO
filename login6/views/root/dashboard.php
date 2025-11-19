<?php
/**
 * root/dashboard.php
 * 
 * Vista del Dashboard del Panel de Root
 * Dashboard principal con 8 módulos de gestión
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel ROOT - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .root-navbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
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
        .root-navbar .btn-logout {
            background: rgba(255,255,255,0.9);
            color: #dc2626;
            border: none;
        }
        .root-navbar .btn-logout:hover {
            background: white;
        }
        .root-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .stats-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .stats-link:hover {
            color: #4338ca;
            gap: 12px;
        }
        .stats-link svg {
            width: 20px;
            height: 20px;
        }
        .welcome-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .welcome-card h2 {
            margin: 0 0 10px 0;
            color: #111827;
            font-size: 1.75rem;
            font-weight: 700;
        }
        .welcome-card p {
            margin: 0;
            color: #6b7280;
            font-size: 1rem;
            line-height: 1.6;
        }
        .privilege-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .privilege-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .privilege-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .privilege-card .icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .privilege-card h3 {
            margin: 0;
            color: #111827;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .module-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .module-card .module-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .module-card h3 {
            margin: 0 0 10px 0;
            color: #111827;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .module-card p {
            margin: 0 0 20px 0;
            color: #6b7280;
            font-size: 0.9375rem;
            line-height: 1.6;
            flex-grow: 1;
        }
        .module-card .btn {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
            display: inline-block;
        }
        .module-card .btn:hover {
            background: #e5e7eb;
            color: #111827;
        }
        .icon-users { color: #3b82f6; }
        .icon-system { color: #8b5cf6; }
        .icon-security { color: #ef4444; }
        .icon-stats { color: #10b981; }
        .icon-global { color: #f59e0b; }
        .icon-database { color: #06b6d4; }
        .icon-emergency { color: #dc2626; }
        .icon-waf { color: #ec4899; }
    </style>
</head>
<body>
    <!-- Navbar ROOT -->
    <div class="root-navbar">
        <div class="container">
            <h1>
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" fill="#dc2626"/>
                </svg>
                Panel ROOT
            </h1>
            <div class="user-info">
                <span>ROOT: <?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? 'Usuario'); ?></span>
                <a href="<?php echo asset('logout'); ?>" class="btn btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="root-container">
        <!-- Enlace a Estadísticas del Sistema -->
        <a href="<?php echo asset('root/analytics'); ?>" class="stats-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Ver toda la información y estadísticas del sistema
        </a>

        <!-- Mensaje de Bienvenida -->
        <div class="welcome-card">
            <h2>¡Bienvenido, <?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? 'Usuario'); ?>!</h2>
            <p>Tienes acceso completo al sistema como usuario ROOT. Desde aquí puedes administrar todos los aspectos del sistema.</p>
        </div>

        <!-- Tarjetas de Privilegios -->
        <div class="privilege-cards">
            <div class="privilege-card">
                <div class="icon">∞</div>
                <h3>Privilegios ROOT</h3>
            </div>
            <div class="privilege-card">
                <div class="icon" style="font-size: 3.5rem; font-weight: 700; color: #4f46e5;">100%</div>
                <h3>Acceso Total</h3>
            </div>
            <div class="privilege-card">
                <div class="icon">🔒</div>
                <h3>Máxima Seguridad</h3>
            </div>
            <div class="privilege-card">
                <div class="icon">⚡</div>
                <h3>Control Total</h3>
            </div>
        </div>

        <!-- Módulos de Gestión -->
        <div class="modules-grid">
            <!-- 1. Gestión de Usuarios -->
            <div class="module-card">
                <div class="module-icon icon-users">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3>Gestión de Usuarios</h3>
                <p>Crear, editar y eliminar usuarios del sistema. Gestionar roles y permisos.</p>
                <a href="<?php echo asset('root/gestion-usuarios'); ?>" class="btn">Gestionar Usuarios</a>
            </div>

            <!-- 2. Gestión del Sistema -->
            <div class="module-card">
                <div class="module-icon icon-system">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <h3>Gestión del Sistema</h3>
                <p>Administrar configuración, reglas y dominios permitidos.</p>
                <a href="<?php echo asset('root/gestion-sistema'); ?>" class="btn">Gestionar Sistema</a>
            </div>

            <!-- 3. Configuración de Seguridad -->
            <div class="module-card">
                <div class="module-icon icon-security">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3>Configuración de Seguridad</h3>
                <p>Configurar políticas de seguridad, autenticación de dos factores y auditoría.</p>
                <a href="<?php echo asset('root/seguridad'); ?>" class="btn">Configurar Seguridad</a>
            </div>

            <!-- 4. Estadísticas del Sistema -->
            <div class="module-card">
                <div class="module-icon icon-stats">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3>Estadísticas del Sistema</h3>
                <p>Ver estadísticas de uso, logs de acceso y métricas de rendimiento.</p>
                <a href="<?php echo asset('root/analytics'); ?>" class="btn">Ver Estadísticas</a>
            </div>

            <!-- 5. Configuración Global -->
            <div class="module-card">
                <div class="module-icon icon-global">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3>Configuración Global</h3>
                <p>Configurar parámetros globales del sistema, base de datos y servicios.</p>
                <a href="<?php echo asset('root/configuracion'); ?>" class="btn">Configuración</a>
            </div>

            <!-- 6. Base de Datos -->
            <div class="module-card">
                <div class="module-icon icon-database">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                </div>
                <h3>Base de Datos</h3>
                <p>Administrar la base de datos, realizar backups y optimizaciones.</p>
                <a href="<?php echo asset('root/database'); ?>" class="btn">Administrar BD</a>
            </div>

            <!-- 7. Sistema de Emergencia -->
            <div class="module-card">
                <div class="module-icon icon-emergency">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3>Sistema de Emergencia</h3>
                <p>Acceso a funciones de emergencia y recuperación del sistema.</p>
                <a href="<?php echo asset('root/emergencia'); ?>" class="btn">Emergencia</a>
            </div>

            <!-- 8. Gestión de Roles y Permisos -->
            <div class="module-card">
                <div class="module-icon" style="color: #8b5cf6;">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3>Gestión de Roles y Permisos</h3>
                <p>Crear y gestionar roles personalizados con permisos granulares.</p>
                <a href="<?php echo asset('roles'); ?>" class="btn">Gestionar Roles</a>
            </div>

            <!-- 9. Reglas WAF -->
            <div class="module-card">
                <div class="module-icon icon-waf">
                    <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3>Reglas WAF</h3>
                <p>Crear y gestionar reglas de firewall de aplicaciones web.</p>
                <a href="<?php echo asset('root/waf-rules'); ?>" class="btn">Gestionar WAF</a>
            </div>
        </div>
    </div>

    <script src="<?php echo asset('assets/js/jquery.min.js'); ?>"></script>
    <script src="<?php echo asset('assets/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>
