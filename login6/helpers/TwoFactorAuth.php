<?php
/**
 * TwoFactorAuth.php
 * 
 * Helper para Autenticación de Dos Factores (2FA)
 * 
 * Proporciona funcionalidades para generar y verificar códigos TOTP
 * compatibles con Google Authenticator.
 */

require_once app_path('vendor/GoogleAuthenticator.php');

class TwoFactorAuth {
    private $ga;

    public function __construct() {
        $this->ga = new PHPGangsta_GoogleAuthenticator();
    }

    /**
     * Generar un secreto para TOTP
     * 
     * @return string Secreto de 16 caracteres
     */
    public function generateSecret() {
        return $this->ga->createSecret();
    }

    /**
     * Obtener el código QR para Google Authenticator
     * 
     * @param string $username Nombre del usuario
     * @param string $secret Secreto TOTP
     * @param string $issuer Nombre de la aplicación
     * @return string URL del código QR
     */
    public function getQRCodeUrl($username, $secret, $issuer = null) {
        if ($issuer === null) {
            $issuer = APP_NAME;
        }
        return $this->ga->getQRCodeGoogleUrl($issuer, $username, $secret);
    }

    /**
     * Verificar un código TOTP
     * 
     * @param string $secret Secreto TOTP
     * @param string $code Código de 6 dígitos proporcionado por el usuario
     * @param int $discrepancy Tolerancia de tiempo (por defecto 2 = ±60 segundos)
     * @return bool True si el código es válido
     */
    public function verifyCode($secret, $code, $discrepancy = 2) {
        return $this->ga->verifyCode($secret, $code, $discrepancy);
    }

    /**
     * Obtener el código actual (útil para pruebas)
     * 
     * @param string $secret Secreto TOTP
     * @return string Código de 6 dígitos
     */
    public function getCurrentCode($secret) {
        return $this->ga->getCode($secret);
    }
}

?>
