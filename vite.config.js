import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                // Un script de entrada por módulo (se carga desde su template con @push('scripts')).
                'resources/js/modulos/catalogos/sexos/Sexos.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
