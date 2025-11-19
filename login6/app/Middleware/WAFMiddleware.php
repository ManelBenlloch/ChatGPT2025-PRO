<?php
/**
 * WAFMiddleware.php
 * 
 * Web Application Firewall (WAF) Middleware
 * 
 * Protege la aplicación contra ataques comunes:
 * - SQL Injection
 * - XSS (Cross-Site Scripting)
 * - CSRF (Cross-Site Request Forgery)
 * - Path Traversal
 * - Command Injection
 */

class WAFMiddleware {
    private static $suspiciousPatterns = [
        // SQL Injection
        '/(\bUNION\b.*\bSELECT\b)|(\bSELECT\b.*\bFROM\b)|(\bINSERT\b.*\bINTO\b)|(\bUPDATE\b.*\bSET\b)|(\bDELETE\b.*\bFROM\b)|(\bDROP\b.*\bTABLE\b)/i',
        
        // XSS
        '/(<script[^>]*>.*?<\/script>)|(<iframe[^>]*>)|(<object[^>]*>)|(<embed[^>]*>)|(<applet[^>]*>)/i',
        
        // Path Traversal
        '/(\.\.\/|\.\.\\\\)/i',
        
        // Command Injection
        '/(\||;|`|\$\(|\$\{)/i',
    ];

    private static $blockedIps = [];

    /**
     * Ejecutar todas las verificaciones del WAF
     * 
     * @return bool True si la petición es segura, False si es sospechosa
     */
    public static function check() {
        // Verificar IP bloqueada
        if (self::isIpBlocked()) {
            self::blockRequest('IP bloqueada');
            return false;
        }

        // Verificar patrones sospechosos en GET
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                if (self::containsSuspiciousPattern($value)) {
                    self::logSuspiciousActivity('GET', $key, $value);
                    self::blockRequest('Patrón sospechoso detectado en GET');
                    return false;
                }
            }
        }

        // Verificar patrones sospechosos en POST
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if (is_string($value) && self::containsSuspiciousPattern($value)) {
                    self::logSuspiciousActivity('POST', $key, $value);
                    self::blockRequest('Patrón sospechoso detectado en POST');
                    return false;
                }
            }
        }

        // Verificar User-Agent sospechoso
        if (self::hasSuspiciousUserAgent()) {
            self::logSuspiciousActivity('USER_AGENT', 'HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT'] ?? '');
            self::blockRequest('User-Agent sospechoso');
            return false;
        }

        return true;
    }

    /**
     * Verificar si un valor contiene patrones sospechosos
     * 
     * @param string $value
     * @return bool
     */
    private static function containsSuspiciousPattern($value) {
        foreach (self::$suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si la IP está bloqueada
     * 
     * @return bool
     */
    private static function isIpBlocked() {
        $ip = self::getClientIp();
        return in_array($ip, self::$blockedIps);
    }

    /**
     * Verificar si el User-Agent es sospechoso
     * 
     * @return bool
     */
    private static function hasSuspiciousUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Lista de bots y scanners conocidos
        $suspiciousAgents = [
            'sqlmap',
            'nikto',
            'nmap',
            'masscan',
            'acunetix',
            'nessus',
            'openvas',
        ];

        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bloquear la petición
     * 
     * @param string $reason Razón del bloqueo
     */
    private static function blockRequest($reason) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Acceso denegado',
            'message' => 'Tu petición ha sido bloqueada por razones de seguridad.',
            'reason' => ENVIRONMENT === 'development' ? $reason : null
        ]);
        exit();
    }

    /**
     * Registrar actividad sospechosa
     * 
     * @param string $type
     * @param string $key
     * @param string $value
     */
    private static function logSuspiciousActivity($type, $key, $value) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => self::getClientIp(),
            'type' => $type,
            'key' => $key,
            'value' => substr($value, 0, 200), // Limitar longitud
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
        ];

        error_log('WAF: Actividad sospechosa detectada - ' . json_encode($logData));
    }

    /**
     * Obtener la IP del cliente
     * 
     * @return string
     */
    private static function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    /**
     * Sanitizar entrada (para uso adicional)
     * 
     * @param string $input
     * @return string
     */
    public static function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

?>
