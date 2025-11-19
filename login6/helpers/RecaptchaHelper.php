<?php
/**
 * RecaptchaHelper.php
 * 
 * Helper para Verificación de reCAPTCHA
 * 
 * Verifica la respuesta de reCAPTCHA con la API de Google.
 */

class RecaptchaHelper {
    
    /**
     * Verificar la respuesta de reCAPTCHA
     * 
     * @param string $recaptchaResponse La respuesta del reCAPTCHA del formulario
     * @param string $secretKey La clave secreta de reCAPTCHA (opcional, usa la del config por defecto)
     * @return bool True si la verificación es exitosa, False en caso contrario
     */
    public static function verify($recaptchaResponse, $secretKey = null) {
        // Si no se proporciona una clave secreta, usar la del config
        if ($secretKey === null) {
            $secretKey = RECAPTCHA_SECRET_KEY;
        }

        // Si no hay respuesta de reCAPTCHA, retornar false
        if (empty($recaptchaResponse)) {
            return false;
        }

        // Preparar los datos para enviar a Google
        $data = [
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        // Configurar opciones de contexto para la petición HTTP
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);

        // Hacer la petición a la API de Google
        $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);

        // Decodificar la respuesta JSON
        $captchaSuccess = json_decode($verify);

        // Retornar true si la verificación fue exitosa
        return $captchaSuccess->success ?? false;
    }

    /**
     * Verificar reCAPTCHA desde el POST
     * 
     * @param string $fieldName El nombre del campo en $_POST (por defecto 'g-recaptcha-response')
     * @param string $secretKey La clave secreta de reCAPTCHA (opcional)
     * @return bool True si la verificación es exitosa, False en caso contrario
     */
    public static function verifyFromPost($fieldName = 'g-recaptcha-response', $secretKey = null) {
        $recaptchaResponse = $_POST[$fieldName] ?? '';
        return self::verify($recaptchaResponse, $secretKey);
    }
}

?>
