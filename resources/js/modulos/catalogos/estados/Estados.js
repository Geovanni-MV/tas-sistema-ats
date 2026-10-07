import $ from 'jquery';
import DataTable from 'datatables.net';
import { Api } from '../../../core/Api';
import { crearEstadosConfig } from './EstadosConfig';

DataTable.use($);

/**
 * Módulo principal del catálogo Estados.
 *
 * Flujo:
 * API REST -> estado.registros -> DataTable -> interfaz.
 */
const Estados = {
    config: null,

    estado: {
        registros: [],
        registroSeleccionado: null,
        tabla: null,
        guardando: false,
    },

    peticiones: {
        obtener: async function () {
            return Api.get(Estados.config.urls.obtener);
        },

        crear: async function (payload) {
            return Api.post(Estados.config.urls.crear, payload);
        },

        actualizar: async function (idEstado, payload) {
            const url = Estados.config.urls.actualizar.replace('__ID__', idEstado);
            return Api.put(url, payload);
        },

        cambiarEstatus: async function (idEstado, payload) {
            const url = Estados.config.urls.cambiarEstatus.replace('__ID__', idEstado);
            return Api.patch(url, payload);
        },
    },

    obtenerRegistroPorId: function (idEstado) {
        return this.estado.registros.find(
            registro => Number(registro.id_estado) === Number(idEstado),
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

    cargando: {
        spinner: function (clase = 'h-4 w-4') {
            return `<svg class="${clase} animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path></svg>`;
        },

        tabla: function (activo) {
            const { cargandoTabla, tabla } = Estados.config.selectores;
            $(cargandoTabla).toggleClass('hidden', !activo).toggleClass('flex', activo);
            $(tabla).attr('aria-busy', activo);
        },

        kpis: function (activo) {
            const { kpiTotal, kpiActivos, kpiInactivos } = Estados.config.selectores;
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
            const s = Estados.config.selectores;
            $(`${s.nombre}, ${s.clave}, ${s.cancelarModal}, ${s.cerrarModal}`).prop('disabled', activo);
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
        return error?.responseJSON?.message
            ?? error?.responseJSON?.mensaje
            ?? error?.message
            ?? error?.statusText
            ?? 'Ocurrió un error inesperado.';
    },

    tabla: {
        inicializar: function () {
            const selector = Estados.config.selectores.tabla;

            if (!$(selector).length) {
                return;
            }

            Estados.estado.tabla = new DataTable(selector, {
                data: [],
                pageLength: Estados.config.datatable.pageLength,
                responsive: Estados.config.datatable.responsive,
                autoWidth: Estados.config.datatable.autoWidth,
                processing: Estados.config.datatable.processing,
                paging: Estados.config.datatable.paging,
                searching: Estados.config.datatable.searching,
                ordering: Estados.config.datatable.ordering,
                info: false,
                lengthChange: false,
                order: [[1, 'desc']],
                layout: {
                    topStart: null,
                    topEnd: null,
                    bottomStart: null,
                    bottomEnd: null,
                },
                columns: [
                    { title: 'ID', data: 'id_estado' },
                    { title: 'Estado', data: 'nombre_estado' },
                    { title: 'Clave', data: 'clave_estado' },
                    { title: 'Fecha Registro', data: 'fecha_registro' },
                    { title: 'Fecha Actualización', data: 'fecha_actualizacion' },
                    { title: 'Activo', data: 'activo' },
                    { title: 'Acciones', data: null },
                ],
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data) {
                            return `<span class="rounded border border-blue-200 bg-blue-50 px-2 py-1 font-mono text-xs font-semibold text-blue-700">#EST-${String(data).padStart(3, '0')}</span>`;
                        },
                    },
                    {
                        targets: 1,
                        render: function (data) {
                            return `<div class="font-semibold text-slate-900">${Estados.escaparHtml(data ?? '')}</div>`;
                        },
                    },
                    {
                        targets: 2,
                        render: function (data) {
                            return `<span class="rounded bg-slate-100 px-2 py-1 font-mono text-xs font-semibold text-slate-700">${Estados.escaparHtml(data ?? '')}</span>`;
                        },
                    },
                    {
                        targets: [3, 4],
                        render: function (data, type) {
                            if (!data) return '—';
                            if (type === 'sort' || type === 'type') {
                                return new Date(data).getTime();
                            }
                            return Estados.formatearFecha(data);
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
                            return `<div class="flex justify-center gap-2"><button type="button" class="btn-editar rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700" data-id-estado="${row.id_estado}">Editar</button><button type="button" class="btn-cambiar-estatus rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700" data-id-estado="${row.id_estado}">${row.activo ? 'Desactivar' : 'Activar'}</button></div>`;
                        },
                    },
                ],
                language: {
                    zeroRecords: 'No hay registros que coincidan con los filtros.',
                    emptyTable: 'No hay estados disponibles.',
                },
            });

            Estados.estado.tabla.on('draw', () => this.actualizarMeta());
            this.inicializarEventos();
            this.actualizarMeta();
        },

        actualizar: function (registros, conservarPagina = true) {
            if (!Estados.estado.tabla) return;

            const paginaActual = Estados.estado.tabla.page();

            Estados.estado.tabla.clear();
            Estados.estado.tabla.rows.add(registros);
            Estados.estado.tabla.draw(false);

            if (conservarPagina) {
                const paginas = Estados.estado.tabla.page.info().pages;
                Estados.estado.tabla
                    .page(Math.min(paginaActual, Math.max(paginas - 1, 0)))
                    .draw('page');
            }
        },

        inicializarEventos: function () {
            const s = Estados.config.selectores;

            $(s.buscar).on('input', function () {
                Estados.estado.tabla.search($(this).val()).draw();
            });

            $(s.filas).on('change', function () {
                Estados.estado.tabla.page.len(Number($(this).val())).draw();
            });

            $(s.estado).on('change', function () {
                const valor = $(this).val();
                const patron = valor === ''
                    ? ''
                    : (valor === '1' ? '^Activo$' : '^Inactivo$');

                Estados.estado.tabla.column(5).search(patron, true, false).draw();
            });

            $(s.limpiarFiltros).on('click', function () {
                $(s.buscar).val('');
                $(s.estado).val('');
                $(s.filas).val('10');

                Estados.estado.tabla
                    .search('')
                    .column(5)
                    .search('')
                    .page.len(10)
                    .draw();
            });

            $(document).on('click', `${s.tabla} .btn-editar`, function () {
                Estados.formulario.editar($(this).data('id-estado'));
            });

            $(document).on(
                'click',
                `${s.tabla} .btn-cambiar-estatus`,
                async function () {
                    const $boton = $(this);
                    const idEstado = $boton.data('id-estado');
                    const registro = Estados.obtenerRegistroPorId(idEstado);

                    if (!registro || $boton.prop('disabled')) return;

                    Estados.cargando.boton(
                        $boton,
                        true,
                        Estados.config.textos.procesando,
                    );

                    try {
                        await Estados.peticiones.cambiarEstatus(
                            idEstado,
                            { activo: !Boolean(registro.activo) },
                        );

                        await Estados.cargarDatos(true);
                    } catch (error) {
                        console.error(Estados.obtenerMensajeError(error));
                    } finally {
                        Estados.cargando.boton($boton, false);
                    }
                },
            );
        },

        actualizarMeta: function () {
            if (!Estados.estado.tabla) return;

            const info = Estados.estado.tabla.page.info();

            $(Estados.config.selectores.visibles)
                .text(Math.max(info.end - info.start, 0));

            $(Estados.config.selectores.filtrados)
                .text(info.recordsDisplay);

            $(Estados.config.selectores.totalRegistros)
                .text(info.recordsDisplay);

            this.renderizarPaginacion(info);
        },

        renderizarPaginacion: function (info) {
            const $contenedor = $(Estados.config.selectores.paginacion);

            $contenedor.empty();

            const crearBoton = function (
                texto,
                pagina,
                activo = false,
                deshabilitado = false,
            ) {
                const clase = activo
                    ? 'border-blue-600 bg-blue-600 text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50';

                $('<button>', {
                    type: 'button',
                    text: texto,
                    disabled: deshabilitado,
                    class: `flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm font-medium transition ${clase} disabled:cursor-not-allowed disabled:opacity-40`,
                })
                    .on('click', function () {
                        Estados.estado.tabla.page(pagina).draw('page');
                    })
                    .appendTo($contenedor);
            };

            crearBoton(
                '‹',
                Math.max(info.page - 1, 0),
                false,
                info.page === 0,
            );

            for (let pagina = 0; pagina < info.pages; pagina += 1) {
                crearBoton(
                    String(pagina + 1),
                    pagina,
                    pagina === info.page,
                );
            }

            crearBoton(
                '›',
                Math.min(info.page + 1, Math.max(info.pages - 1, 0)),
                false,
                info.page >= info.pages - 1,
            );
        },
    },

    errores: {
        temporizadores: {},

        mostrar: function (campo, mensaje) {
            const definicion = Estados.config.campos[campo];
            if (!definicion) return;

            const $input = $(definicion.input);
            const $error = $(definicion.error);

            this.cancelarTemporizadores(campo);

            $error
                .removeClass('field-error-out field-error-in')
                .text(mensaje)
                .attr('title', mensaje);

            void $error[0].offsetWidth;

            $error.addClass('field-error-in');
            $input.addClass('field-invalid').attr('aria-invalid', 'true');

            this.temporizadores[campo] = {
                ocultar: setTimeout(
                    () => this.ocultar(campo),
                    Estados.config.mensajes.duracionErrorMs,
                ),
            };
        },

        ocultar: function (campo, animar = true) {
            const definicion = Estados.config.campos[campo];
            if (!definicion) return;

            const $input = $(definicion.input);
            const $error = $(definicion.error);

            this.cancelarTemporizadores(campo);
            $input.removeClass('field-invalid').removeAttr('aria-invalid');

            const vaciar = () => $error
                .removeClass('field-error-in field-error-out')
                .text('')
                .removeAttr('title');

            if (!animar || !$error.text()) {
                vaciar();
                return;
            }

            $error
                .removeClass('field-error-in')
                .addClass('field-error-out');

            this.temporizadores[campo] = {
                vaciar: setTimeout(
                    vaciar,
                    Estados.config.mensajes.animacionMs,
                ),
            };
        },

        limpiar: function (animar = false) {
            Object.keys(Estados.config.campos).forEach(
                campo => this.ocultar(campo, animar),
            );
        },

        cancelarTemporizadores: function (campo) {
            const actuales = this.temporizadores[campo];

            if (!actuales) return;

            Object.values(actuales).forEach(clearTimeout);
            delete this.temporizadores[campo];
        },

        mostrarDesdeServidor: function (error) {
            const errores = error?.status === 422
                ? error?.responseJSON?.errors
                : null;

            if (!errores) return false;

            let primerCampo = null;

            Object.entries(errores).forEach(([campo, mensajes]) => {
                if (!Estados.config.campos[campo]) return;

                this.mostrar(
                    campo,
                    Array.isArray(mensajes)
                        ? mensajes[0]
                        : String(mensajes),
                );

                primerCampo ??= campo;
            });

            if (primerCampo) {
                $(Estados.config.campos[primerCampo].input).trigger('focus');
            }

            return primerCampo !== null;
        },
    },

    modal: {
        abrir: function () {
            $(Estados.config.selectores.modal)
                .removeClass('hidden')
                .addClass('flex');
        },

        cerrar: function () {
            if (Estados.estado.guardando) return;

            $(Estados.config.selectores.modal)
                .removeClass('flex')
                .addClass('hidden');

            Estados.formulario.limpiar();
        },

        inicializarEventos: function () {
            const s = Estados.config.selectores;

            $(s.nuevo).on('click', function () {
                Estados.formulario.limpiar();
                $(s.modalTitulo).text(Estados.config.textos.nuevo);
                Estados.modal.abrir();
            });

            $(`${s.cerrarModal}, ${s.cancelarModal}`).on('click', function () {
                Estados.modal.cerrar();
            });

            $(s.modal).on('click', function (evento) {
                if ($(evento.target).is(s.modal)) {
                    Estados.modal.cerrar();
                }
            });
        },
    },

    formulario: {
        limpiar: function () {
            const s = Estados.config.selectores;

            Estados.estado.registroSeleccionado = null;

            $(s.id).val('0');
            $(s.nombre).val('');
            $(s.clave).val('');
            $(s.modalTitulo).text(Estados.config.textos.nuevo);

            Estados.errores.limpiar(false);
        },

        editar: function (idEstado) {
            const registro = Estados.obtenerRegistroPorId(idEstado);

            if (!registro) return;

            const s = Estados.config.selectores;

            Estados.errores.limpiar(false);
            Estados.estado.registroSeleccionado = registro;

            $(s.id).val(registro.id_estado);
            $(s.nombre).val(registro.nombre_estado ?? '');
            $(s.clave).val(registro.clave_estado ?? '');
            $(s.modalTitulo).text(Estados.config.textos.editar);

            Estados.modal.abrir();
        },

        obtenerPayload: function () {
            const s = Estados.config.selectores;

            return {
                nombre_estado: String($(s.nombre).val() ?? '').trim(),
                clave_estado: String($(s.clave).val() ?? '').trim().toUpperCase(),
            };
        },

        guardar: async function () {
            const s = Estados.config.selectores;
            const idEstado = Number($(s.id).val() ?? 0);
            const payload = this.obtenerPayload();
            const $boton = $(s.guardar);

            if (Estados.estado.guardando) return;

            Estados.errores.limpiar(false);
            Estados.estado.guardando = true;
            Estados.cargando.boton(
                $boton,
                true,
                Estados.config.textos.guardando,
            );
            Estados.cargando.modal(true);

            try {
                if (idEstado > 0) {
                    await Estados.peticiones.actualizar(idEstado, payload);
                } else {
                    await Estados.peticiones.crear(payload);
                }

                Estados.estado.guardando = false;
                Estados.modal.cerrar();
                await Estados.cargarDatos(true);
            } catch (error) {
                if (!Estados.errores.mostrarDesdeServidor(error)) {
                    console.error(Estados.obtenerMensajeError(error));
                }
            } finally {
                Estados.estado.guardando = false;
                Estados.cargando.boton($boton, false);
                Estados.cargando.modal(false);
            }
        },

        inicializarEventos: function () {
            Object.entries(Estados.config.campos).forEach(
                ([campo, { input }]) => {
                    $(input).on('input', function () {
                        Estados.errores.ocultar(campo);
                    });
                },
            );

            $(Estados.config.selectores.guardar).on(
                'click',
                function (evento) {
                    evento.preventDefault();
                    Estados.formulario.guardar();
                },
            );
        },
    },

    formatearFecha: function (valor) {
        const fecha = new Date(valor);

        if (Number.isNaN(fecha.getTime())) {
            return '—';
        }

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
        if (!$('#estados-config').length) {
            return;
        }

        this.config = crearEstadosConfig();
        this.tabla.inicializar();
        this.modal.inicializarEventos();
        this.formulario.inicializarEventos();

        await this.cargarDatos(false);
    },
};

$(function () {
    Estados.inicializar();
});

export default Estados;
