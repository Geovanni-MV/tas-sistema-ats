import $ from 'jquery';

/**
 * Configuración central del módulo Roles y permisos.
 * Aquí se concentran URLs, selectores, textos y opciones reutilizables.
 */
export const crearPermisosRolesConfig = function () {
    const $config = $('#permisos-roles-config');

    return {
        urls: {
            obtener: $config.data('url-obtener'),
            crear: $config.data('url-crear'),
            cambiarEstatus: $config.data('url-cambiar-estatus'),
            roles: $config.data('url-roles'),
            permisos: $config.data('url-permisos'),
            menu: $config.data('url-menu'),
        },

        selectores: {
            matriz: '#matriz',
            vacio: '#matrix-empty',
            cargando: '#matrix-loading',
            error: '#matrix-error',
            rol: '#rol-select',
            buscar: '#matrix-search',
            soloAsignadas: '#solo-asignadas',
            kpiAsignados: '#kpi-asignados',
            kpiMenus: '#kpi-menus',
            kpiPermisos: '#kpi-permisos',
            casilla: '.chk-celda',
            casillaFila: '.chk-fila',
            casillaColumna: '.chk-columna',
        },

        mensajes: {
            // Tiempo que permanece visible el aviso de error (ms).
            duracionErrorMs: 7000,
            // Peticiones simultáneas al aplicar cambios masivos (fila o columna).
            loteMaximo: 5,
        },

        textos: {
            sinRol: 'Selecciona un rol para configurar sus permisos.',
            sinCatalogos: 'Registra al menos un permiso y una opción de menú activos para configurar el acceso.',
            sinCoincidencias: 'Ninguna opción de menú coincide con los filtros.',
            errorGenerico: 'Ocurrió un error inesperado.',
        },
    };
};
