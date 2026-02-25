<?php
/**
 * Unauthorized access page
 */

// If user is not logged in, send to login
if (empty($_SESSION['logged_in'])) {
    header('Location: ' . url('login'));
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Usuario';
?>

<section class="pt-32 pb-20 bg-gray-50 min-h-screen">
    <div class="container mx-auto px-6">
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Acceso denegado</h1>
            <p class="text-sm text-gray-600 mb-4">Lo sentimos, <?php echo sanitize($user_name); ?>. No tienes permisos para acceder a esta sección.</p>
            <div class="flex items-center justify-center gap-4">
                <a href="<?php echo url('dashboard'); ?>" class="px-4 py-2 bg-gray-100 rounded hover:bg-red-50">Volver al dashboard</a>
                <?php if (is_role('admin')): ?>
                    <a href="<?php echo url('admin'); ?>" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Ir al panel de administración</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
