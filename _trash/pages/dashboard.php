<?php
/**
 * Dashboard Page (Student Portal)
 */

// require login
if (empty($_SESSION['logged_in'])) {
    header('Location: ' . url('login'));
    exit;
}

// determine role
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'student';

// Get user info from session
$user_email = $_SESSION['user_email'] ?? 'Estudiante';
$user_name = $_SESSION['user_name'] ?? 'Bienvenido';
$login_time = $_SESSION['login_time'] ?? time();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . url('home'));
    exit;
}
?>

<section class="pt-32 pb-20 bg-gray-50 min-h-screen">
    <div class="container mx-auto px-6">
        <div class="grid lg:grid-cols-4 gap-6 mb-8">
            <div class="lg:col-span-3">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Bienvenido, <?php echo safe_output($user_name); ?></h1>
                    <p class="text-gray-600">Panel de control de tu aprendizaje en Punjab Idiomas</p>

                    <h2 class="text-2xl font-bold"><?php echo $user_role === 'admin' ? 'Admin Dashboard' : 'Dashboard'; ?></h2>
                    <p class="mt-2">Welcome back, <?php echo sanitize($_SESSION['user_name'] ?? 'User'); ?>.</p>

                    <?php if ($user_role === 'admin'): ?>
                        <div class="mt-4 bg-white p-4 rounded shadow">
                            <h3 class="font-semibold">Administrator Controls</h3>
                            <ul class="list-disc ml-5 mt-2 text-sm">
                                <li><a href="<?php echo url('admin'); ?>" class="text-blue-600 hover:underline">Manage users</a></li>
                                <li>View site logs</li>
                                <li>Site settings</li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 bg-white p-4 rounded shadow">
                            <p class="text-sm">This is your user dashboard. More features coming soon.</p>
                        </div>
                    <?php endif; ?>
                <p class="text-sm text-gray-500 mt-2"><i class="fas fa-envelope mr-2"></i><?php echo safe_output($user_email); ?></p>
            </div>
            <div class="flex items-center justify-end">
                <a href="<?php echo url('dashboard', ['logout' => '1']); ?>" class="px-6 py-3 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition">
                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Nivel Actual</h3>
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-red-500"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">B1</p>
                <p class="text-sm text-gray-600 mt-2">Intermedio</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Progreso</h3>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-500"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">65%</p>
                <p class="text-sm text-gray-600 mt-2">Del nivel actual</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Horas de Clase</h3>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-blue-500"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">36</p>
                <p class="text-sm text-gray-600 mt-2">Completadas</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-900">Tareas</h3>
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-tasks text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900">5</p>
                <p class="text-sm text-gray-600 mt-2">Pendientes</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Current Course -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="h-32 bg-gradient-to-r from-red-500 to-red-600"></div>
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Curso Actual: Español B1</h2>
                        <p class="text-gray-600 mb-6">Estás en la unidad 8 de 12. Falta menos para completar tu nivel B1.</p>

                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Progreso del Curso</span>
                                <span class="text-sm font-bold text-gray-700">65%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-red-500 h-3 rounded-full" style="width: 65%"></div>
                            </div>
                        </div>

                        <a href="<?php echo url('courses'); ?>" class="inline-block px-6 py-2 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition">
                            Continuar Estudiando
                        </a>
                    </div>
                </div>

                <!-- Upcoming Classes -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-red-500"></i>
                            Próximas Clases
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div class="p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-gray-900">Subjuntivo - Uso en cláusulas nominales</h4>
                                    <p class="text-sm text-gray-600 mt-1"><i class="fas fa-user mr-2 text-red-500"></i>Prof. María González</p>
                                    <p class="text-sm text-gray-600"><i class="fas fa-clock mr-2 text-red-500"></i>Mañana, 6:00 PM - 7:30 PM</p>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">CONFIRMADA</span>
                            </div>
                        </div>
                        <div class="p-6 hover:bg-gray-50 transition cursor-pointer">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-gray-900">Práctica Conversacional - Temas de interés personal</h4>
                                    <p class="text-sm text-gray-600 mt-1"><i class="fas fa-user mr-2 text-red-500"></i>Prof. Juan López</p>
                                    <p class="text-sm text-gray-600"><i class="fas fa-clock mr-2 text-red-500"></i>Jueves, 7:00 PM - 8:30 PM</p>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">CONFIRMADA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Acciones Rápidas</h3>
                    <div class="space-y-2">
                        <a href="#" class="block w-full py-2 px-4 bg-gray-100 text-gray-900 rounded-lg hover:bg-red-500 hover:text-white transition font-medium text-sm text-center">
                            <i class="fas fa-file mr-2"></i>Descargar Material
                        </a>
                        <a href="#" class="block w-full py-2 px-4 bg-gray-100 text-gray-900 rounded-lg hover:bg-red-500 hover:text-white transition font-medium text-sm text-center">
                            <i class="fas fa-headphones mr-2"></i>Audio de Práctica
                        </a>
                        <a href="#" class="block w-full py-2 px-4 bg-gray-100 text-gray-900 rounded-lg hover:bg-red-500 hover:text-white transition font-medium text-sm text-center">
                            <i class="fas fa-video mr-2"></i>Tutoriales Video
                        </a>
                        <a href="<?php echo url('contact'); ?>" class="block w-full py-2 px-4 bg-gray-100 text-gray-900 rounded-lg hover:bg-red-500 hover:text-white transition font-medium text-sm text-center">
                            <i class="fas fa-envelope mr-2"></i>Contactar Profesor
                        </a>
                    </div>
                </div>

                <!-- Upcoming Test -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl border border-red-200 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-3"><i class="fas fa-exclamation-circle text-red-600 mr-2"></i>Examen Próximo</h3>
                    <p class="text-sm text-gray-700 mb-4">Tu examen de simulación DELE B1 está programado para:</p>
                    <p class="text-2xl font-bold text-gray-900 mb-4">15 de Marzo</p>
                    <a href="#" class="block w-full py-2 px-4 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-bold text-sm text-center">
                        Ver Detalles
                    </a>
                </div>

                <!-- Resources -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Recursos Útiles</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="text-red-500 hover:text-red-600 font-medium flex items-center gap-2"><i class="fas fa-file-pdf"></i>Guía DELE B1</a></li>
                        <li><a href="#" class="text-red-500 hover:text-red-600 font-medium flex items-center gap-2"><i class="fas fa-book"></i>Diccionario Online</a></li>
                        <li><a href="#" class="text-red-500 hover:text-red-600 font-medium flex items-center gap-2"><i class="fas fa-link"></i>Foro de Estudiantes</a></li>
                        <li><a href="<?php echo url('faq'); ?>" class="text-red-500 hover:text-red-600 font-medium flex items-center gap-2"><i class="fas fa-question-circle"></i>Preguntas Frecuentes</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
