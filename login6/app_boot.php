<?php
/**
 * app_boot.php
 * 
 * Script de Portabilidad Total para login3
 * 
 * Este archivo garantiza que el sistema funcione correctamente en cualquier entorno:
 * - Localhost con XAMPP
 * - Hostinger
 * - SiteGround
 * - Cualquier dominio (.es, .dev, .org, etc.)
 * - Cualquier nombre de carpeta (login, login2, login3, logon, etc.)
 * - Cualquier nivel de subcarpetas
 * 
 * NO MODIFICAR ESTE ARCHIVO A MENOS QUE SEA ABSOLUTAMENTE NECESARIO
 */

// Evitar redefinición si ya está cargado
if (!defined("APP_PORTABILITY_LOADED")) {
    define("APP_PORTABILITY_LOADED", true);

    // 1. PROTOCOLO: Detectar si es HTTP o HTTPS
    $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https://" : "http://";

    // 2. HOST: Obtener el nombre del dominio o subdominio
    // Ejemplos: localhost, manelbenlloch.es, secure.manelbenlloch.dev
    $host = $_SERVER["HTTP_HOST"];

    // 3. RUTA DEL SCRIPT: Obtener la ruta del directorio del script actual desde la raíz del servidor web
    // dirname($_SERVER["SCRIPT_NAME"]) nos da la ruta de la carpeta que contiene el index.php
    // Ejemplo: Si la URL es http://localhost/proyectos/login-2033/index.php, esto será /proyectos/login-2033
    $script_path = dirname($_SERVER["SCRIPT_NAME"]);

    // 4. NORMALIZACIÓN: Asegurarse de que no haya barras duplicadas y que la raíz sea una sola barra
    $base_uri = rtrim($script_path, "/");
    if ($base_uri === "" || $base_uri === "\\") {
        $base_uri = "/";
    }

    // 5. URL BASE: Construir la URL base completa y definirla como una constante global
    define("APP_BASE_URL", $protocol . $host . $base_uri);

    // 6. RUTA BASE DEL SISTEMA: Ruta absoluta del directorio raíz del proyecto
    define("APP_ROOT_PATH", dirname(__FILE__));

    /**
     * Función de ayuda para generar URLs absolutas y portables
     * 
     * @param string $path La ruta interna (ej. "public/css/style.css")
     * @return string La URL completa y correcta
     * 
     * Ejemplos de uso:
     * - asset("css/style.css") -> http://localhost/login3/css/style.css
     * - asset("js/auth.js") -> https://manelbenlloch.es/logon/js/auth.js
     */
    function asset($path = "") {
        return APP_BASE_URL . "/" . ltrim($path, "/");
    }

    /**
     * Función de ayuda para generar rutas de archivos absolutas
     * 
     * @param string $path La ruta interna relativa al directorio raíz
     * @return string La ruta absoluta del archivo
     */
    function app_path($path = "") {
        return APP_ROOT_PATH . "/" . ltrim($path, "/");
    }
}
?>
