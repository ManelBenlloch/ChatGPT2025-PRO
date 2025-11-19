<?php
/**
 * root/seguridad.php
 * 
 * Vista Placeholder - Configuración de Seguridad
 * Se implementará en funcionalidades futuras
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Seguridad - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/bootstrap.min.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .placeholder-container { max-width: 800px; margin: 80px auto; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
        .icon-large { font-size: 4rem; color: #ef4444; margin-bottom: 20px; }
        h1 { color: #111827; font-size: 2rem; font-weight: 700; margin-bottom: 15px; }
        .badge-coming-soon { background: #fef3c7; color: #92400e; padding: 8px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; display: inline-block; margin-bottom: 20px; }
        p { color: #6b7280; font-size: 1.125rem; line-height: 1.6; margin-bottom: 30px; }
        .btn-back { background: #4f46e5; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; transition: all 0.3s; }
        .btn-back:hover { background: #4338ca; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="placeholder-container">
        <div class="icon-large">
            <svg width="80" height="80" fill="currentColor" viewBox="0 0 24 24" style="display: inline-block;">
                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <span class="badge-coming-soon">⏳ Próximamente</span>
        <h1>Configuración de Seguridad</h1>
        <p>Políticas de seguridad, autenticación de dos factores y auditoría.</p>
        <a href="<?php echo asset('root/dashboard'); ?>" class="btn-back">← Volver al Panel ROOT</a>
    </div>
</body>
</html>
