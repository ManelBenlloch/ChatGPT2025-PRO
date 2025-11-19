<?php
/**
 * AuthController.php
 * 
 * Controlador de Autenticación
 * 
 * Gestiona todas las operaciones de autenticación con:
 * - Rate Limiting
 * - Activity Logs
 * - Email Service
 */

require_once app_path('core/Controller.php');
require_once app_path('app/Models/RateLimit.php');
require_once app_path('app/Models/ActivityLog.php');
require_once app_path('app/Models/AllowedDomain.php');
require_once app_path('helpers/EmailService.php');
require_once app_path('helpers/RecaptchaHelper.php');

class AuthController extends Controller {
    private $rateLimitModel;
    private $activityLogModel;
    private $emailService;
    
    public function __construct() {
        $this->rateLimitModel = new RateLimit();
        $this->activityLogModel = new ActivityLog();
        $this->emailService = new EmailService();
    }

    /**
     * Mostrar el formulario de login
     */
    public function showLogin() {
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/login');
    }

    /**
     * Procesar el login
     */
    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }

        $ip = $this->getClientIp();

        // Verificar si la IP está bloqueada
        if ($this->rateLimitModel->isBlocked($ip)) {
            $timeRemaining = $this->rateLimitModel->getLockoutTimeRemaining($ip);
            $_SESSION['error'] = "Demasiados intentos fallidos. Intenta de nuevo en " . ceil($timeRemaining / 60) . " minutos.";
            $this->redirect('login');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validaciones básicas
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Por favor, completa todos los campos.';
            $this->redirect('login');
        }

        // Verificar reCAPTCHA
        if (!RecaptchaHelper::verifyFromPost('g-recaptcha-response', RECAPTCHA_SECRET_KEY_LOGIN)) {
            $this->rateLimitModel->recordAttempt($ip);
            $_SESSION['error'] = 'Por favor, completa el reCAPTCHA correctamente.';
            $this->redirect('login');
        }

        // Buscar el usuario
        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);

        if (!$user) {
            $this->rateLimitModel->recordAttempt($ip);
            $this->activityLogModel->logFailedLogin($email);
            $_SESSION['error'] = 'Credenciales incorrectas.';
            $this->redirect('login');
        }

        // Verificar la contraseña
        if (!$userModel->verifyPassword($user, $password)) {
            $this->rateLimitModel->recordAttempt($ip);
            $this->activityLogModel->logFailedLogin($email);
            $_SESSION['error'] = 'Credenciales incorrectas.';
            $this->redirect('login');
        }

        // Verificar si el usuario está activo
        if (!$user->is_active) {
            $_SESSION['error'] = 'Tu cuenta ha sido desactivada. Contacta al administrador.';
            $this->redirect('login');
        }

        // Verificar si el email está verificado
        if (!$user->email_verified) {
            $_SESSION['error'] = 'Por favor, verifica tu email antes de iniciar sesión.';
            $this->redirect('login');
        }

        // Login exitoso - Resetear intentos
        $this->rateLimitModel->resetAttempts($ip);

        // Crear sesión
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_fullname'] = $user->fullname;

        // Registrar login
        $userModel->updateLastLogin($user->id);
        $this->activityLogModel->logLogin($user->id);

        // Redirigir según el rol
        $this->redirectBasedOnRole($user->role);
    }

    /**
     * Mostrar el formulario de registro
     */
    public function showRegister() {
        if ($this->isAuthenticated()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/register');
    }

    /**
     * Procesar el registro
     */
    public function handleRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('register');
        }

        // Obtener datos del formulario
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $alias = trim($_POST['alias'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);

        // Validaciones
        $errors = [];

        // Verificar términos
        if (!$terms) {
            $errors[] = 'Debes aceptar los términos y condiciones.';
        }

        // Verificar reCAPTCHA
        if (!RecaptchaHelper::verifyFromPost('g-recaptcha-response', RECAPTCHA_SECRET_KEY_REGISTER)) {
            $errors[] = 'Por favor, completa el reCAPTCHA correctamente.';
        }

        if (empty($fullname)) {
            $errors[] = 'El nombre completo es obligatorio.';
        }

        if (empty($username)) {
            $errors[] = 'El nombre de usuario es obligatorio.';
        }

        // El alias es opcional, pero si se proporciona debe ser válido
        if (!empty($alias) && !preg_match('/^[a-zA-Z0-9_-]{3,18}$/', $alias)) {
            $errors[] = 'El alias debe tener entre 3 y 18 caracteres (letras, números, guiones y guiones bajos).';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido.';
        }

        // Verificar que el dominio del email esté permitido
        if (!empty($email)) {
            $emailParts = explode('@', $email);
            if (count($emailParts) === 2) {
                $domain = $emailParts[1];
                $allowedDomainModel = new AllowedDomain();
                if (!$allowedDomainModel->isAllowed($domain)) {
                    $errors[] = "El dominio de email '{$domain}' no está permitido para registro.";
                }
            }
        }

        if (empty($password) || strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        // Verificar si el email, username o alias ya existen
        $userModel = $this->model('User');

        if ($userModel->emailExists($email)) {
            $errors[] = 'El email ya está registrado.';
        }

        if ($userModel->usernameExists($username)) {
            $errors[] = 'El nombre de usuario ya está en uso.';
        }

        // Solo verificar alias si se proporcionó
        if (!empty($alias) && $userModel->aliasExists($alias)) {
            $errors[] = 'El alias ya está en uso.';
        }

        // Si hay errores, redirigir con los errores
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('register');
        }

        // Crear el usuario
        $userId = $userModel->createUser([
            'fullname' => $fullname,
            'username' => $username,
            'alias' => $alias,
            'email' => $email,
            'password' => $password,
            'role' => 'user',
            'terms_accepted' => 1
        ]);

        // Obtener el token de verificación
        $user = $userModel->findById($userId);

        // Enviar email de verificación
        $this->emailService->sendVerificationEmail($email, $fullname, $user->verification_token);

        // Registrar actividad
        $this->activityLogModel->logRegistration($userId, $email);

        $_SESSION['success'] = 'Registro exitoso. Por favor, verifica tu email para activar tu cuenta.';
        $this->redirect('login');
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->activityLogModel->logLogout($_SESSION['user_id']);
        }
        
        session_destroy();
        $this->redirect('login');
    }

    /**
     * Verificar email
     */
    public function verifyEmail() {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = 'Token de verificación inválido.';
            $this->redirect('login');
        }

        $userModel = $this->model('User');
        $success = $userModel->verifyEmail($token);

        if ($success) {
            // Obtener el usuario para registrar la actividad
            $stmt = $GLOBALS['pdo']->prepare("SELECT id FROM users WHERE verification_token IS NULL AND email_verified = 1 ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch();
            
            if ($user) {
                $this->activityLogModel->logEmailVerification($user->id);
            }

            $_SESSION['success'] = 'Email verificado correctamente. Ya puedes iniciar sesión.';
        } else {
            $_SESSION['error'] = 'Token de verificación inválido o expirado.';
        }

        $this->redirect('login');
    }

    /**
     * Mostrar el formulario de reseteo de contraseña
     */
    public function showResetPassword() {
        $this->view('auth/reset_password');
    }

    /**
     * Procesar la solicitud de reseteo de contraseña
     */
    public function handleResetPasswordRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reset-password');
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Por favor, introduce un email válido.';
            $this->redirect('reset-password');
        }

        $userModel = $this->model('User');
        $token = $userModel->generateResetToken($email);

        if ($token) {
            $user = $userModel->findByEmail($email);
            
            // Enviar email con el token
            $this->emailService->sendPasswordResetEmail($email, $user->fullname, $token);
            
            // Registrar actividad
            $this->activityLogModel->logPasswordResetRequest($user->id, $email);
            
            $_SESSION['success'] = 'Se ha enviado un email con las instrucciones para resetear tu contraseña.';
        } else {
            $_SESSION['error'] = 'No se encontró ninguna cuenta con ese email.';
        }

        $this->redirect('reset-password');
    }

    /**
     * Redirigir según el rol del usuario
     * 
     * @param string $role
     */
    private function redirectBasedOnRole($role) {
        switch ($role) {
            case 'root':
                $this->redirect('root/dashboard');
                break;
            case 'admin':
                $this->redirect('admin/dashboard');
                break;
            case 'personal':
                $this->redirect('personal/dashboard');
                break;
            default:
                $this->redirect('dashboard');
                break;
        }
    }

    /**
     * Obtener la IP del cliente
     * 
     * @return string
     */
    private function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
}

?>
