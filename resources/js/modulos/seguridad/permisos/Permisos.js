import $ from 'jquery';
import DataTable from 'datatables.net';
import { Api } from '../../../core/Api';
import { crearPermisosConfig } from './PermisosConfig';

DataTable.use($);

/**
 * Módulo principal de permisos de seguridad.
 *
 * Flujo:
 * API REST -> estado.registros -> DataTable -> interfaz.
 *
 * La colección recibida desde el servidor se conserva localmente para
 * reutilizarla en edición y cambio de estatus sin volver a solicitar el
 * registro individual al backend.
 */
const Permisos = {
    config: null,

    estado: {
        registros: [],
        registroSeleccionado: null,
        tabla: null,
        guardando: false,

    },

    peticiones: {
        obtener: async function () {
            return Api.get(Permisos.config.urls.obtener);
        },

        crear: async function (payload) {
            return Api.post(Permisos.config.urls.crear, payload);
        },

        actualizar: async function (idPermiso, payload) {
            const url = Permisos.config.urls.actualizar.replace('__ID__', idPermiso);
            return Api.put(url, payload);
        },

        // La API alterna el valor actual de "activo", por eso no requiere cuerpo.
        cambiarEstatus: async function (idPermiso) {
            const url = Permisos.config.urls.cambiarEstatus.replace('__ID__', idPermiso);
            return Api.patch(url, {});
        },

    },

    obtenerRegistroPorId: function (idPermiso) {
        return this.estado.registros.find(
            registro => Number(registro.id_permiso) === Number(idPermiso),
        ) ?? null;
    },

    cargarDatos: async function (conservarPagina = true) {
        this.cargando.tabla(true);
        this.cargando.kpis(true);

        try {
            const respuesta = await this.peticiones.obtener();
            this.estado.registros = respuesta.data ?? [];
            this.tabla.actualizar(this.estado.registros, conservarPagina);
            this.actualizarKpis();
        } catch (error) {
            console.error(this.obtenerMensajeError(error));
        } finally {
            this.cargando.tabla(false);
            this.cargando.kpis(false);
        }
    },

    /**
     * Estados de carga reutilizables del módulo.
     */
    cargando: {
        spinner: function (clase = 'h-4 w-4') {
            return `<svg class="${clase} animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path></svg>`;
        },

        tabla: function (activo) {
            const { cargandoTabla, tabla } = Permisos.config.selectores;

            $(cargandoTabla).toggleClass('hidden', !activo).toggleClass('flex', activo);
            $(tabla).attr('aria-busy', activo);
        },

        kpis: function (activo) {
            const { kpiTotal, kpiActivos, kpiInactivos } = Permisos.config.selectores;

            $(`${kpiTotal}, ${kpiActivos}, ${kpiInactivos}`).toggleClass('animate-pulse', activo);
        },

        boton: function ($boton, activo, texto) {
            if (activo) {
                if ($boton.data('html-original') === undefined) {
                    $boton.data('html-original', $boton.html());
                }

                $boton
                    .prop('disabled', true)
                    .addClass('cursor-not-allowed opacity-70')
                    .html(`<span class="inline-flex items-center gap-2">${this.spinner()}${texto}</span>`);
                return;
            }

            if ($boton.data('html-original') !== undefined) {
                $boton.html($boton.data('html-original')).removeData('html-original');
            }

            $boton.prop('disabled', false).removeClass('cursor-not-allowed opacity-70');
        },

        modal: function (activo) {
            const s = Permisos.config.selectores;

            $(`${s.nombre}, ${s.descripcion}, ${s.cancelarModal}, ${s.cerrarModal}`).prop('disabled', activo);
        },
    },

    actualizarKpis: function () {
        const total = this.estado.registros.length;
        const activos = this.estado.registros.filter(registro => Boolean(registro.activo)).length;
        const inactivos = total - activos;

        $(this.config.selectores.kpiTotal).text(total);
        $(this.config.selectores.kpiActivos).text(activos);
        $(this.config.selectores.kpiInactivos).text(inactivos);
    },

    obtenerMensajeError: function (error) {
        return error?.responseJSON?.mensaje
            ?? error?.responseJSON?.message
            ?? error?.statusText
            ?? 'Ocurrió un error inesperado.';
    },

    tabla: {
        inicializar: function () {
            const selector = Permisos.config.selectores.tabla;

            if (!$(selector).length) {
                return;
            }

            Permisos.estado.tabla = new DataTable(selector, {
                data: [],
                pageLength: Permisos.config.datatable.pageLength,
                responsive: Permisos.config.datatable.responsive,
                autoWidth: Permisos.config.datatable.autoWidth,
                processing: Permisos.config.datatable.processing,
                paging: Permisos.config.datatable.paging,
                searching: Permisos.config.datatable.searching,
                ordering: Permisos.config.datatable.ordering,
                info: false,
                lengthChange: false,
                order: [[0, 'asc']],
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
                columns: [
                    { title: 'ID', data: 'id_permiso' },
                    { title: 'Nombre', data: 'nombre_permiso' },
                    { title: 'Descripcion', data: 'descripcion' },
                    { title: 'Fecha Registro', data: 'fecha_registro' },
                    { title: 'Fecha Actualizacion', data: 'fecha_actualizacion' },
                    { title: 'Activo', data: 'activo' },
                    { title: 'Acciones', data: null },
                ],
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data) {
                            return `<span class="rounded border border-blue-200 bg-blue-50 px-2 py-1 font-mono text-xs font-semibold text-blue-700">#PER-${String(data).padStart(3, '0')}</span>`;
                        },
                    },
                    {
                        targets: 1,
                        render: function (data) {
                            return `<div class="font-semibold text-slate-900">${Permisos.escaparHtml(data ?? '')}</div>`;
                        },
                    },
                    {
                        targets: 2,
                        render: function (data) {
                            const valor = data || '—';
                            return `<div class="max-w-md truncate text-slate-500" title="${Permisos.escaparHtml(valor)}">${Permisos.escaparHtml(valor)}</div>`;
                        },
                    },
                    {
                        targets: 3,
                        render: function (data, type) {
                            if (!data) return '—';
                            if (type === 'sort' || type === 'type') return new Date(data).getTime();
                            return Permisos.formatearFecha(data);
                        },
                    },
                    {
                        targets: 4,
                        render: function (data, type) {
                            if (!data) return '—';
                            if (type === 'sort' || type === 'type') return new Date(data).getTime();
                            return Permisos.formatearFecha(data);
                        },
                    },
                    {
                        targets: 5,
                        render: function (data) {
                            const activo = Boolean(data);
                            return `<span class="inline-flex items-center gap-1.5 rounded-full ${activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'} px-2.5 py-1 text-xs font-semibold"><span class="h-1.5 w-1.5 rounded-full ${activo ? 'bg-emerald-500' : 'bg-slate-400'}"></span>${activo ? 'Activo' : 'Inactivo'}</span>`;
                        },
                    },
                    {
                        targets: 6,
                        orderable: false,
                        searchable: false,
                        render: function (_data, _type, row) {
                            return `<div class="flex justify-center gap-2"><button type="button" class="btn-editar rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700" data-id-permiso="${row.id_permiso}">Editar</button><button type="button" class="btn-cambiar-estatus rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700" data-id-permiso="${row.id_permiso}">${row.activo ? 'Desactivar' : 'Activar'}</button></div>`;
                        },
                    },
                ],
                language: {
                    zeroRecords: 'No hay registros que coincidan con los filtros.',
                    emptyTable: 'No hay registros disponibles.',
                },
            });

            Permisos.estado.tabla.on('draw', () => this.actualizarMeta());
            this.inicializarEventos();
            this.actualizarMeta();
        },

        actualizar: function (registros, conservarPagina = true) {
            if (!Permisos.estado.tabla) return;

            const paginaActual = Permisos.estado.tabla.page();
            Permisos.estado.tabla.clear();
            Permisos.estado.tabla.rows.add(registros);
            Permisos.estado.tabla.draw(false);

            if (conservarPagina) {
                const paginas = Permisos.estado.tabla.page.info().pages;
                Permisos.estado.tabla.page(Math.min(paginaActual, Math.max(paginas - 1, 0))).draw('page');
            }
        },

        inicializarEventos: function () {
            const selectores = Permisos.config.selectores;
            const columnaActivo = Permisos.config.datatable.columnaActivo;

            $(selectores.buscar).on('input', function () {
                Permisos.estado.tabla.search($(this).val()).draw();
            });

            $(selectores.filas).on('change', function () {
                Permisos.estado.tabla.page.len(Number($(this).val())).draw();
            });

            $(selectores.estado).on('change', function () {
                const valor = $(this).val();
                const patron = valor === '' ? '' : (valor === '1' ? '^Activo$' : '^Inactivo$');
                Permisos.estado.tabla.column(columnaActivo).search(patron, true, false).draw();
            });

            $(selectores.limpiarFiltros).on('click', function () {
                $(selectores.buscar).val('');
                $(selectores.estado).val('');
                $(selectores.filas).val('10');
                Permisos.estado.tabla.search('').column(columnaActivo).search('').page.len(10).draw();
            });

            $(document).on('click', `${selectores.tabla} .btn-editar`, function () {
                const idPermiso = $(this).data('id-permiso');
                Permisos.formulario.editar(idPermiso);
            });

            $(document).on('click', `${selectores.tabla} .btn-cambiar-estatus`, async function () {
                const $boton = $(this);
                const idPermiso = $boton.data('id-permiso');
                const registro = Permisos.obtenerRegistroPorId(idPermiso);

                if (!registro || $boton.prop('disabled')) return;

                Permisos.cargando.boton($boton, true, Permisos.config.textos.procesando);

                try {
                    await Permisos.peticiones.cambiarEstatus(idPermiso);
                    await Permisos.cargarDatos(true);
                } catch (error) {
                    console.error(Permisos.obtenerMensajeError(error));
                } finally {
                    Permisos.cargando.boton($boton, false);
                }
            });

        },

        actualizarMeta: function () {
            if (!Permisos.estado.tabla) return;

            const info = Permisos.estado.tabla.page.info();
            $(Permisos.config.selectores.visibles).text(Math.max(info.end - info.start, 0));
            $(Permisos.config.selectores.filtrados).text(info.recordsDisplay);
            $(Permisos.config.selectores.totalRegistros).text(info.recordsDisplay);
            this.renderizarPaginacion(info);
        },

        renderizarPaginacion: function (info) {
            const $contenedor = $(Permisos.config.selectores.paginacion);
            $contenedor.empty();

            const crearBoton = function (texto, pagina, activo = false, deshabilitado = false) {
                const clase = activo
                    ? 'border-blue-600 bg-blue-600 text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50';

                $('<button>', {
                    type: 'button',
                    text: texto,
                    disabled: deshabilitado,
                    class: `flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm font-medium transition ${clase} disabled:cursor-not-allowed disabled:opacity-40`,
                }).on('click', function () {
                    Permisos.estado.tabla.page(pagina).draw('page');
                }).appendTo($contenedor);
            };

            crearBoton('‹', Math.max(info.page - 1, 0), false, info.page === 0);

            for (let pagina = 0; pagina < info.pages; pagina += 1) {
                crearBoton(String(pagina + 1), pagina, pagina === info.page);
            }

            crearBoton('›', Math.min(info.page + 1, Math.max(info.pages - 1, 0)), false, info.page >= info.pages - 1);
        },
    },

    /**
     * Mensajes de validación bajo cada input.
     * El contenedor tiene altura fija en el template, por lo que mostrar u
     * ocultar un mensaje no desplaza los demás campos.
     */
    errores: {
        temporizadores: {},

        // Campos que aplican según el formulario que esté abierto.
        definiciones: function () {
            return Permisos.config.campos;
        },

        mostrar: function (campo, mensaje) {
            const definicion = this.definiciones()[campo];
            if (!definicion) return;

            const { duracionErrorMs } = Permisos.config.mensajes;
            const $input = $(definicion.input);
            const $error = $(definicion.error);

            this.cancelarTemporizadores(campo);

            $error
                .removeClass('field-error-out field-error-in')
                .text(mensaje)
                .attr('title', mensaje);

            // Forzar reflow para reiniciar la animación si ya estaba visible.
            void $error[0].offsetWidth;

            $error.addClass('field-error-in');
            $input.addClass('field-invalid').attr('aria-invalid', 'true');

            this.temporizadores[campo] = {
                ocultar: setTimeout(() => this.ocultar(campo), duracionErrorMs),
            };
        },

        ocultar: function (campo, animar = true) {
            const definicion = this.definiciones()[campo];
            if (!definicion) return;

            const $input = $(definicion.input);
            const $error = $(definicion.error);

            this.cancelarTemporizadores(campo);
            $input.removeClass('field-invalid').removeAttr('aria-invalid');

            const vaciar = () => $error.removeClass('field-error-in field-error-out').text('').removeAttr('title');

            if (!animar || !$error.text()) {
                vaciar();
                return;
            }

            $error.removeClass('field-error-in').addClass('field-error-out');
            this.temporizadores[campo] = {
                vaciar: setTimeout(vaciar, Permisos.config.mensajes.animacionMs),
            };
        },

        limpiar: function (animar = false) {
            Object.keys(this.definiciones()).forEach(campo => this.ocultar(campo, animar));
        },

        cancelarTemporizadores: function (campo) {
            const actuales = this.temporizadores[campo];
            if (!actuales) return;

            Object.values(actuales).forEach(clearTimeout);
            delete this.temporizadores[campo];
        },

        /**
         * Muestra los errores de una respuesta 422 de Laravel.
         * Devuelve true si se mostró al menos un error de campo.
         */
        mostrarDesdeServidor: function (error) {
            const errores = error?.status === 422 ? error?.responseJSON?.errors : null;
            if (!errores) return false;

            const definiciones = this.definiciones();
            let primerCampo = null;

            Object.entries(errores).forEach(([campo, mensajes]) => {
                if (!definiciones[campo]) return;

                this.mostrar(campo, Array.isArray(mensajes) ? mensajes[0] : String(mensajes));
                primerCampo ??= campo;
            });

            if (primerCampo) {
                $(definiciones[primerCampo].input).trigger('focus');
            }

            return primerCampo !== null;
        },
    },

    modal: {
        abrir: function () {
            $(Permisos.config.selectores.modal).removeClass('hidden').addClass('flex');
        },

        cerrar: function () {
            if (Permisos.estado.guardando) return;

            $(Permisos.config.selectores.modal).removeClass('flex').addClass('hidden');
            Permisos.formulario.limpiar();
        },

        inicializarEventos: function () {
            const selectores = Permisos.config.selectores;

            $(selectores.nuevo).on('click', function () {
                Permisos.formulario.limpiar();
                $(selectores.modalTitulo).text(Permisos.config.textos.nuevo);
                Permisos.modal.abrir();
            });

            $(selectores.cerrarModal + ', ' + selectores.cancelarModal).on('click', function () {
                Permisos.modal.cerrar();
            });

            $(selectores.modal).on('click', function (evento) {
                if ($(evento.target).is(selectores.modal)) {
                    Permisos.modal.cerrar();
                }
            });
        },
    },

    formulario: {
        limpiar: function () {
            const selectores = Permisos.config.selectores;
            Permisos.estado.registroSeleccionado = null;
            $(selectores.id).val('0');
            $(selectores.nombre).val('');
            $(selectores.descripcion).val('');
            $(selectores.modalTitulo).text(Permisos.config.textos.nuevo);
            Permisos.errores.limpiar(false);
        },

        editar: function (idPermiso) {
            const registro = Permisos.obtenerRegistroPorId(idPermiso);
            if (!registro) return;

            const selectores = Permisos.config.selectores;
            Permisos.errores.limpiar(false);
            Permisos.estado.registroSeleccionado = registro;
            $(selectores.id).val(registro.id_permiso);
            $(selectores.nombre).val(registro.nombre_permiso ?? '');
            $(selectores.descripcion).val(registro.descripcion ?? '');
            $(selectores.modalTitulo).text(Permisos.config.textos.editar);
            Permisos.modal.abrir();
        },

        obtenerPayload: function (esEdicion = false) {
            const selectores = Permisos.config.selectores;
            return {
                nombre_permiso: String($(selectores.nombre).val() ?? '').trim(),
                descripcion: String($(selectores.descripcion).val() ?? '').trim() || null,
            };
        },

        guardar: async function () {
            const selectores = Permisos.config.selectores;
            const idPermiso = Number($(selectores.id).val() ?? 0);
            const payload = this.obtenerPayload(idPermiso > 0);
            const $boton = $(selectores.guardar);

            if (Permisos.estado.guardando) return;

            Permisos.errores.limpiar(false);
            Permisos.estado.guardando = true;
            Permisos.cargando.boton($boton, true, Permisos.config.textos.guardando);
            Permisos.cargando.modal(true);

            try {
                if (idPermiso > 0) {
                    await Permisos.peticiones.actualizar(idPermiso, payload);
                } else {
                    await Permisos.peticiones.crear(payload);
                }

                Permisos.estado.guardando = false;
                Permisos.modal.cerrar();
                Permisos.cargarDatos(true);
            } catch (error) {
                if (!Permisos.errores.mostrarDesdeServidor(error)) {
                    console.error(Permisos.obtenerMensajeError(error));
                }
            } finally {
                Permisos.estado.guardando = false;
                Permisos.cargando.boton($boton, false);
                Permisos.cargando.modal(false);
            }
        },

        inicializarEventos: function () {
            Object.entries(Permisos.config.campos).forEach(([campo, { input }]) => {
                $(input).on('input change', function () {
                    Permisos.errores.ocultar(campo);
                });
            });

            $(Permisos.config.selectores.guardar).on('click', function (evento) {
                evento.preventDefault();
                Permisos.formulario.guardar();
            });
        },
    },

    formatearFecha: function (valor) {
        const fecha = new Date(valor);
        if (Number.isNaN(fecha.getTime())) return '—';

        return new Intl.DateTimeFormat('es-MX', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(fecha);
    },

    escaparHtml: function (valor) {
        return $('<div>').text(String(valor ?? '')).html();
    },

    inicializar: async function () {
        if (!$('#permisos-config').length) return;

        this.config = crearPermisosConfig();
        this.tabla.inicializar();
        this.modal.inicializarEventos();
        this.formulario.inicializarEventos();

        await this.cargarDatos(false);
    },
};

$(function () {
    Permisos.inicializar();
});

export default Permisos;
