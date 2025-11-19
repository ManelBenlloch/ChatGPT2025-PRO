<?php
/**
 * sessions/index.php
 * 
 * Vista de Gestión de Sesiones Activas
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Sesiones - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div style="max-width: 1200px; margin: 40px auto; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="margin: 0; color: #111827;">Mis Sesiones Activas</h1>
            <a href="<?php echo asset('dashboard'); ?>" class="btn btn-secondary">Volver al Dashboard</a>
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

        <div style="background-color: #eff6ff; border-left: 4px solid #4f46e5; padding: 15px; margin-bottom: 30px; border-radius: 4px;">
            <p style="margin: 0; color: #1e3a8a;">
                <strong>Información:</strong> Aquí puedes ver todas tus sesiones activas y cerrar aquellas que no reconozcas.
            </p>
        </div>

        <?php if (count($sessions) > 1): ?>
            <form method="POST" action="<?php echo asset('sessions/revoke-others'); ?>" style="margin-bottom: 20px;">
                <button type="submit" class="btn btn-secondary" onclick="return confirm('¿Estás seguro de que deseas cerrar todas las demás sesiones?');">
                    Cerrar Todas las Demás Sesiones
                </button>
            </form>
        <?php endif; ?>

        <div class="sessions-list">
            <?php if (empty($sessions)): ?>
                <p style="text-align: center; color: #6b7280; padding: 40px;">No tienes sesiones activas.</p>
            <?php else: ?>
                <?php foreach ($sessions as $session): ?>
                    <div class="session-card" style="background-color: <?php echo $session->is_current ? '#f0fdf4' : '#ffffff'; ?>; border: 1px solid <?php echo $session->is_current ? '#86efac' : '#e5e7eb'; ?>; border-radius: 8px; padding: 20px; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <?php if ($session->is_current): ?>
                                    <span style="display: inline-block; background-color: #22c55e; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; margin-bottom: 10px;">
                                        SESIÓN ACTUAL
                                    </span>
                                <?php endif; ?>
                                
                                <h3 style="margin: 0 0 10px 0; color: #111827; font-size: 1.1rem;">
                                    <?php echo htmlspecialchars($session->device_info['browser']); ?> en <?php echo htmlspecialchars($session->device_info['os']); ?>
                                </h3>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 15px;">
                                    <div>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">Tipo de Dispositivo</p>
                                        <p style="margin: 5px 0 0 0; color: #111827; font-weight: 500;"><?php echo htmlspecialchars($session->device_info['device_type']); ?></p>
                                    </div>
                                    
                                    <div>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">Dirección IP</p>
                                        <p style="margin: 5px 0 0 0; color: #111827; font-weight: 500; font-family: monospace;"><?php echo htmlspecialchars($session->device_info['ip']); ?></p>
                                    </div>
                                    
                                    <div>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">Iniciada</p>
                                        <p style="margin: 5px 0 0 0; color: #111827; font-weight: 500;"><?php echo date('d/m/Y H:i', strtotime($session->created_at)); ?></p>
                                    </div>
                                    
                                    <div>
                                        <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">Expira</p>
                                        <p style="margin: 5px 0 0 0; color: #111827; font-weight: 500;"><?php echo date('d/m/Y H:i', strtotime($session->expires_at)); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!$session->is_current): ?>
                                <form method="POST" action="<?php echo asset('sessions/revoke'); ?>" style="margin-left: 20px;">
                                    <input type="hidden" name="session_id" value="<?php echo $session->id; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas cerrar esta sesión?');" style="background-color: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 500;">
                                        Cerrar Sesión
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding: 20px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
            <h3 style="margin: 0 0 10px 0; color: #92400e;">Consejos de Seguridad</h3>
            <ul style="margin: 0; padding-left: 20px; color: #78350f;">
                <li>Revisa regularmente tus sesiones activas</li>
                <li>Cierra sesiones que no reconozcas inmediatamente</li>
                <li>Usa 2FA para mayor seguridad</li>
                <li>No compartas tu cuenta con otras personas</li>
            </ul>
        </div>
    </div>

    <script src="<?php echo asset('assets/js/validations.js'); ?>"></script>
</body>
</html>
