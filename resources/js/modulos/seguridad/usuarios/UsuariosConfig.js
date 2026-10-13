import $ from 'jquery';

/**
 * Configuración central del módulo usuarios.
 * Aquí se concentran URLs, selectores, textos y opciones reutilizables.
 */
export const crearUsuariosConfig = function () {
    const $config = $('#usuarios-config');

    return {
        urls: {
            obtener: $config.data('url-obtener'),
            crear: $config.data('url-crear'),
            actualizar: $config.data('url-actualizar'),
            cambiarEstatus: $config.data('url-cambiar-estatus'),
            cambiarClave: $config.data('url-cambiar-clave'),
            obtenerRoles: $config.data('url-roles'),
            obtenerEmpresas: $config.data('url-empresas'),
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
            nombres: '#nombres',
            apellidos: '#apellidos',
            telefono: '#telefono',
            correo: '#correo_electronico',
            empresa: '#id_empresa',
            rol: '#id_rol',
            clave: '#clave',
            grupoClave: '#grupo-clave',
            // Modal "Cambiar clave"
            modalClave: '#clave-modal',
            claveSubtitulo: '#clave-modal-subtitle',
            claveId: '#clave-id',
            claveInput: '#nueva-clave',
            claveGuardar: '#clave-save',
            claveCancelar: '#clave-cancel',
            claveCerrar: '#clave-close',
        },

        // Campos del formulario que pueden mostrar errores de validación.
        // La clave coincide con el nombre del campo que devuelve la API.
        campos: {
            nombres: { input: '#nombres', error: '#error-nombres' },
            apellidos: { input: '#apellidos', error: '#error-apellidos' },
            telefono: { input: '#telefono', error: '#error-telefono' },
            correo_electronico: { input: '#correo_electronico', error: '#error-correo_electronico' },
            id_empresa: { input: '#id_empresa', error: '#error-id_empresa' },
            id_rol: { input: '#id_rol', error: '#error-id_rol' },
            clave: { input: '#clave', error: '#error-clave' },
        },

        // Campos del modal "Cambiar clave".
        camposClave: {
            clave: { input: '#nueva-clave', error: '#error-nueva-clave' },
        },

        mensajes: {
            // Tiempo que permanece visible un mensaje de error (ms).
            duracionErrorMs: 7000,
            // Duración de la animación de salida (debe coincidir con app.css).
            animacionMs: 220,
        },

        entidad: {
            id: 'id_usuario',
            nombres: 'nombres',
            apellidos: 'apellidos',
            telefono: 'telefono',
            correoElectronico: 'correo_electronico',
            idEmpresa: 'id_empresa',
            idRol: 'id_rol',
            fechaRegistro: 'fecha_registro',
            fechaActualizacion: 'fecha_actualizacion',
            activo: 'activo',
        },

        textos: {
            nuevo: 'Registrar usuario',
            editar: 'Actualizar usuario',
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
            columnaActivo: 5, // índice de la columna "Activo" (filtro por estatus)
        },
    };
};
