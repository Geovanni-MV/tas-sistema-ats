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
            sincronizar: $config.data('url-sincronizar'),
            roles: $config.data('url-roles'),
            permisos: $config.data('url-permisos'),
            menu: $config.data('url-menu'),
        },

        selectores: {
            matriz: '#matriz',
            vacio: '#matrix-empty',
            cargando: '#matrix-loading',
            aviso: '#matrix-aviso',
            rol: '#rol-select',
            buscar: '#matrix-search',
            soloAsignadas: '#solo-asignadas',
            kpiAsignados: '#kpi-asignados',
            kpiMenus: '#kpi-menus',
            kpiPermisos: '#kpi-permisos',
            barra: '#barra-cambios',
            contador: '#contador-cambios',
            guardar: '#guardar-cambios',
            descartar: '#descartar-cambios',
            casilla: '.chk-celda',
            casillaFila: '.chk-fila',
            casillaColumna: '.chk-columna',
        },

        mensajes: {
            // Tiempo que permanece visible el aviso (ms).
            duracionAvisoMs: 7000,
        },

        textos: {
            sinRol: 'Selecciona un rol para configurar sus permisos.',
            sinCatalogos: 'Registra al menos un permiso y una opción de menú activos para configurar el acceso.',
            sinCoincidencias: 'Ninguna opción de menú coincide con los filtros.',
            sinCambios: 'Sin cambios pendientes',
            guardando: 'Guardando...',
            confirmarCambioRol: 'Hay cambios sin guardar. Si cambias de rol se perderán. ¿Deseas continuar?',
            errorGenerico: 'Ocurrió un error inesperado.',
        },
    };
};
