<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $pageTitle ?? APP_NAME; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
    
    <!-- Fuente Inter de Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Meta tags adicionales -->
    <meta name="description" content="<?php echo $pageDescription ?? 'Sistema de autenticación profesional'; ?>">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
