<?php ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Global - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/bootstrap.min.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .placeholder-container { max-width: 800px; margin: 80px auto; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
        .icon-large { font-size: 4rem; color: #f59e0b; margin-bottom: 20px; }
        h1 { color: #111827; font-size: 2rem; font-weight: 700; margin-bottom: 15px; }
        .badge-coming-soon { background: #fef3c7; color: #92400e; padding: 8px 16px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; display: inline-block; margin-bottom: 20px; }
        p { color: #6b7280; font-size: 1.125rem; line-height: 1.6; margin-bottom: 30px; }
        .btn-back { background: #4f46e5; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; transition: all 0.3s; }
        .btn-back:hover { background: #4338ca; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="placeholder-container">
        <div class="icon-large">⚙️</div>
        <span class="badge-coming-soon">⏳ Próximamente</span>
        <h1>Configuración Global</h1>
        <p>Parámetros globales del sistema, base de datos y servicios.</p>
        <a href="<?php echo asset('root/dashboard'); ?>" class="btn-back">← Volver al Panel ROOT</a>
    </div>
</body>
</html>
