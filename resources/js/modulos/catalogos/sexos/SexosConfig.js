import $ from 'jquery';

/**
 * Configuración central del módulo Sexos.
 * Aquí se concentran URLs, selectores, textos y opciones reutilizables.
 */
export const crearSexosConfig = function () {
    const $config = $('#sexos-config');

    return {
        urls: {
            obtener: $config.data('url-obtener'),
            crear: $config.data('url-crear'),
            actualizar: $config.data('url-actualizar'),
            cambiarEstatus: $config.data('url-cambiar-estatus'),
        },

        selectores: {
            tabla: '#tabla-principal',
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
            nombre: '#nombre_sexo',
            descripcion: '#descripcion',
        },

        // Campos del formulario que pueden mostrar errores de validación.
        // La clave coincide con el nombre del campo que devuelve la API.
        campos: {
            nombre_sexo: { input: '#nombre_sexo', error: '#error-nombre_sexo' },
            descripcion: { input: '#descripcion', error: '#error-descripcion' },
        },

        mensajes: {
            // Tiempo que permanece visible un mensaje de error (ms).
            duracionErrorMs: 7000,
            // Duración de la animación de salida (debe coincidir con app.css).
            animacionMs: 220,
        },

        entidad: {
            id: 'id_sexo',
            nombre: 'nombre_sexo',
            descripcion: 'descripcion',
            fechaRegistro: 'fecha_registro',
            fechaActualizacion: 'fecha_actualizacion',
            activo: 'activo',
        },

        textos: {
            nuevo: 'Registrar sexo',
            editar: 'Actualizar sexo',
            guardando: 'Guardando...',
            procesando: 'Procesando...',
        },

        datatable: {
            pageLength: 10,
            responsive: false,
            autoWidth: false,
            processing: false, // el indicador de carga es propio (#table-loading)
            paging: true,
            searching: true,
            ordering: true,
        },
    };
};
