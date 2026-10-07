import $ from 'jquery';

/**
 * Configuración central del módulo Estados.
 */
export const crearEstadosConfig = function () {
    const $config = $('#estados-config');

    return {
        urls: {
            obtener: $config.data('url-obtener'),
            crear: $config.data('url-crear'),
            actualizar: $config.data('url-actualizar'),
            cambiarEstatus: $config.data('url-cambiar-estatus'),
        },

        selectores: {
            tabla: '#tabla-estados',
            cargandoTabla: '#table-loading',
            buscar: '#table-search',
            filas: '#per-page',
            estado: '#status-filter',
            limpiarFiltros: '#clear-filters',
            totalRegistros: '#record-count',
            visibles: '#visible-count',
            filtrados: '#filtered-count',
            paginacion: '#table-pagination',
            kpiTotal: '#kpi-total',
            kpiActivos: '#kpi-active',
            kpiInactivos: '#kpi-inactive',
            modal: '#record-modal',
            modalTitulo: '#record-modal-title',
            nuevo: '#new-record',
            cerrarModal: '#close-modal',
            cancelarModal: '#cancel',
            guardar: '#save',
            id: '#record-id',
            nombre: '#nombre_estado',
            clave: '#clave_estado',
        },

        campos: {
            nombre_estado: {
                input: '#nombre_estado',
                error: '#error-nombre_estado',
            },
            clave_estado: {
                input: '#clave_estado',
                error: '#error-clave_estado',
            },
        },

        mensajes: {
            duracionErrorMs: 7000,
            animacionMs: 220,
        },

        textos: {
            nuevo: 'Registrar estado',
            editar: 'Actualizar estado',
            guardando: 'Guardando...',
            procesando: 'Procesando...',
        },

        datatable: {
            pageLength: 10,
            responsive: false,
            autoWidth: false,
            processing: false,
            paging: true,
            searching: true,
            ordering: true,
        },
    };
};
