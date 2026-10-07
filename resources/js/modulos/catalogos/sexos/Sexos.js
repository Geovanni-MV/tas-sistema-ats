import $ from 'jquery';
import DataTable from 'datatables.net';
import { Api } from '../../../core/Api';
import { crearSexosConfig } from './SexosConfig';

DataTable.use($);

/**
 * Módulo principal del catálogo Sexos.
 *
 * Flujo:
 * API REST -> estado.registros -> DataTable -> interfaz.
 *
 * La colección recibida desde el servidor se conserva localmente para
 * reutilizarla en edición y cambio de estatus sin volver a solicitar el
 * registro individual al backend.
 */
const Sexos = {
    config: null,

    estado: {
        registros: [],
        registroSeleccionado: null,
        tabla: null,
        guardando: false,
    },

    peticiones: {
        obtener: async function () {
            return Api.get(Sexos.config.urls.obtener);
        },

        crear: async function (payload) {
            return Api.post(Sexos.config.urls.crear, payload);
        },

        actualizar: async function (idSexo, payload) {
            const url = Sexos.config.urls.actualizar.replace('__ID__', idSexo);
            return Api.put(url, payload);
        },

        cambiarEstatus: async function (idSexo, payload) {
            const url = Sexos.config.urls.cambiarEstatus.replace('__ID__', idSexo);
            return Api.patch(url, payload);
        },
    },

    obtenerRegistroPorId: function (idSexo) {
        return this.estado.registros.find(
            registro => Number(registro.id_sexo) === Number(idSexo),
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
            const { cargandoTabla, tabla } = Sexos.config.selectores;

            $(cargandoTabla).toggleClass('hidden', !activo).toggleClass('flex', activo);
            $(tabla).attr('aria-busy', activo);
        },

        kpis: function (activo) {
            const { kpiTotal, kpiActivos, kpiInactivos } = Sexos.config.selectores;

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
            const s = Sexos.config.selectores;

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
            const selector = Sexos.config.selectores.tabla;

            if (!$(selector).length) {
                return;
            }

            Sexos.estado.tabla = new DataTable(selector, {
                data: [],
                pageLength: Sexos.config.datatable.pageLength,
                responsive: Sexos.config.datatable.responsive,
                autoWidth: Sexos.config.datatable.autoWidth,
                processing: Sexos.config.datatable.processing,
                paging: Sexos.config.datatable.paging,
                searching: Sexos.config.datatable.searching,
                ordering: Sexos.config.datatable.ordering,
                info: false,
                lengthChange: false,
                order: [[0, 'desc']],
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
                columns: [
                    { title: 'ID', data: 'id_sexo' },
                    { title: 'Nombre', data: 'nombre_sexo' },
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
                            return `<span class="rounded border border-blue-200 bg-blue-50 px-2 py-1 font-mono text-xs font-semibold text-blue-700">#SEX-${String(data).padStart(3, '0')}</span>`;
                        },
                    },
                    {
                        targets: 1,
                        render: function (data) {
                            return `<div class="font-semibold text-slate-900">${Sexos.escaparHtml(data ?? '')}</div>`;
                        },
                    },
                    {
                        targets: 2,
                        render: function (data) {
                            const valor = data || '—';
                            return `<div class="max-w-md truncate text-slate-500" title="${Sexos.escaparHtml(valor)}">${Sexos.escaparHtml(valor)}</div>`;
                        },
                    },
                    {
                        targets: [3, 4],
                        render: function (data, type) {
                            if (!data) return '—';
                            if (type === 'sort' || type === 'type') return new Date(data).getTime();
                            return Sexos.formatearFecha(data);
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
                            return `<div class="flex justify-center gap-2"><button type="button" class="btn-editar rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700" data-id-sexo="${row.id_sexo}">Editar</button><button type="button" class="btn-cambiar-estatus rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700" data-id-sexo="${row.id_sexo}">${row.activo ? 'Desactivar' : 'Activar'}</button></div>`;
                        },
                    },
                ],
                language: {
                    zeroRecords: 'No hay registros que coincidan con los filtros.',
                    emptyTable: 'No hay registros disponibles.',
                },
            });

            Sexos.estado.tabla.on('draw', () => this.actualizarMeta());
            this.inicializarEventos();
            this.actualizarMeta();
        },

        actualizar: function (registros, conservarPagina = true) {
            if (!Sexos.estado.tabla) return;

            const paginaActual = Sexos.estado.tabla.page();
            Sexos.estado.tabla.clear();
            Sexos.estado.tabla.rows.add(registros);
            Sexos.estado.tabla.draw(false);

            if (conservarPagina) {
                const paginas = Sexos.estado.tabla.page.info().pages;
                Sexos.estado.tabla.page(Math.min(paginaActual, Math.max(paginas - 1, 0))).draw('page');
            }
        },

        inicializarEventos: function () {
            const selectores = Sexos.config.selectores;

            $(selectores.buscar).on('input', function () {
                Sexos.estado.tabla.search($(this).val()).draw();
            });

            $(selectores.filas).on('change', function () {
                Sexos.estado.tabla.page.len(Number($(this).val())).draw();
            });

            $(selectores.estado).on('change', function () {
                const valor = $(this).val();
                const patron = valor === '' ? '' : (valor === '1' ? '^Activo$' : '^Inactivo$');
                Sexos.estado.tabla.column(5).search(patron, true, false).draw();
            });

            $(selectores.limpiarFiltros).on('click', function () {
                $(selectores.buscar).val('');
                $(selectores.estado).val('');
                $(selectores.filas).val('10');
                Sexos.estado.tabla.search('').column(5).search('').page.len(10).draw();
            });

            $(document).on('click', `${selectores.tabla} .btn-editar`, function () {
                const idSexo = $(this).data('id-sexo');
                Sexos.formulario.editar(idSexo);
            });

            $(document).on('click', `${selectores.tabla} .btn-cambiar-estatus`, async function () {
                const $boton = $(this);
                const idSexo = $boton.data('id-sexo');
                const registro = Sexos.obtenerRegistroPorId(idSexo);

                if (!registro || $boton.prop('disabled')) return;

                Sexos.cargando.boton($boton, true, Sexos.config.textos.procesando);

                try {
                    await Sexos.peticiones.cambiarEstatus(idSexo, { activo: !Boolean(registro.activo) });
                    await Sexos.cargarDatos(true);
                } catch (error) {
                    console.error(Sexos.obtenerMensajeError(error));
                } finally {
                    Sexos.cargando.boton($boton, false);
                }
            });
        },

        actualizarMeta: function () {
            if (!Sexos.estado.tabla) return;

            const info = Sexos.estado.tabla.page.info();
            $(Sexos.config.selectores.visibles).text(Math.max(info.end - info.start, 0));
            $(Sexos.config.selectores.filtrados).text(info.recordsDisplay);
            $(Sexos.config.selectores.totalRegistros).text(info.recordsDisplay);
            this.renderizarPaginacion(info);
        },

        renderizarPaginacion: function (info) {
            const $contenedor = $(Sexos.config.selectores.paginacion);
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
                    Sexos.estado.tabla.page(pagina).draw('page');
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

        mostrar: function (campo, mensaje) {
            const definicion = Sexos.config.campos[campo];
            if (!definicion) return;

            const { duracionErrorMs } = Sexos.config.mensajes;
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
            const definicion = Sexos.config.campos[campo];
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
                vaciar: setTimeout(vaciar, Sexos.config.mensajes.animacionMs),
            };
        },

        limpiar: function (animar = false) {
            Object.keys(Sexos.config.campos).forEach(campo => this.ocultar(campo, animar));
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

            let primerCampo = null;

            Object.entries(errores).forEach(([campo, mensajes]) => {
                if (!Sexos.config.campos[campo]) return;

                this.mostrar(campo, Array.isArray(mensajes) ? mensajes[0] : String(mensajes));
                primerCampo ??= campo;
            });

            if (primerCampo) {
                $(Sexos.config.campos[primerCampo].input).trigger('focus');
            }

            return primerCampo !== null;
        },
    },

    modal: {
        abrir: function () {
            $(Sexos.config.selectores.modal).removeClass('hidden').addClass('flex');
        },

        cerrar: function () {
            if (Sexos.estado.guardando) return;

            $(Sexos.config.selectores.modal).removeClass('flex').addClass('hidden');
            Sexos.formulario.limpiar();
        },

        inicializarEventos: function () {
            const selectores = Sexos.config.selectores;

            $(selectores.nuevo).on('click', function () {
                Sexos.formulario.limpiar();
                $(selectores.modalTitulo).text(Sexos.config.textos.nuevo);
                Sexos.modal.abrir();
            });

            $(selectores.cerrarModal + ', ' + selectores.cancelarModal).on('click', function () {
                Sexos.modal.cerrar();
            });

            $(selectores.modal).on('click', function (evento) {
                if ($(evento.target).is(selectores.modal)) {
                    Sexos.modal.cerrar();
                }
            });
        },
    },

    formulario: {
        limpiar: function () {
            const selectores = Sexos.config.selectores;
            Sexos.estado.registroSeleccionado = null;
            $(selectores.id).val('0');
            $(selectores.nombre).val('');
            $(selectores.descripcion).val('');
            $(selectores.modalTitulo).text(Sexos.config.textos.nuevo);
            Sexos.errores.limpiar(false);
        },

        editar: function (idSexo) {
            const registro = Sexos.obtenerRegistroPorId(idSexo);
            if (!registro) return;

            const selectores = Sexos.config.selectores;
            Sexos.errores.limpiar(false);
            Sexos.estado.registroSeleccionado = registro;
            $(selectores.id).val(registro.id_sexo);
            $(selectores.nombre).val(registro.nombre_sexo ?? '');
            $(selectores.descripcion).val(registro.descripcion ?? '');
            $(selectores.modalTitulo).text(Sexos.config.textos.editar);
            Sexos.modal.abrir();
        },

        obtenerPayload: function () {
            const selectores = Sexos.config.selectores;

            return {
                nombre_sexo: String($(selectores.nombre).val() ?? '').trim(),
                descripcion: String($(selectores.descripcion).val() ?? '').trim() || null,
            };
        },

        guardar: async function () {
            const selectores = Sexos.config.selectores;
            const idSexo = Number($(selectores.id).val() ?? 0);
            const payload = this.obtenerPayload();
            const $boton = $(selectores.guardar);

            if (Sexos.estado.guardando) return;

            Sexos.errores.limpiar(false);
            Sexos.estado.guardando = true;
            Sexos.cargando.boton($boton, true, Sexos.config.textos.guardando);
            Sexos.cargando.modal(true);

            try {
                if (idSexo > 0) {
                    await Sexos.peticiones.actualizar(idSexo, payload);
                } else {
                    await Sexos.peticiones.crear(payload);
                }

                Sexos.estado.guardando = false;
                Sexos.modal.cerrar();
                Sexos.cargarDatos(true);
            } catch (error) {
                if (!Sexos.errores.mostrarDesdeServidor(error)) {
                    console.error(Sexos.obtenerMensajeError(error));
                }
            } finally {
                Sexos.estado.guardando = false;
                Sexos.cargando.boton($boton, false);
                Sexos.cargando.modal(false);
            }
        },

        inicializarEventos: function () {
            Object.entries(Sexos.config.campos).forEach(([campo, { input }]) => {
                $(input).on('input', function () {
                    Sexos.errores.ocultar(campo);
                });
            });

            $(Sexos.config.selectores.guardar).on('click', function (evento) {
                evento.preventDefault();
                Sexos.formulario.guardar();
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
        if (!$('#sexos-config').length) return;

        this.config = crearSexosConfig();
        this.tabla.inicializar();
        this.modal.inicializarEventos();
        this.formulario.inicializarEventos();
        await this.cargarDatos(false);
    },
};

$(function () {
    Sexos.inicializar();
});

export default Sexos;
