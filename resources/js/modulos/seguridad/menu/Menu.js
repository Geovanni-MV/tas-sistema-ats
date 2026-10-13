import $ from 'jquery';
import DataTable from 'datatables.net';
import { Api } from '../../../core/Api';
import { crearMenuConfig } from './MenuConfig';

DataTable.use($);

/**
 * Módulo principal de opciones de menú.
 *
 * Flujo:
 * API REST -> estado.registros -> DataTable -> interfaz.
 *
 * La colección recibida desde el servidor se conserva localmente para
 * reutilizarla en edición y cambio de estatus sin volver a solicitar el
 * registro individual al backend.
 */
const Menu = {
    config: null,

    estado: {
        registros: [],
        registroSeleccionado: null,
        tabla: null,
        guardando: false,

    },

    peticiones: {
        obtener: async function () {
            return Api.get(Menu.config.urls.obtener);
        },

        crear: async function (payload) {
            return Api.post(Menu.config.urls.crear, payload);
        },

        actualizar: async function (idMenu, payload) {
            const url = Menu.config.urls.actualizar.replace('__ID__', idMenu);
            return Api.put(url, payload);
        },

        // La API alterna el valor actual de "activo", por eso no requiere cuerpo.
        cambiarEstatus: async function (idMenu) {
            const url = Menu.config.urls.cambiarEstatus.replace('__ID__', idMenu);
            return Api.patch(url, {});
        },

    },

    obtenerRegistroPorId: function (idMenu) {
        return this.estado.registros.find(
            registro => Number(registro.id_menu) === Number(idMenu),
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
            const { cargandoTabla, tabla } = Menu.config.selectores;

            $(cargandoTabla).toggleClass('hidden', !activo).toggleClass('flex', activo);
            $(tabla).attr('aria-busy', activo);
        },

        kpis: function (activo) {
            const { kpiTotal, kpiActivos, kpiInactivos } = Menu.config.selectores;

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
            const s = Menu.config.selectores;

            $(`${s.padre}, ${s.nombre}, ${s.ruta}, ${s.icono}, ${s.orden}, ${s.esPadre}, ${s.esColapsable}, ${s.descripcion}, ${s.cancelarModal}, ${s.cerrarModal}`).prop('disabled', activo);
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
            const selector = Menu.config.selectores.tabla;

            if (!$(selector).length) {
                return;
            }

            Menu.estado.tabla = new DataTable(selector, {
                data: [],
                pageLength: Menu.config.datatable.pageLength,
                responsive: Menu.config.datatable.responsive,
                autoWidth: Menu.config.datatable.autoWidth,
                processing: Menu.config.datatable.processing,
                paging: Menu.config.datatable.paging,
                searching: Menu.config.datatable.searching,
                ordering: Menu.config.datatable.ordering,
                info: false,
                lengthChange: false,
                order: [[0, 'asc']],
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
                columns: [
                    { title: 'ID', data: 'id_menu' },
                    { title: 'Nombre', data: 'nombre_menu' },
                    { title: 'Padre', data: 'padre' },
                    { title: 'Ruta', data: 'ruta' },
                    { title: 'Orden', data: 'orden' },
                    { title: 'Tipo', data: 'es_padre' },
                    { title: 'Activo', data: 'activo'},
                    { title: 'Acciones', data: null, class:'text-center' },
                ],
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data) {
                            return `<span class="rounded border border-blue-200 bg-blue-50 px-2 py-1 font-mono text-xs font-semibold text-blue-700">#MNU-${String(data).padStart(3, '0')}</span>`;
                        },
                    },
                    {
                        targets: 1,
                        render: function (data) {
                            return `<div class="font-semibold text-slate-900">${Menu.escaparHtml(data ?? '')}</div>`;
                        },
                    },
                    {
                        targets: 2,
                        render: function (data) {
                            return data?.nombre_menu
                                ? `<span class="text-slate-600">${Menu.escaparHtml(data.nombre_menu)}</span>`
                                : '<span class="text-slate-400">—</span>';
                        },
                    },
                    {
                        targets: 3,
                        render: function (data) {
                            return data
                                ? `<code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-600">${Menu.escaparHtml(data)}</code>`
                                : '<span class="text-slate-400">—</span>';
                        },
                    },
                    {
                        targets: 4,
                        render: function (data, type) {
                            if (type === 'sort' || type === 'type') return data ?? 9999;
                            return data ?? '—';
                        },
                    },
                    {
                        targets: 5,
                        render: function (data, _type, row) {
                            const [texto, clase] = Boolean(data)
                                ? ['Padre', 'bg-violet-50 text-violet-700']
                                : (row.id_menu_padre ? ['Submenú', 'bg-sky-50 text-sky-700'] : ['Opción', 'bg-slate-100 text-slate-600']);

                            return `<span class="inline-flex rounded-full ${clase} px-2.5 py-1 text-xs font-semibold">${texto}</span>`;
                        },
                    },
                    {
                        targets: 6,
                        render: function (data) {
                            const activo = Boolean(data);
                            return `<span class="inline-flex items-center gap-1.5 rounded-full ${activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'} px-2.5 py-1 text-xs font-semibold"><span class="h-1.5 w-1.5 rounded-full ${activo ? 'bg-emerald-500' : 'bg-slate-400'}"></span>${activo ? 'Activo' : 'Inactivo'}</span>`;
                        },
                    },
                    {
                        targets: 7,
                        orderable: false,
                        searchable: false,
                        render: function (_data, _type, row) {
                            return `<div class="flex justify-center gap-2"><button type="button" class="btn-editar rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700" data-id-menu="${row.id_menu}">Editar</button><button type="button" class="btn-cambiar-estatus rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700" data-id-menu="${row.id_menu}">${row.activo ? 'Desactivar' : 'Activar'}</button></div>`;
                        },
                    },
                ],
                language: {
                    zeroRecords: 'No hay registros que coincidan con los filtros.',
                    emptyTable: 'No hay registros disponibles.',
                },
            });

            Menu.estado.tabla.on('draw', () => this.actualizarMeta());
            this.inicializarEventos();
            this.actualizarMeta();
        },

        actualizar: function (registros, conservarPagina = true) {
            if (!Menu.estado.tabla) return;

            const paginaActual = Menu.estado.tabla.page();
            Menu.estado.tabla.clear();
            Menu.estado.tabla.rows.add(registros);
            Menu.estado.tabla.draw(false);

            if (conservarPagina) {
                const paginas = Menu.estado.tabla.page.info().pages;
                Menu.estado.tabla.page(Math.min(paginaActual, Math.max(paginas - 1, 0))).draw('page');
            }
        },

        inicializarEventos: function () {
            const selectores = Menu.config.selectores;
            const columnaActivo = Menu.config.datatable.columnaActivo;

            $(selectores.buscar).on('input', function () {
                Menu.estado.tabla.search($(this).val()).draw();
            });

            $(selectores.filas).on('change', function () {
                Menu.estado.tabla.page.len(Number($(this).val())).draw();
            });

            $(selectores.estado).on('change', function () {
                const valor = $(this).val();
                const patron = valor === '' ? '' : (valor === '1' ? '^Activo$' : '^Inactivo$');
                Menu.estado.tabla.column(columnaActivo).search(patron, true, false).draw();
            });

            $(selectores.limpiarFiltros).on('click', function () {
                $(selectores.buscar).val('');
                $(selectores.estado).val('');
                $(selectores.filas).val('10');
                Menu.estado.tabla.search('').column(columnaActivo).search('').page.len(10).draw();
            });

            $(document).on('click', `${selectores.tabla} .btn-editar`, function () {
                const idMenu = $(this).data('id-menu');
                Menu.formulario.editar(idMenu);
            });

            $(document).on('click', `${selectores.tabla} .btn-cambiar-estatus`, async function () {
                const $boton = $(this);
                const idMenu = $boton.data('id-menu');
                const registro = Menu.obtenerRegistroPorId(idMenu);

                if (!registro || $boton.prop('disabled')) return;

                Menu.cargando.boton($boton, true, Menu.config.textos.procesando);

                try {
                    await Menu.peticiones.cambiarEstatus(idMenu);
                    await Menu.cargarDatos(true);
                } catch (error) {
                    console.error(Menu.obtenerMensajeError(error));
                } finally {
                    Menu.cargando.boton($boton, false);
                }
            });

        },

        actualizarMeta: function () {
            if (!Menu.estado.tabla) return;

            const info = Menu.estado.tabla.page.info();
            $(Menu.config.selectores.visibles).text(Math.max(info.end - info.start, 0));
            $(Menu.config.selectores.filtrados).text(info.recordsDisplay);
            $(Menu.config.selectores.totalRegistros).text(info.recordsDisplay);
            this.renderizarPaginacion(info);
        },

        renderizarPaginacion: function (info) {
            const $contenedor = $(Menu.config.selectores.paginacion);
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
                    Menu.estado.tabla.page(pagina).draw('page');
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
            return Menu.config.campos;
        },

        mostrar: function (campo, mensaje) {
            const definicion = this.definiciones()[campo];
            if (!definicion) return;

            const { duracionErrorMs } = Menu.config.mensajes;
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
                vaciar: setTimeout(vaciar, Menu.config.mensajes.animacionMs),
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
            $(Menu.config.selectores.modal).removeClass('hidden').addClass('flex');
        },

        cerrar: function () {
            if (Menu.estado.guardando) return;

            $(Menu.config.selectores.modal).removeClass('flex').addClass('hidden');
            Menu.formulario.limpiar();
        },

        inicializarEventos: function () {
            const selectores = Menu.config.selectores;

            $(selectores.nuevo).on('click', function () {
                Menu.formulario.limpiar();
                $(selectores.modalTitulo).text(Menu.config.textos.nuevo);
                Menu.modal.abrir();
            });

            $(selectores.cerrarModal + ', ' + selectores.cancelarModal).on('click', function () {
                Menu.modal.cerrar();
            });

            $(selectores.modal).on('click', function (evento) {
                if ($(evento.target).is(selectores.modal)) {
                    Menu.modal.cerrar();
                }
            });
        },
    },

    formulario: {
        limpiar: function () {
            const selectores = Menu.config.selectores;
            Menu.estado.registroSeleccionado = null;
            $(selectores.id).val('0');
            $(selectores.nombre).val('');
            $(selectores.ruta).val('');
            $(selectores.icono).val('');
            $(selectores.orden).val('');
            $(selectores.descripcion).val('');
            $(selectores.esPadre).prop('checked', false);
            $(selectores.esColapsable).prop('checked', false);
            Menu.poblarPadres();
            $(selectores.padre).val('');
            $(selectores.modalTitulo).text(Menu.config.textos.nuevo);
            Menu.errores.limpiar(false);
        },

        editar: function (idMenu) {
            const registro = Menu.obtenerRegistroPorId(idMenu);
            if (!registro) return;

            const selectores = Menu.config.selectores;
            Menu.errores.limpiar(false);
            Menu.estado.registroSeleccionado = registro;
            $(selectores.id).val(registro.id_menu);
            Menu.poblarPadres(registro.id_menu, registro.id_menu_padre);
            $(selectores.padre).val(registro.id_menu_padre ?? '');
            $(selectores.nombre).val(registro.nombre_menu ?? '');
            $(selectores.ruta).val(registro.ruta ?? '');
            $(selectores.icono).val(registro.icono_clase ?? '');
            $(selectores.orden).val(registro.orden ?? '');
            $(selectores.descripcion).val(registro.descripcion ?? '');
            $(selectores.esPadre).prop('checked', Boolean(registro.es_padre));
            $(selectores.esColapsable).prop('checked', Boolean(registro.es_colapsable));
            $(selectores.modalTitulo).text(Menu.config.textos.editar);
            Menu.modal.abrir();
        },

        obtenerPayload: function (esEdicion = false) {
            const selectores = Menu.config.selectores;
            const texto = selector => String($(selector).val() ?? '').trim();
            const orden = texto(selectores.orden);

            return {
                id_menu_padre: Number($(selectores.padre).val()) || null,
                es_padre: $(selectores.esPadre).is(':checked'),
                es_colapsable: $(selectores.esColapsable).is(':checked'),
                nombre_menu: texto(selectores.nombre),
                icono_clase: texto(selectores.icono) || null,
                descripcion: texto(selectores.descripcion) || null,
                orden: orden === '' ? null : Number(orden),
                ruta: texto(selectores.ruta) || null,
            };
        },

        guardar: async function () {
            const selectores = Menu.config.selectores;
            const idMenu = Number($(selectores.id).val() ?? 0);
            const payload = this.obtenerPayload(idMenu > 0);
            const $boton = $(selectores.guardar);

            if (Menu.estado.guardando) return;

            Menu.errores.limpiar(false);
            Menu.estado.guardando = true;
            Menu.cargando.boton($boton, true, Menu.config.textos.guardando);
            Menu.cargando.modal(true);

            try {
                if (idMenu > 0) {
                    await Menu.peticiones.actualizar(idMenu, payload);
                } else {
                    await Menu.peticiones.crear(payload);
                }

                Menu.estado.guardando = false;
                Menu.modal.cerrar();
                Menu.cargarDatos(true);
            } catch (error) {
                if (!Menu.errores.mostrarDesdeServidor(error)) {
                    console.error(Menu.obtenerMensajeError(error));
                }
            } finally {
                Menu.estado.guardando = false;
                Menu.cargando.boton($boton, false);
                Menu.cargando.modal(false);
            }
        },

        inicializarEventos: function () {
            Object.entries(Menu.config.campos).forEach(([campo, { input }]) => {
                $(input).on('input change', function () {
                    Menu.errores.ocultar(campo);
                });
            });

            $(Menu.config.selectores.guardar).on('click', function (evento) {
                evento.preventDefault();
                Menu.formulario.guardar();
            });
        },
    },

    /**
     * Llena el select de menú padre con las opciones que pueden serlo
     * (activas y marcadas como "padre"). Excluye al propio registro en edición.
     * La API valida además que no se genere un ciclo en la jerarquía.
     */
    poblarPadres: function (excluirId = null, seleccionado = null) {
        const s = Menu.config.selectores;
        const opciones = Menu.estado.registros
            .filter(registro => Number(registro.id_menu) !== Number(excluirId)
                && ((Boolean(registro.es_padre) && Boolean(registro.activo))
                    || Number(registro.id_menu) === Number(seleccionado)))
            .sort((a, b) => String(a.nombre_menu).localeCompare(String(b.nombre_menu), 'es'))
            .map(registro => `<option value="${registro.id_menu}">${Menu.escaparHtml(registro.nombre_menu)}</option>`);

        $(s.padre).html(`<option value="">Sin padre (nivel principal)</option>${opciones.join('')}`);
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
        if (!$('#menu-config').length) return;

        this.config = crearMenuConfig();
        this.tabla.inicializar();
        this.modal.inicializarEventos();
        this.formulario.inicializarEventos();

        await this.cargarDatos(false);
    },
};

$(function () {
    Menu.inicializar();
});

export default Menu;
