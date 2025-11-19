<?php
/**
 * TwoFactorController.php
 * 
 * Controlador de Autenticación de Dos Factores (2FA)
 * 
 * Gestiona la configuración, activación y verificación de 2FA.
 */

require_once app_path('core/Controller.php');
require_once app_path('app/Models/User.php');
require_once app_path('app/Models/MFAFactor.php');
require_once app_path('app/Models/ActivityLog.php');
require_once app_path('helpers/TwoFactorAuth.php');

class TwoFactorController extends Controller {
    private $userModel;
    private $mfaModel;
    private $activityLog;
    private $twoFactorAuth;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->mfaModel = new MFAFactor();
        $this->activityLog = new ActivityLog();
        $this->twoFactorAuth = new TwoFactorAuth();
    }

    /**
     * Mostrar la página de configuración de 2FA
     */
    public function setup() {
        // Verificar autenticación
        if (!$this->isAuthenticated()) {
            $this->redirect('login');
            return;
        }

        $userId = $_SESSION['user_id'];
        
        // Verificar si ya tiene 2FA activado
        if ($this->mfaModel->has2FAEnabled($userId)) {
            $_SESSION['error'] = 'Ya tienes 2FA activado. Desactívalo primero si deseas reconfigurarlo.';
            $this->redirect('dashboard');
            return;
        }

        // Generar un nuevo secreto
        $secret = $this->twoFactorAuth->generateSecret();
        $_SESSION['temp_2fa_secret'] = $secret;

        // Obtener el código QR
        $username = $_SESSION['user_email'];
        $qrCodeUrl = $this->twoFactorAuth->getQRCodeUrl($username, $secret);

        // Mostrar la vista
        $this->view('auth/2fa_setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret' => $secret
        ]);
    }

    /**
     * Verificar y activar 2FA
     */
    public function verify() {
        // Verificar autenticación
        if (!$this->isAuthenticated()) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('2fa/setup');
            return;
        }

        $userId = $_SESSION['user_id'];
        $code = $_POST['code'] ?? '';
        $secret = $_SESSION['temp_2fa_secret'] ?? '';

        // Validar el código
        if (empty($code) || empty($secret)) {
            $_SESSION['error'] = 'Código o secreto no válido.';
            $this->redirect('2fa/setup');
            return;
        }

        // Verificar el código TOTP
        if (!$this->twoFactorAuth->verifyCode($secret, $code)) {
            $_SESSION['error'] = 'Código incorrecto. Por favor, inténtalo de nuevo.';
            $this->redirect('2fa/setup');
            return;
        }

        // Crear el factor TOTP
        $factorId = $this->mfaModel->createTOTPFactor($userId, $secret);
        $this->mfaModel->verifyFactor($factorId);

        // Actualizar el usuario
        $this->userModel->update($userId, ['is_2fa_enabled' => 1]);

        // Registrar en activity log
        $this->activityLog->log2FAEnabled($userId);

        // Limpiar el secreto temporal
        unset($_SESSION['temp_2fa_secret']);

        $_SESSION['success'] = '¡2FA activado correctamente! Tu cuenta ahora está más segura.';
        $this->redirect('dashboard');
    }

    /**
     * Mostrar la página de verificación de 2FA durante el login
     */
    public function challenge() {
        // Verificar que el usuario esté en proceso de login
        if (!isset($_SESSION['pending_2fa_user_id'])) {
            $this->redirect('login');
            return;
        }

        $this->view('auth/2fa_challenge', []);
    }

    /**
     * Verificar el código 2FA durante el login
     */
    public function validateChallenge() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
            return;
        }

        // Verificar que el usuario esté en proceso de login
        if (!isset($_SESSION['pending_2fa_user_id'])) {
            $_SESSION['error'] = 'Sesión expirada. Por favor, inicia sesión de nuevo.';
            $this->redirect('login');
            return;
        }

        $userId = $_SESSION['pending_2fa_user_id'];
        $code = $_POST['code'] ?? '';

        // Validar el código
        if (empty($code)) {
            $_SESSION['error'] = 'Por favor, introduce el código de verificación.';
            $this->redirect('2fa/challenge');
            return;
        }

        // Obtener el factor verificado del usuario
        $factor = $this->mfaModel->getVerifiedFactor($userId);
        if (!$factor) {
            $_SESSION['error'] = 'No se encontró un factor 2FA válido.';
            $this->redirect('login');
            return;
        }

        // Verificar el código TOTP
        if (!$this->twoFactorAuth->verifyCode($factor->secret, $code)) {
            $_SESSION['error'] = 'Código incorrecto. Por favor, inténtalo de nuevo.';
            $this->redirect('2fa/challenge');
            return;
        }

        // Código correcto, completar el login
        $user = $this->userModel->findById($userId);
        
        // Establecer la sesión
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_fullname'] = $user->fullname;
        $_SESSION['user_role'] = $user->role;

        // Actualizar último login
        $this->userModel->updateLastLogin($userId);

        // Registrar en activity log
        $this->activityLog->logLogin($userId);

        // Limpiar el estado pendiente
        unset($_SESSION['pending_2fa_user_id']);

        $_SESSION['success'] = '¡Bienvenido de nuevo, ' . htmlspecialchars($user->fullname) . '!';
        
        // Redirigir según el rol
        if ($user->role === 'admin' || $user->role === 'root') {
            $this->redirect('admin/dashboard');
        } elseif ($user->role === 'personal') {
            $this->redirect('personal/dashboard');
        } else {
            $this->redirect('dashboard');
        }
    }

    /**
     * Desactivar 2FA
     */
    public function disable() {
        // Verificar autenticación
        if (!$this->isAuthenticated()) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('dashboard');
            return;
        }

        $userId = $_SESSION['user_id'];

        // Obtener todos los factores del usuario
        $factors = $this->mfaModel->getUserFactors($userId);
        
        // Eliminar todos los factores
        foreach ($factors as $factor) {
            $this->mfaModel->removeFactor($factor->id);
        }

        // Actualizar el usuario
        $this->userModel->update($userId, ['is_2fa_enabled' => 0]);

        // Registrar en activity log
        $this->activityLog->log2FADisabled($userId);

        $_SESSION['success'] = '2FA desactivado correctamente.';
        $this->redirect('dashboard');
    }
}

?>
