<?php
/**
 * Register Page - Secure Student Registration
 */

// Handle form submission
$form_submitted = false;
$form_error = false;
$form_success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $csrf_token = isset($_POST['_csrf_token']) ? $_POST['_csrf_token'] : '';
    if (!CSRFToken::verify($csrf_token)) {
        $form_error = true;
        SecurityLogger::log("CSRF token validation failed for register form", 'WARNING');
    } else {
        // Rate limiting check (5 registrations per hour per IP)
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!RateLimiter::check($ip . '_register_form', 5, 3600)) {
            $form_error = true;
            $form_success_msg = 'Too many registration attempts. Please try again later.';
            SecurityLogger::log("Register form rate limit exceeded from IP: $ip", 'WARNING');
        } else {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

            // Validate inputs
            $errors = [];

            if (!InputValidator::name($name)) {
                $errors[] = 'Name must be 2-100 characters and contain only letters.';
            }

            if (!InputValidator::email($email)) {
                $errors[] = 'Please enter a valid email address.';
            }

            if (!InputValidator::password($password)) {
                $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, and a number.';
            }

            if ($password !== $password_confirm) {
                $errors[] = 'Passwords do not match.';
            }

            if (!empty($phone) && !InputValidator::phone($phone)) {
                $errors[] = 'Please enter a valid phone number.';
            }

            if (empty($errors)) {
                // Sanitize inputs
                $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');

                // Hash password
                $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

                // Here you would save to database
                // Example: INSERT INTO users VALUES (NULL, $name, $email, $phone, $password_hash, NOW())

                $form_submitted = true;
                $form_success_msg = 'Account created successfully! You can now log in.';
                SecurityLogger::log("New user registered: $email", 'INFO');
            } else {
                $form_error = true;
                $form_success_msg = implode(' ', $errors);
            }
        }
    }
}
?>

<section class="pt-32 pb-20 bg-white">
    <div class="container mx-auto px-6 max-w-2xl">
        <div class="bg-gradient-to-br from-red-50 to-gray-50 rounded-3xl shadow-xl p-10 md:p-14 border border-red-100">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-plus text-red-500 text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Crear Cuenta</h1>
                <p class="text-gray-600 text-sm mt-2">Únete a Punjab Idiomas y comienza tu aprendizaje</p>
            </div>

            <?php if ($form_submitted): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
                    <p class="font-bold mb-2"><i class="fas fa-check-circle mr-2"></i>¡Registro exitoso!</p>
                    <p><?php echo safe_output($form_success_msg); ?></p>
                    <p class="text-sm mt-2"><a href="<?php echo url('login'); ?>" class="font-bold underline hover:no-underline">Iniciar sesión aquí</a></p>
                </div>
            <?php endif; ?>

            <?php if ($form_error && !$form_submitted): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-lg">
                    <p class="font-bold mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Error en el formulario:</p>
                    <p><?php echo safe_output($form_success_msg) ?: 'Por favor, verifica todos los campos.'; ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <!-- CSRF Token -->
                <input type="hidden" name="_csrf_token" value="<?php echo CSRFToken::getToken(); ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo *</label>
                    <input
                        type="text"
                        name="name"
                        required
                        maxlength="100"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                        placeholder="Tu nombre completo"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico *</label>
                    <input
                        type="email"
                        name="email"
                        required
                        maxlength="254"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                        placeholder="tu@email.com"
                    >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña *</label>
                        <input
                            type="password"
                            name="password"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                            placeholder="Mínimo 8 caracteres"
                        >
                        <p class="text-xs text-gray-500 mt-1">Incluir mayúsculas, minúsculas y números</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Contraseña *</label>
                        <input
                            type="password"
                            name="password_confirm"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                            placeholder="Repite tu contraseña"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono (Opcional)</label>
                    <input
                        type="tel"
                        name="phone"
                        maxlength="20"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                        placeholder="+34 XXX XXX XXX"
                    >
                </div>

                <div class="flex items-start gap-3 pt-2">
                    <input type="checkbox" name="terms" required class="mt-1 w-4 h-4 text-red-500 rounded">
                    <label class="text-sm text-gray-600">
                        Acepto los <a href="#" class="text-red-500 font-bold hover:underline">términos de servicio</a> y <a href="#" class="text-red-500 font-bold hover:underline">política de privacidad</a>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition shadow-lg transform hover:-translate-y-1 mt-6"
                >
                    <i class="fas fa-user-check mr-2"></i>Crear Cuenta
                </button>
            </form>

            <p class="text-center text-gray-600 text-sm mt-6">
                ¿Ya tienes cuenta?
                <a href="<?php echo url('login'); ?>" class="text-red-500 hover:text-red-600 font-bold">Inicia sesión aquí</a>
            </p>

            <p class="text-xs text-gray-500 mt-4 text-center"><i class="fas fa-lock mr-1"></i>Tu información está segura y encriptada.</p>
        </div>
    </div>
</section>
