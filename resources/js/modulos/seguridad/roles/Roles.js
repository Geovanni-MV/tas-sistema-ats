import $ from 'jquery';
import DataTable from 'datatables.net';
import { Api } from '../../../core/Api';
import { crearRolesConfig } from './RolesConfig';

DataTable.use($);

/**
 * Módulo principal de roles de seguridad.
 *
 * Flujo:
 * API REST -> estado.registros -> DataTable -> interfaz.
 *
 * La colección recibida desde el servidor se conserva localmente para
 * reutilizarla en edición y cambio de estatus sin volver a solicitar el
 * registro individual al backend.
 */
const Roles = {
    config: null,

    estado: {
        registros: [],
        registroSeleccionado: null,
        tabla: null,
        guardando: false,

    },

    peticiones: {
        obtener: async function () {
            return Api.get(Roles.config.urls.obtener);
        },

        crear: async function (payload) {
            return Api.post(Roles.config.urls.crear, payload);
        },

        actualizar: async function (idRol, payload) {
            const url = Roles.config.urls.actualizar.replace('__ID__', idRol);
            return Api.put(url, payload);
        },

        // La API alterna el valor actual de "activo", por eso no requiere cuerpo.
        cambiarEstatus: async function (idRol) {
            const url = Roles.config.urls.cambiarEstatus.replace('__ID__', idRol);
            return Api.patch(url, {});
        },

    },

    obtenerRegistroPorId: function (idRol) {
        return this.estado.registros.find(
            registro => Number(registro.id_rol) === Number(idRol),
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
            const { cargandoTabla, tabla } = Roles.config.selectores;

            $(cargandoTabla).toggleClass('hidden', !activo).toggleClass('flex', activo);
            $(tabla).attr('aria-busy', activo);
        },

        kpis: function (activo) {
            const { kpiTotal, kpiActivos, kpiInactivos } = Roles.config.selectores;

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
            const s = Roles.config.selectores;

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
            const selector = Roles.config.selectores.tabla;

            if (!$(selector).length) {
                return;
            }

            Roles.estado.tabla = new DataTable(selector, {
                data: [],
                pageLength: Roles.config.datatable.pageLength,
                responsive: Roles.config.datatable.responsive,
                autoWidth: Roles.config.datatable.autoWidth,
                processing: Roles.config.datatable.processing,
                paging: Roles.config.datatable.paging,
                searching: Roles.config.datatable.searching,
                ordering: Roles.config.datatable.ordering,
                info: false,
                lengthChange: false,
                order: [[0, 'asc']],
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
                columns: [
                    { title: 'ID', data: 'id_rol' },
                    { title: 'Nombre', data: 'nombre_rol' },
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
                            return `<span class="rounded border border-blue-200 bg-blue-50 px-2 py-1 font-mono text-xs font-semibold text-blue-700">#ROL-${String(data).padStart(3, '0')}</span>`;
                        },
                    },
                    {
                        targets: 1,
                        render: function (data) {
                            return `<div class="font-semibold text-slate-900">${Roles.escaparHtml(data ?? '')}</div>`;
                        },
                    },
                    {
                        targets: 2,
                        render: function (data) {
                            const valor = data || '—';
                            return `<div class="max-w-md truncate text-slate-500" title="${Roles.escaparHtml(valor)}">${Roles.escaparHtml(valor)}</div>`;
                        },
                    },
                    {
                        targets: 3,
                        render: function (data, type) {
                            if (!data) return '—';
                            if (type === 'sort' || type === 'type') return new Date(data).getTime();
                            return Roles.formatearFecha(data);
                        },
                    },
                    {
                        targets: 4,
                        render: function (data, type) {
                            if (!data) return '—';
                            if (type === 'sort' || type === 'type') return new Date(data).getTime();
                            return Roles.formatearFecha(data);
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
                            return `<div class="flex justify-center gap-2"><button type="button" class="btn-editar rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700" data-id-rol="${row.id_rol}">Editar</button><a href="${Roles.config.urls.permisos}?id_rol=${row.id_rol}" class="btn-permisos rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700">Permisos</a><button type="button" class="btn-cambiar-estatus rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700" data-id-rol="${row.id_rol}">${row.activo ? 'Desactivar' : 'Activar'}</button></div>`;
                        },
                    },
                ],
                language: {
                    zeroRecords: 'No hay registros que coincidan con los filtros.',
                    emptyTable: 'No hay registros disponibles.',
                },
            });

            Roles.estado.tabla.on('draw', () => this.actualizarMeta());
            this.inicializarEventos();
            this.actualizarMeta();
        },

        actualizar: function (registros, conservarPagina = true) {
            if (!Roles.estado.tabla) return;

            const paginaActual = Roles.estado.tabla.page();
            Roles.estado.tabla.clear();
            Roles.estado.tabla.rows.add(registros);
            Roles.estado.tabla.draw(false);

            if (conservarPagina) {
                const paginas = Roles.estado.tabla.page.info().pages;
                Roles.estado.tabla.page(Math.min(paginaActual, Math.max(paginas - 1, 0))).draw('page');
            }
        },

        inicializarEventos: function () {
            const selectores = Roles.config.selectores;
            const columnaActivo = Roles.config.datatable.columnaActivo;

            $(selectores.buscar).on('input', function () {
                Roles.estado.tabla.search($(this).val()).draw();
            });

            $(selectores.filas).on('change', function () {
                Roles.estado.tabla.page.len(Number($(this).val())).draw();
            });

            $(selectores.estado).on('change', function () {
                const valor = $(this).val();
                const patron = valor === '' ? '' : (valor === '1' ? '^Activo$' : '^Inactivo$');
                Roles.estado.tabla.column(columnaActivo).search(patron, true, false).draw();
            });

            $(selectores.limpiarFiltros).on('click', function () {
                $(selectores.buscar).val('');
                $(selectores.estado).val('');
                $(selectores.filas).val('10');
                Roles.estado.tabla.search('').column(columnaActivo).search('').page.len(10).draw();
            });

            $(document).on('click', `${selectores.tabla} .btn-editar`, function () {
                const idRol = $(this).data('id-rol');
                Roles.formulario.editar(idRol);
            });

            $(document).on('click', `${selectores.tabla} .btn-cambiar-estatus`, async function () {
                const $boton = $(this);
                const idRol = $boton.data('id-rol');
                const registro = Roles.obtenerRegistroPorId(idRol);

                if (!registro || $boton.prop('disabled')) return;

                Roles.cargando.boton($boton, true, Roles.config.textos.procesando);

                try {
                    await Roles.peticiones.cambiarEstatus(idRol);
                    await Roles.cargarDatos(true);
                } catch (error) {
                    console.error(Roles.obtenerMensajeError(error));
                } finally {
                    Roles.cargando.boton($boton, false);
                }
            });

        },

        actualizarMeta: function () {
            if (!Roles.estado.tabla) return;

            const info = Roles.estado.tabla.page.info();
            $(Roles.config.selectores.visibles).text(Math.max(info.end - info.start, 0));
            $(Roles.config.selectores.filtrados).text(info.recordsDisplay);
            $(Roles.config.selectores.totalRegistros).text(info.recordsDisplay);
            this.renderizarPaginacion(info);
        },

        renderizarPaginacion: function (info) {
            const $contenedor = $(Roles.config.selectores.paginacion);
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
                    Roles.estado.tabla.page(pagina).draw('page');
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
            return Roles.config.campos;
        },

        mostrar: function (campo, mensaje) {
            const definicion = this.definiciones()[campo];
            if (!definicion) return;

            const { duracionErrorMs } = Roles.config.mensajes;
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
                vaciar: setTimeout(vaciar, Roles.config.mensajes.animacionMs),
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
            $(Roles.config.selectores.modal).removeClass('hidden').addClass('flex');
        },

        cerrar: function () {
            if (Roles.estado.guardando) return;

            $(Roles.config.selectores.modal).removeClass('flex').addClass('hidden');
            Roles.formulario.limpiar();
        },

        inicializarEventos: function () {
            const selectores = Roles.config.selectores;

            $(selectores.nuevo).on('click', function () {
                Roles.formulario.limpiar();
                $(selectores.modalTitulo).text(Roles.config.textos.nuevo);
                Roles.modal.abrir();
            });

            $(selectores.cerrarModal + ', ' + selectores.cancelarModal).on('click', function () {
                Roles.modal.cerrar();
            });

            $(selectores.modal).on('click', function (evento) {
                if ($(evento.target).is(selectores.modal)) {
                    Roles.modal.cerrar();
                }
            });
        },
    },

    formulario: {
        limpiar: function () {
            const selectores = Roles.config.selectores;
            Roles.estado.registroSeleccionado = null;
            $(selectores.id).val('0');
            $(selectores.nombre).val('');
            $(selectores.descripcion).val('');
            $(selectores.modalTitulo).text(Roles.config.textos.nuevo);
            Roles.errores.limpiar(false);
        },

        editar: function (idRol) {
            const registro = Roles.obtenerRegistroPorId(idRol);
            if (!registro) return;

            const selectores = Roles.config.selectores;
            Roles.errores.limpiar(false);
            Roles.estado.registroSeleccionado = registro;
            $(selectores.id).val(registro.id_rol);
            $(selectores.nombre).val(registro.nombre_rol ?? '');
            $(selectores.descripcion).val(registro.descripcion ?? '');
            $(selectores.modalTitulo).text(Roles.config.textos.editar);
            Roles.modal.abrir();
        },

        obtenerPayload: function (esEdicion = false) {
            const selectores = Roles.config.selectores;
            return {
                nombre_rol: String($(selectores.nombre).val() ?? '').trim(),
                descripcion: String($(selectores.descripcion).val() ?? '').trim() || null,
            };
        },

        guardar: async function () {
            const selectores = Roles.config.selectores;
            const idRol = Number($(selectores.id).val() ?? 0);
            const payload = this.obtenerPayload(idRol > 0);
            const $boton = $(selectores.guardar);

            if (Roles.estado.guardando) return;

            Roles.errores.limpiar(false);
            Roles.estado.guardando = true;
            Roles.cargando.boton($boton, true, Roles.config.textos.guardando);
            Roles.cargando.modal(true);

            try {
                if (idRol > 0) {
                    await Roles.peticiones.actualizar(idRol, payload);
                } else {
                    await Roles.peticiones.crear(payload);
                }

                Roles.estado.guardando = false;
                Roles.modal.cerrar();
                Roles.cargarDatos(true);
            } catch (error) {
                if (!Roles.errores.mostrarDesdeServidor(error)) {
                    console.error(Roles.obtenerMensajeError(error));
                }
            } finally {
                Roles.estado.guardando = false;
                Roles.cargando.boton($boton, false);
                Roles.cargando.modal(false);
            }
        },

        inicializarEventos: function () {
            Object.entries(Roles.config.campos).forEach(([campo, { input }]) => {
                $(input).on('input change', function () {
                    Roles.errores.ocultar(campo);
                });
            });

            $(Roles.config.selectores.guardar).on('click', function (evento) {
                evento.preventDefault();
                Roles.formulario.guardar();
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
        if (!$('#roles-config').length) return;

        this.config = crearRolesConfig();
        this.tabla.inicializar();
        this.modal.inicializarEventos();
        this.formulario.inicializarEventos();

        await this.cargarDatos(false);
    },
};

$(function () {
    Roles.inicializar();
});

export default Roles;
