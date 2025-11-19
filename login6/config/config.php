<?php
/**
 * config.php
 * 
 * Archivo de Configuración Principal de login3
 * 
 * IMPORTANTE: Este archivo contiene información sensible.
 * NO subir a repositorios públicos sin encriptar las credenciales.
 */

// Cargar el script de portabilidad
require_once dirname(__DIR__) . '/app_boot.php';

// ============================================================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================================================

// Configuración para entorno LOCAL (XAMPP)
define('DB_HOST_LOCAL', 'localhost');
define('DB_NAME_LOCAL', 'login6_db');
define('DB_USER_LOCAL', 'root');
define('DB_PASS_LOCAL', '');

// Configuración para entorno PRODUCCIÓN (Hostinger/SiteGround)
define('DB_HOST_PROD', 'localhost');
define('DB_NAME_PROD', 'u459047355_login3');
define('DB_USER_PROD', 'u459047355_login3');
define('DB_PASS_PROD', 'TU_CONTRASEÑA_AQUI');

// Detectar el entorno automáticamente
// Si estamos en localhost, usar configuración local, si no, usar producción
$is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
             strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

if ($is_local) {
    define('DB_HOST', DB_HOST_LOCAL);
    define('DB_NAME', DB_NAME_LOCAL);
    define('DB_USER', DB_USER_LOCAL);
    define('DB_PASS', DB_PASS_LOCAL);
    define('ENVIRONMENT', 'development');
} else {
    define('DB_HOST', DB_HOST_PROD);
    define('DB_NAME', DB_NAME_PROD);
    define('DB_USER', DB_USER_PROD);
    define('DB_PASS', DB_PASS_PROD);
    define('ENVIRONMENT', 'production');
}

// ============================================================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================================================

// Clave secreta para tokens CSRF y encriptación
// CAMBIAR ESTA CLAVE EN PRODUCCIÓN
define('APP_SECRET_KEY', 'tu_clave_secreta_super_segura_aqui_' . md5(APP_BASE_URL));

// Duración de sesión en segundos (2 horas por defecto)
define('SESSION_LIFETIME', 7200);

// Número máximo de intentos de login antes de bloquear
define('MAX_LOGIN_ATTEMPTS', 5);

// Tiempo de bloqueo en segundos (15 minutos)
define('LOCKOUT_TIME', 900);

// ============================================================================
// CONFIGURACIÓN DE reCAPTCHA
// ============================================================================

// Claves de reCAPTCHA v2
// IMPORTANTE: Obtener tus propias claves en https://www.google.com/recaptcha/admin
define('RECAPTCHA_SITE_KEY', '6LerEl0eAAAAAEVE8Iy3hPUuAr8T7uZHu6whUn9-');
define('RECAPTCHA_SECRET_KEY', '6LerEl0eAAAAAD1W2y5315qnRsTRUANd_Fhb_jjA');

// Claves específicas por formulario (opcional, usar las mismas si no necesitas diferentes)
define('RECAPTCHA_SITE_KEY_LOGIN', RECAPTCHA_SITE_KEY);
define('RECAPTCHA_SECRET_KEY_LOGIN', RECAPTCHA_SECRET_KEY);
define('RECAPTCHA_SITE_KEY_REGISTER', RECAPTCHA_SITE_KEY);
define('RECAPTCHA_SECRET_KEY_REGISTER', RECAPTCHA_SECRET_KEY);

// ============================================================================
// CONFIGURACIÓN DE EMAIL (PHPMailer)
// ============================================================================

define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true);
define('SMTP_USER', 'info@manelbenlloch.es');
define('SMTP_PASS', 'Trolencio911*_*');
define('SMTP_FROM_EMAIL', 'info@manelbenlloch.es');
define('SMTP_FROM_NAME', 'Info ManelBenlloch');

// ============================================================================
// CONFIGURACIÓN GENERAL
// ============================================================================

// Nombre de la aplicación
define('APP_NAME', 'Login5 - Sistema de Autenticación Avanzado');

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores según el entorno
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', app_path('logs/error.log'));
}

// ============================================================================
// INICIAR SESIÓN
// ============================================================================

// Configuración segura de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', !$is_local); // Solo HTTPS en producción

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// CONEXIÓN A BASE DE DATOS (PDO)
// ============================================================================

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    } else {
        die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
    }
}

// Hacer la conexión PDO disponible globalmente
$GLOBALS['pdo'] = $pdo;

?>
