import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { createRequire } from 'module';

const require = createRequire(import.meta.url);

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                // Un script de entrada por módulo (se carga desde su template con @push('scripts')).
                'resources/js/modulos/catalogos/sexos/Sexos.js',
                'resources/js/modulos/seguridad/usuarios/Usuarios.js',
                'resources/js/modulos/seguridad/roles/Roles.js',
                'resources/js/modulos/seguridad/permisos/Permisos.js',
                'resources/js/modulos/seguridad/menu/Menu.js',
                'resources/js/modulos/seguridad/permisos-roles/PermisosRoles.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});