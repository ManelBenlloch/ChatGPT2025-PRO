<?php
/**
 * EmailService.php
 * 
 * Servicio de Envío de Emails
 * 
 * Gestiona el envío de emails usando PHPMailer.
 * Incluye plantillas para verificación de email, reseteo de contraseña, etc.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once app_path('vendor/PHPMailer/src/Exception.php');
require_once app_path('vendor/PHPMailer/src/PHPMailer.php');
require_once app_path('vendor/PHPMailer/src/SMTP.php');

class EmailService {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Configurar SMTP
     */
    private function configureSMTP() {
        try {
            // Configuración del servidor SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USER;
            $this->mailer->Password = SMTP_PASS;
            $this->mailer->SMTPSecure = SMTP_SECURE;
            $this->mailer->Port = SMTP_PORT;

            // Configuración del remitente
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Error al configurar SMTP: " . $e->getMessage());
        }
    }

    /**
     * Enviar email de verificación
     * 
     * @param string $to Email del destinatario
     * @param string $fullname Nombre completo del usuario
     * @param string $token Token de verificación
     * @return bool
     */
    public function sendVerificationEmail($to, $fullname, $token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $fullname);

            $verificationLink = asset("verify-email?token={$token}");

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Verifica tu cuenta - ' . APP_NAME;
            $this->mailer->Body = $this->getVerificationEmailTemplate($fullname, $verificationLink);
            $this->mailer->AltBody = "Hola {$fullname},\n\nPor favor, verifica tu cuenta haciendo clic en el siguiente enlace:\n{$verificationLink}\n\nSi no creaste esta cuenta, ignora este email.";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar email de verificación: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    /**
     * Enviar email de reseteo de contraseña
     * 
     * @param string $to Email del destinatario
     * @param string $fullname Nombre completo del usuario
     * @param string $token Token de reseteo
     * @return bool
     */
    public function sendPasswordResetEmail($to, $fullname, $token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $fullname);

            $resetLink = asset("reset-password-confirm?token={$token}");

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Resetea tu contraseña - ' . APP_NAME;
            $this->mailer->Body = $this->getPasswordResetEmailTemplate($fullname, $resetLink);
            $this->mailer->AltBody = "Hola {$fullname},\n\nHas solicitado resetear tu contraseña. Haz clic en el siguiente enlace:\n{$resetLink}\n\nEste enlace expira en 1 hora.\n\nSi no solicitaste esto, ignora este email.";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar email de reseteo: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    /**
     * Plantilla HTML para email de verificación
     */
    private function getVerificationEmailTemplate($fullname, $link) {
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: 'Inter', Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: #4f46e5; color: #ffffff; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 40px; }
                .content p { color: #333; line-height: 1.6; margin-bottom: 20px; }
                .button { display: inline-block; background: #4f46e5; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: 500; }
                .button:hover { background: #4338ca; }
                .footer { background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Verifica tu Cuenta</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$fullname}</strong>,</p>
                    <p>Gracias por registrarte en " . APP_NAME . ". Para completar tu registro, por favor verifica tu dirección de email haciendo clic en el botón de abajo:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$link}' class='button'>Verificar mi Email</a>
                    </p>
                    <p>Si no creaste esta cuenta, puedes ignorar este email de forma segura.</p>
                    <p>Saludos,<br>El equipo de " . APP_NAME . "</p>
                </div>
                <div class='footer'>
                    <p>Este es un email automático, por favor no respondas.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Plantilla HTML para email de reseteo de contraseña
     */
    private function getPasswordResetEmailTemplate($fullname, $link) {
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: 'Inter', Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: #4f46e5; color: #ffffff; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 40px; }
                .content p { color: #333; line-height: 1.6; margin-bottom: 20px; }
                .button { display: inline-block; background: #4f46e5; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: 500; }
                .button:hover { background: #4338ca; }
                .footer { background: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px; }
                .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; color: #92400e; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Resetea tu Contraseña</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$fullname}</strong>,</p>
                    <p>Hemos recibido una solicitud para resetear la contraseña de tu cuenta en " . APP_NAME . ".</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='{$link}' class='button'>Resetear mi Contraseña</a>
                    </p>
                    <div class='warning'>
                        <strong>⚠️ Importante:</strong> Este enlace expira en 1 hora por seguridad.
                    </div>
                    <p>Si no solicitaste resetear tu contraseña, puedes ignorar este email de forma segura. Tu contraseña no cambiará.</p>
                    <p>Saludos,<br>El equipo de " . APP_NAME . "</p>
                </div>
                <div class='footer'>
                    <p>Este es un email automático, por favor no respondas.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

?>
