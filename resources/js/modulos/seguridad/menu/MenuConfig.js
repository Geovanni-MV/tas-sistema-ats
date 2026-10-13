import $ from 'jquery';

/**
 * Configuración central del módulo opciones de menú.
 * Aquí se concentran URLs, selectores, textos y opciones reutilizables.
 */
export const crearMenuConfig = function () {
    const $config = $('#menu-config');

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
            padre: '#id_menu_padre',
            nombre: '#nombre_menu',
            ruta: '#ruta',
            icono: '#icono_clase',
            orden: '#orden',
            esPadre: '#es_padre',
            esColapsable: '#es_colapsable',
            descripcion: '#descripcion',
        },

        // Campos del formulario que pueden mostrar errores de validación.
        // La clave coincide con el nombre del campo que devuelve la API.
        campos: {
            id_menu_padre: { input: '#id_menu_padre', error: '#error-id_menu_padre' },
            nombre_menu: { input: '#nombre_menu', error: '#error-nombre_menu' },
            ruta: { input: '#ruta', error: '#error-ruta' },
            icono_clase: { input: '#icono_clase', error: '#error-icono_clase' },
            orden: { input: '#orden', error: '#error-orden' },
            es_padre: { input: '#es_padre', error: '#error-es_padre' },
            es_colapsable: { input: '#es_colapsable', error: '#error-es_colapsable' },
            descripcion: { input: '#descripcion', error: '#error-descripcion' },
        },

        mensajes: {
            // Tiempo que permanece visible un mensaje de error (ms).
            duracionErrorMs: 7000,
            // Duración de la animación de salida (debe coincidir con app.css).
            animacionMs: 220,
        },

        entidad: {
            id: 'id_menu',
            idPadre: 'id_menu_padre',
            nombre: 'nombre_menu',
            ruta: 'ruta',
            icono: 'icono_clase',
            orden: 'orden',
            esPadre: 'es_padre',
            esColapsable: 'es_colapsable',
            descripcion: 'descripcion',
            fechaRegistro: 'fecha_registro',
            fechaActualizacion: 'fecha_actualizacion',
            activo: 'activo',
        },

        textos: {
            nuevo: 'Registrar opción de menú',
            editar: 'Actualizar opción de menú',
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
            columnaActivo: 6, // índice de la columna "Activo" (filtro por estatus)
        },
    };
};
