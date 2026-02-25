<?php
/**
 * Detailed Courses Page
 */
?>

<section class="pt-32 pb-20 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16 max-w-3xl mx-auto">
            <h1 class="text-5xl font-bold text-gray-900 mb-6">Aprende a tu ritmo</h1>
            <p class="text-xl text-gray-600">Cursos completos con profesores nativos y metodología probada. Disponible en modalidad presencial y online.</p>
        </div>

        <!-- Course Details -->
        <div class="grid lg:grid-cols-4 gap-8 mb-16">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 sticky top-32">
                    <h3 class="font-bold text-gray-900 mb-4">Filtrar Cursos</h3>

                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Niveles</h4>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-red-500 rounded" checked>
                                <span class="text-sm text-gray-700">A1 (Principiante)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-red-500 rounded" checked>
                                <span class="text-sm text-gray-700">A2 (Elemental)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-red-500 rounded" checked>
                                <span class="text-sm text-gray-700">B1 (Intermedio)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-red-500 rounded" checked>
                                <span class="text-sm text-gray-700">B2 (Alto)</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Modalidad</h4>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-red-500 rounded" checked>
                                <span class="text-sm text-gray-700">Presencial</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-red-500 rounded" checked>
                                <span class="text-sm text-gray-700">Online</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-red-500 rounded" checked>
                                <span class="text-sm text-gray-700">Híbrida</span>
                            </label>
                        </div>
                    </div>

                    <button class="w-full py-2 px-4 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition">
                        Aplicar Filtros
                    </button>
                </div>
            </div>

            <!-- Course List -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Course Item 1 -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col md:flex-row">
                    <div class="md:w-48 h-48 bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-layer-group text-white text-4xl"></i>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">A1 - A2</span>
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">Online</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Español para Principiantes</h3>
                            <p class="text-gray-600 mb-4">Curso completo de 24 semanas para aprender español desde cero hasta el nivel A2. Incluye gramática, vocabulario y práctica conversacional.</p>
                            <div class="flex gap-6 mb-4 text-sm text-gray-600">
                                <span><i class="fas fa-calendar text-red-500 mr-2"></i>24 semanas</span>
                                <span><i class="fas fa-clock text-red-500 mr-2"></i>72 horas</span>
                                <span><i class="fas fa-users text-red-500 mr-2"></i>8-10 alumnos</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <span class="text-2xl font-bold text-gray-900">€299</span>
                            <a href="<?php echo url('contact'); ?>" class="px-6 py-2 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition">
                                Inscribirse
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Course Item 2 -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col md:flex-row">
                    <div class="md:w-48 h-48 bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-graduation-cap text-white text-4xl"></i>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-3 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">B1</span>
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">Presencial</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Preparación DELE B1 Intensivo</h3>
                            <p class="text-gray-600 mb-4">Curso intensivo de 12 semanas enfocado en la preparación del examen DELE B1. Simulacros, estrategias y material oficial.</p>
                            <div class="flex gap-6 mb-4 text-sm text-gray-600">
                                <span><i class="fas fa-calendar text-red-500 mr-2"></i>12 semanas</span>
                                <span><i class="fas fa-clock text-red-500 mr-2"></i>48 horas</span>
                                <span><i class="fas fa-users text-red-500 mr-2"></i>6-8 alumnos</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <span class="text-2xl font-bold text-gray-900">€399</span>
                            <a href="<?php echo url('contact'); ?>" class="px-6 py-2 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition">
                                Inscribirse
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Course Item 3 -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition flex flex-col md:flex-row">
                    <div class="md:w-48 h-48 bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-taxi text-white text-4xl"></i>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">B1 Especializado</span>
                                <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">Híbrida</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Español B1 para Taxistas</h3>
                            <p class="text-gray-600 mb-4">Curso especializado para profesionales del taxi que necesitan acreditar el nivel B1. Vocabulario profesional y comunicación real.</p>
                            <div class="flex gap-6 mb-4 text-sm text-gray-600">
                                <span><i class="fas fa-calendar text-red-500 mr-2"></i>8 semanas</span>
                                <span><i class="fas fa-clock text-red-500 mr-2"></i>32 horas</span>
                                <span><i class="fas fa-users text-red-500 mr-2"></i>5-6 alumnos</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <span class="text-2xl font-bold text-gray-900">€349</span>
                            <a href="<?php echo url('contact'); ?>" class="px-6 py-2 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition">
                                Inscribirse
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Choose Us -->
        <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-2xl border border-red-200 p-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">¿Por qué elegir a Punjab Idiomas?</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-md">
                        <i class="fas fa-user-tie text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Profesores Nativos</h3>
                    <p class="text-gray-600 text-sm">Experiencia de habla natural y auténtica pronunciación</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-md">
                        <i class="fas fa-chart-line text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Resultados Probados</h3>
                    <p class="text-gray-600 text-sm">95% de tasa de aprobación en exámenes DELE</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-md">
                        <i class="fas fa-handshake text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Apoyo Integral</h3>
                    <p class="text-gray-600 text-sm">Acompañamiento personal durante todo tu aprendizaje</p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">¿No encuentras el curso que buscas?</h2>
            <a href="<?php echo url('contact'); ?>" class="px-8 py-4 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition shadow-lg transform hover:-translate-y-1 inline-block">
                Contacta con nosotros
            </a>
        </div>
    </div>
</section>
