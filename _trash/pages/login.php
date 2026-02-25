<?php
/**
 * Login Page - Secure Authentication
 */

$login_error = false;
$login_success = false;

// Demo Credentials (in production, use database)
$valid_users = [
    ['email' => 'student@punjab.com', 'password' => 'student', 'name' => 'Student Account', 'role' => 'student'],
    ['email' => 'admin@punjab.com', 'password' => 'admin', 'name' => 'Administrator', 'role' => 'admin'],
    ['email' => 'demo@punjab.com', 'password' => 'demo', 'name' => 'Demo User', 'role' => 'student']
];

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $csrf_token = isset($_POST['_csrf_token']) ? $_POST['_csrf_token'] : '';
    if (!CSRFToken::verify($csrf_token)) {
        $login_error = true;
        SecurityLogger::log("CSRF token validation failed for login", 'WARNING');
    } else {
        // Rate limiting (5 attempts per 15 minutes per IP)
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!RateLimiter::check($ip . '_login_attempt', 5, 900)) {
            $login_error = true;
            SecurityLogger::log("Login rate limit exceeded from IP: $ip", 'WARNING');
        } else {
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            // normalize email for comparison
            $email = strtolower($email);
            $remember = isset($_POST['remember']) ? true : false;

            // Validate inputs
            $email_valid = InputValidator::email($email);
            $password_valid = !empty($password);

            if (!$email_valid || !$password_valid) {
                $login_error = true;
                SecurityLogger::log("Invalid login attempt - missing email or password from IP: $ip", 'WARNING');
            } else {
                // Check credentials
                $user_found = false;
                foreach ($valid_users as $user) {
                    if (strtolower($user['email']) === $email && $user['password'] === $password) {
                        $user_found = true;
                        // Start logged-in session
                            $_SESSION['user_id'] = bin2hex(random_bytes(8));
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'student';
                            $_SESSION['logged_in'] = true;
                        $_SESSION['login_time'] = time();

                        if ($remember) {
                            $_SESSION['remember_me'] = true;
                        }

                        SecurityLogger::log("Successful login from: $email", 'INFO');
                        $login_success = true;

                        // Redirect to dashboard immediately
                        header('Location: ' . url('dashboard'));
                        exit;
                    }
                }

                if (!$user_found) {
                    $login_error = true;
                    SecurityLogger::log("Failed login attempt for: $email from IP: $ip", 'WARNING');
                }
            }
        }
    }
}
?>

<section class="pt-32 pb-32 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="container mx-auto px-6">
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book-open text-red-500 text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Iniciar Sesión</h1>
                    <p class="text-gray-600 text-sm mt-2">Accede a tu cuenta de estudiante</p>
                </div>

                <?php if ($login_success): ?>
                    <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
                        <p class="font-bold mb-1"><i class="fas fa-check-circle mr-2"></i>¡Bienvenido!</p>
                        <p class="text-sm">Redirigiendo al dashboard...</p>
                    </div>
                <?php endif; ?>

                <?php if ($login_error): ?>
                    <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
                        <p class="font-bold"><i class="fas fa-exclamation-circle mr-2"></i>Error de Autenticación</p>
                        <p class="text-sm">Correo o contraseña incorrectos.</p>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <!-- CSRF Token -->
                    <input type="hidden" name="_csrf_token" value="<?php echo CSRFToken::getToken(); ?>">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico</label>
                        <input
                            type="email"
                            name="email"
                            required
                            maxlength="254"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                            placeholder="tu@email.com"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña</label>
                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                            placeholder="Tu contraseña"
                        >
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-red-500 rounded">
                            <span class="text-gray-600">Recuérdame</span>
                        </label>
                        <a href="#" class="text-red-500 hover:text-red-600 font-medium">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition shadow-lg mt-6"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                    </button>
                </form>

                <p class="text-center text-gray-600 text-sm mt-6">
                    Para más información, contáctanos por
                    <a href="https://wa.me/34612455057" target="_blank" class="text-green-600 hover:text-green-700 font-bold">WhatsApp</a>
                </p>
            </div>

            <div class="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-lg text-center">
                <i class="fas fa-info-circle text-blue-500 text-2xl mb-3 block"></i>
                <p class="text-sm text-blue-800 font-semibold mb-2">Credenciales de Demostración:</p>
                <p class="text-xs text-blue-700 mb-1"><strong>Email:</strong> student@punjab.com | <strong>Password:</strong> student</p>
                <p class="text-xs text-blue-700 mb-1"><strong>Email:</strong> admin@punjab.com | <strong>Password:</strong> admin</p>
                <p class="text-xs text-blue-700"><strong>Email:</strong> demo@punjab.com | <strong>Password:</strong> demo</p>
            </div>
        </div>
    </div>
</section>
