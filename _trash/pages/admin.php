<?php
/**
 * Admin Page (protected)
 */

// Require admin role
require_role('admin');

$user_name = $_SESSION['user_name'] ?? 'Administrador';

?>

<section class="pt-32 pb-20 bg-gray-50 min-h-screen">
    <div class="container mx-auto px-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Panel de Administración</h1>
            <p class="text-sm text-gray-600 mb-4">Bienvenido, <?php echo sanitize($user_name); ?>. Área restringida a administradores.</p>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="p-4 border rounded">
                    <h3 class="font-semibold mb-2">Usuarios</h3>
                    <p class="text-sm text-gray-600">Gestión de usuarios (próximamente).</p>
                </div>
                <div class="p-4 border rounded">
                    <h3 class="font-semibold mb-2">Registros</h3>
                    <p class="text-sm text-gray-600">Ver registros de seguridad y actividad.</p>
                </div>
            </div>
        </div>
    </div>
</section>
