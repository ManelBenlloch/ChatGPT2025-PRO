<?php
/**
 * root/gestion_sistema.php
 * 
 * Vista Placeholder - Gestión del Sistema
 * Se implementará en la Funcionalidad #3
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Sistema - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/bootstrap.min.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .placeholder-container {
            max-width: 800px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .icon-large {
            font-size: 4rem;
            color: #8b5cf6;
            margin-bottom: 20px;
        }
        h1 {
            color: #111827;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .badge-coming-soon {
            background: #fef3c7;
            color: #92400e;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }
        p {
            color: #6b7280;
            font-size: 1.125rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .feature-list {
            text-align: left;
            max-width: 500px;
            margin: 30px auto;
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }
        .feature-list h3 {
            color: #374151;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .feature-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .feature-list li {
            color: #6b7280;
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .feature-list li::before {
            content: "✓";
            color: #10b981;
            font-weight: 700;
            font-size: 1.25rem;
        }
        .btn-back {
            background: #4f46e5;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #4338ca;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="placeholder-container">
        <div class="icon-large">
            <svg width="80" height="80" fill="currentColor" viewBox="0 0 24 24" style="display: inline-block;">
                <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
        </div>
        
        <span class="badge-coming-soon">⏳ Próximamente</span>
        
        <h1>Gestión del Sistema</h1>
        
        <p>Este módulo avanzado se implementará en la <strong>Funcionalidad #3</strong> con configuraciones editables en tiempo real.</p>
        
        <div class="feature-list">
            <h3>Características que se implementarán:</h3>
            <ul>
                <li>Tabla de configuraciones del sistema</li>
                <li>20+ parámetros configurables</li>
                <li>Edición en tiempo real</li>
                <li>Validación de dominios permitidos</li>
                <li>Reglas de validación dinámicas</li>
                <li>Sistema CRUD completo</li>
                <li>Botón "+ Nuevo" para añadir configs</li>
            </ul>
        </div>
        
        <a href="<?php echo asset('root/dashboard'); ?>" class="btn-back">← Volver al Panel ROOT</a>
    </div>
</body>
</html>
