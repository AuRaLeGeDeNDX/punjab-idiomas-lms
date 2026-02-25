import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/design-system.css',
                'resources/css/creative-professional.css',
                'resources/css/editorjs-content.css',
                'resources/css/components/buttons.css',
                'resources/css/components/cards.css',
                'resources/css/components/forms.css',
                'resources/css/components/tables.css',
                'resources/css/components/alerts.css',
                'resources/css/components/modals.css',
                'resources/css/components/navigation.css',
                'resources/css/course-hierarchy.css',
                'resources/js/app.js',
                'resources/js/course-hierarchy-simple.jsx',
                'resources/js/course-assignment.jsx',
                'resources/js/editor/index.js'
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    // Optimize Editor.js dependencies for better bundling
    optimizeDeps: {
        include: [
            '@editorjs/editorjs',
            '@editorjs/header',
            '@editorjs/list',
            '@editorjs/quote',
            '@editorjs/code'
        ]
    }
});
