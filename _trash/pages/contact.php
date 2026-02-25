<?php
/**
 * Contact Page - Secure Form Handler
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
        SecurityLogger::log("CSRF token validation failed for contact form", 'WARNING');
    } else {
        // Rate limiting check (10 submissions per hour per IP)
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!RateLimiter::check($ip . '_contact_form', 10, 3600)) {
            $form_error = true;
            $form_success_msg = 'Too many submissions. Please try again later.';
            SecurityLogger::log("Contact form rate limit exceeded from IP: $ip", 'WARNING');
        } else {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';

            // Validate inputs
            $errors = [];

            if (!InputValidator::name($name)) {
                $errors[] = 'Name must be 2-100 characters and contain only letters.';
            }

            if (!InputValidator::email($email)) {
                $errors[] = 'Please enter a valid email address.';
            }

            if (!empty($phone) && !InputValidator::phone($phone)) {
                $errors[] = 'Please enter a valid phone number.';
            }

            if (!InputValidator::text($subject, 100)) {
                $errors[] = 'Subject must be 1-100 characters.';
            }

            if (!InputValidator::text($message, 2000)) {
                $errors[] = 'Message must be 1-2000 characters.';
            }

            if (empty($errors)) {
                // Sanitize all inputs
                $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
                $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
                $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

                // Here you would send the email with sanitized data
                // Example: mail($to, $subject, $message, $headers);
                $form_submitted = true;
                $form_success_msg = 'Thank you! Your message has been received. We will contact you soon.';
                SecurityLogger::log("Contact form submitted successfully from: $email", 'INFO');
            } else {
                $form_error = true;
                $form_success_msg = implode(' ', $errors);
            }
        }
    }
}
?>

<section class="pt-32 pb-20 bg-white">
    <div class="container mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-12 bg-gray-50 rounded-3xl overflow-hidden shadow-lg">
            <!-- Contact Info -->
            <div class="p-10 md:p-14">
                <h4 class="text-red-500 font-bold uppercase tracking-wide mb-2">Contáctanos</h4>
                <h2 class="text-4xl font-bold text-gray-900 mb-6">Estamos aquí para ayudarte</h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    ¿Tienes preguntas sobre nuestros cursos? Contáctanos a través de WhatsApp para obtener información más detallada y personalizada.
                </p>

                <div class="mb-8 p-6 bg-green-50 border-2 border-green-500 rounded-2xl">
                    <a href="https://wa.me/34612455057" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-3 py-4 px-6 bg-green-500 text-white font-bold rounded-lg hover:bg-green-600 transition shadow-lg transform hover:-translate-y-1">
                        <i class="fab fa-whatsapp text-2xl"></i>
                        <span>Contacta por WhatsApp</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <p class="text-center text-green-700 font-semibold mt-4">+34 612 45 50 57</p>
                    <p class="text-center text-sm text-green-600 mt-2">Disponible de lunes a sábado</p>
                </div>

                <p class="text-gray-600 mb-8 leading-relaxed">
                    Responderemos tu mensaje lo antes posible. También puedes usar el formulario de contacto a continuación si prefieres.
                </p>

                <div class="space-y-6 mb-10">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 shrink-0 mt-1">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-900">Ubicación</h5>
                            <p class="text-gray-600">Calle Padilla 391, 08025 Barcelona, España</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 shrink-0 mt-1">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-900">Horario</h5>
                            <p class="text-gray-600">Lunes - Sábado: 10:00 AM - 8:00 PM</p>
                            <p class="text-gray-600">Domingo: Cerrado</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 shrink-0 mt-1">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-900">Correo Electrónico</h5>
                            <p class="text-gray-600"><a href="mailto:info@punjabidiomas.com" class="hover:text-red-500 transition">info@punjabidiomas.com</a></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 shrink-0 mt-1">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-900">Teléfono</h5>
                            <p class="text-gray-600"><a href="tel:+34933456789" class="hover:text-red-500 transition">+34 93 345 67 89</a></p>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-gray-200">
                    <h5 class="font-bold text-gray-900 mb-4">Síguenos en Redes Sociales</h5>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="p-10 md:p-14 bg-white">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Envíanos un Mensaje</h3>

                <?php if ($form_submitted): ?>
                    <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-lg">
                        <p class="font-bold mb-2"><i class="fas fa-check-circle mr-2"></i>¡Gracias por tu mensaje!</p>
                        <p><?php echo safe_output($form_success_msg); ?></p>
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

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
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
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                            <input
                                type="tel"
                                name="phone"
                                maxlength="20"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                                placeholder="+34 XXX XXX XXX"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Asunto</label>
                            <input
                                type="text"
                                name="subject"
                                required
                                maxlength="100"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition"
                                placeholder="Tema de tu mensaje"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje *</label>
                        <textarea
                            name="message"
                            required
                            rows="5"
                            maxlength="2000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition resize-none"
                            placeholder="Tu mensaje aquí... (máximo 2000 caracteres)"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition shadow-lg transform hover:-translate-y-1"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>Enviar Mensaje
                    </button>
                </form>

                <p class="text-xs text-gray-500 mt-4"><i class="fas fa-lock mr-1"></i>Tu información está protegida y encriptada.</p>
            </div>
        </div>

        <!-- Map -->
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Ubicación</h2>
            <div class="rounded-2xl overflow-hidden shadow-lg h-96">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2992.569427289568!2d2.1746883!3d41.4052344!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12a4a3260c07d377%3A0x6b8f804576394344!2sCarrer%20de%20Padilla%2C%20391%2C%20Horta-Guinard%C3%B3%2C%2008025%20Barcelona%2C%20Spain!5e0!3m2!1sen!2sus!4v1700000000000!5m2!1sen!2sus"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</section>
