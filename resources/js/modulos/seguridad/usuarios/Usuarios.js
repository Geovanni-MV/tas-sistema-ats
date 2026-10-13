import $ from 'jquery';
import DataTable from 'datatables.net';
import { Api } from '../../../core/Api';
import { crearUsuariosConfig } from './UsuariosConfig';

DataTable.use($);

/**
 * Módulo principal de usuarios.
 *
 * Flujo:
 * API REST -> estado.registros -> DataTable -> interfaz.
 *
 * La colección recibida desde el servidor se conserva localmente para
 * reutilizarla en edición y cambio de estatus sin volver a solicitar el
 * registro individual al backend.
 */
const Usuarios = {
    config: null,

    estado: {
        registros: [],
        registroSeleccionado: null,
        tabla: null,
        guardando: false,
        roles: [],
        empresas: [],
        modalClaveAbierto: false,
    },

    peticiones: {
        obtener: async function () {
            return Api.get(Usuarios.config.urls.obtener);
        },

        crear: async function (payload) {
            return Api.post(Usuarios.config.urls.crear, payload);
        },

        actualizar: async function (idUsuario, payload) {
            const url = Usuarios.config.urls.actualizar.replace('__ID__', idUsuario);
            return Api.put(url, payload);
        },

        // La API alterna el valor actual de "activo", por eso no requiere cuerpo.
        cambiarEstatus: async function (idUsuario) {
            const url = Usuarios.config.urls.cambiarEstatus.replace('__ID__', idUsuario);
            return Api.patch(url, {});
        },

        cambiarClave: async function (idUsuario, payload) {
            const url = Usuarios.config.urls.cambiarClave.replace('__ID__', idUsuario);
            return Api.patch(url, payload);
        },

        obtenerRoles: async function () {
            return Api.get(Usuarios.config.urls.obtenerRoles);
        },

        obtenerEmpresas: async function () {
            return Api.get(Usuarios.config.urls.obtenerEmpresas);
        },
    },

    obtenerRegistroPorId: function (idUsuario) {
        return this.estado.registros.find(
            registro => Number(registro.id_usuario) === Number(idUsuario),
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
            const { cargandoTabla, tabla } = Usuarios.config.selectores;

            $(cargandoTabla).toggleClass('hidden', !activo).toggleClass('flex', activo);
            $(tabla).attr('aria-busy', activo);
        },

        kpis: function (activo) {
            const { kpiTotal, kpiActivos, kpiInactivos } = Usuarios.config.selectores;

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
            const s = Usuarios.config.selectores;

            $(`${s.nombres}, ${s.apellidos}, ${s.telefono}, ${s.correo}, ${s.empresa}, ${s.rol}, ${s.clave}, ${s.cancelarModal}, ${s.cerrarModal}`).prop('disabled', activo);
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
            const selector = Usuarios.config.selectores.tabla;

            if (!$(selector).length) {
                return;
            }

            Usuarios.estado.tabla = new DataTable(selector, {
                data: [],
                pageLength: Usuarios.config.datatable.pageLength,
                responsive: Usuarios.config.datatable.responsive,
                autoWidth: Usuarios.config.datatable.autoWidth,
                processing: Usuarios.config.datatable.processing,
                paging: Usuarios.config.datatable.paging,
                searching: Usuarios.config.datatable.searching,
                ordering: Usuarios.config.datatable.ordering,
                info: false,
                lengthChange: false,
                order: [[0, 'asc']],
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null },
                columns: [
                    { title: 'ID', data: 'id_usuario' },
                    { title: 'Usuario', data: 'nombres' },
                    { title: 'Telefono', data: 'telefono' },
                    { title: 'Empresa', data: 'empresa' },
                    { title: 'Rol', data: 'rol' },
                    { title: 'Activo', data: 'activo' },
                    { title: 'Acciones', data: null },
                ],
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data) {
                            return `<span class="rounded border border-blue-200 bg-blue-50 px-2 py-1 font-mono text-xs font-semibold text-blue-700">#USR-${String(data).padStart(3, '0')}</span>`;
                        },
                    },
                    {
                        targets: 1,
                        render: function (_data, type, row) {
                            if (type === 'sort' || type === 'type') return `${row.nombres} ${row.apellidos}`.toLowerCase();

                            return `<div class="font-semibold text-slate-900">${Usuarios.escaparHtml(row.nombres)} ${Usuarios.escaparHtml(row.apellidos)}</div>`
                                + `<div class="text-xs text-slate-500">${Usuarios.escaparHtml(row.correo_electronico)}</div>`;
                        },
                    },
                    {
                        targets: 2,
                        render: function (data) {
                            return `<span class="text-slate-600">${Usuarios.escaparHtml(data ?? '—')}</span>`;
                        },
                    },
                    {
                        targets: 3,
                        render: function (data) {
                            return data?.nombre_empresa
                                ? `<span class="text-slate-600">${Usuarios.escaparHtml(data.nombre_empresa)}</span>`
                                : '<span class="text-slate-400">—</span>';
                        },
                    },
                    {
                        targets: 4,
                        render: function (data) {
                            return data?.nombre_rol
                                ? `<span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">${Usuarios.escaparHtml(data.nombre_rol)}</span>`
                                : '<span class="text-slate-400">Sin rol</span>';
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
                            return `<div class="flex justify-center gap-2"><button type="button" class="btn-editar rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700" data-id-usuario="${row.id_usuario}">Editar</button><button type="button" class="btn-clave rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700" data-id-usuario="${row.id_usuario}">Clave</button><button type="button" class="btn-cambiar-estatus rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700" data-id-usuario="${row.id_usuario}">${row.activo ? 'Desactivar' : 'Activar'}</button></div>`;
                        },
                    },
                ],
                language: {
                    zeroRecords: 'No hay registros que coincidan con los filtros.',
                    emptyTable: 'No hay registros disponibles.',
                },
            });

            Usuarios.estado.tabla.on('draw', () => this.actualizarMeta());
            this.inicializarEventos();
            this.actualizarMeta();
        },

        actualizar: function (registros, conservarPagina = true) {
            if (!Usuarios.estado.tabla) return;

            const paginaActual = Usuarios.estado.tabla.page();
            Usuarios.estado.tabla.clear();
            Usuarios.estado.tabla.rows.add(registros);
            Usuarios.estado.tabla.draw(false);

            if (conservarPagina) {
                const paginas = Usuarios.estado.tabla.page.info().pages;
                Usuarios.estado.tabla.page(Math.min(paginaActual, Math.max(paginas - 1, 0))).draw('page');
            }
        },

        inicializarEventos: function () {
            const selectores = Usuarios.config.selectores;
            const columnaActivo = Usuarios.config.datatable.columnaActivo;

            $(selectores.buscar).on('input', function () {
                Usuarios.estado.tabla.search($(this).val()).draw();
            });

            $(selectores.filas).on('change', function () {
                Usuarios.estado.tabla.page.len(Number($(this).val())).draw();
            });

            $(selectores.estado).on('change', function () {
                const valor = $(this).val();
                const patron = valor === '' ? '' : (valor === '1' ? '^Activo$' : '^Inactivo$');
                Usuarios.estado.tabla.column(columnaActivo).search(patron, true, false).draw();
            });

            $(selectores.limpiarFiltros).on('click', function () {
                $(selectores.buscar).val('');
                $(selectores.estado).val('');
                $(selectores.filas).val('10');
                Usuarios.estado.tabla.search('').column(columnaActivo).search('').page.len(10).draw();
            });

            $(document).on('click', `${selectores.tabla} .btn-editar`, function () {
                const idUsuario = $(this).data('id-usuario');
                Usuarios.formulario.editar(idUsuario);
            });

            $(document).on('click', `${selectores.tabla} .btn-cambiar-estatus`, async function () {
                const $boton = $(this);
                const idUsuario = $boton.data('id-usuario');
                const registro = Usuarios.obtenerRegistroPorId(idUsuario);

                if (!registro || $boton.prop('disabled')) return;

                Usuarios.cargando.boton($boton, true, Usuarios.config.textos.procesando);

                try {
                    await Usuarios.peticiones.cambiarEstatus(idUsuario);
                    await Usuarios.cargarDatos(true);
                } catch (error) {
                    console.error(Usuarios.obtenerMensajeError(error));
                } finally {
                    Usuarios.cargando.boton($boton, false);
                }
            });

            $(document).on('click', `${selectores.tabla} .btn-clave`, function () {
                Usuarios.claveModal.abrir($(this).data('id-usuario'));
            });
        },

        actualizarMeta: function () {
            if (!Usuarios.estado.tabla) return;

            const info = Usuarios.estado.tabla.page.info();
            $(Usuarios.config.selectores.visibles).text(Math.max(info.end - info.start, 0));
            $(Usuarios.config.selectores.filtrados).text(info.recordsDisplay);
            $(Usuarios.config.selectores.totalRegistros).text(info.recordsDisplay);
            this.renderizarPaginacion(info);
        },

        renderizarPaginacion: function (info) {
            const $contenedor = $(Usuarios.config.selectores.paginacion);
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
                    Usuarios.estado.tabla.page(pagina).draw('page');
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
            return Usuarios.estado.modalClaveAbierto ? Usuarios.config.camposClave : Usuarios.config.campos;
        },

        mostrar: function (campo, mensaje) {
            const definicion = this.definiciones()[campo];
            if (!definicion) return;

            const { duracionErrorMs } = Usuarios.config.mensajes;
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
                vaciar: setTimeout(vaciar, Usuarios.config.mensajes.animacionMs),
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
            $(Usuarios.config.selectores.modal).removeClass('hidden').addClass('flex');
        },

        cerrar: function () {
            if (Usuarios.estado.guardando) return;

            $(Usuarios.config.selectores.modal).removeClass('flex').addClass('hidden');
            Usuarios.formulario.limpiar();
        },

        inicializarEventos: function () {
            const selectores = Usuarios.config.selectores;

            $(selectores.nuevo).on('click', function () {
                Usuarios.formulario.limpiar();
                $(selectores.modalTitulo).text(Usuarios.config.textos.nuevo);
                Usuarios.modal.abrir();
            });

            $(selectores.cerrarModal + ', ' + selectores.cancelarModal).on('click', function () {
                Usuarios.modal.cerrar();
            });

            $(selectores.modal).on('click', function (evento) {
                if ($(evento.target).is(selectores.modal)) {
                    Usuarios.modal.cerrar();
                }
            });
        },
    },

    formulario: {
        limpiar: function () {
            const selectores = Usuarios.config.selectores;
            Usuarios.estado.registroSeleccionado = null;
            $(selectores.id).val('0');
            $(selectores.nombres).val('');
            $(selectores.apellidos).val('');
            $(selectores.telefono).val('');
            $(selectores.correo).val('');
            $(selectores.clave).val('');
            $(selectores.grupoClave).removeClass('hidden');
            Usuarios.poblarSelects();
            $(selectores.modalTitulo).text(Usuarios.config.textos.nuevo);
            Usuarios.errores.limpiar(false);
        },

        editar: function (idUsuario) {
            const registro = Usuarios.obtenerRegistroPorId(idUsuario);
            if (!registro) return;

            const selectores = Usuarios.config.selectores;
            Usuarios.errores.limpiar(false);
            Usuarios.estado.registroSeleccionado = registro;
            $(selectores.id).val(registro.id_usuario);
            $(selectores.nombres).val(registro.nombres ?? '');
            $(selectores.apellidos).val(registro.apellidos ?? '');
            $(selectores.telefono).val(registro.telefono ?? '');
            $(selectores.correo).val(registro.correo_electronico ?? '');
            $(selectores.clave).val('');
            $(selectores.grupoClave).addClass('hidden');
            Usuarios.poblarSelects(registro.id_empresa, registro.id_rol);
            $(selectores.modalTitulo).text(Usuarios.config.textos.editar);
            Usuarios.modal.abrir();
        },

        obtenerPayload: function (esEdicion = false) {
            const selectores = Usuarios.config.selectores;
            const texto = selector => String($(selector).val() ?? '').trim();

            const payload = {
                nombres: texto(selectores.nombres),
                apellidos: texto(selectores.apellidos),
                telefono: texto(selectores.telefono),
                correo_electronico: texto(selectores.correo),
                id_empresa: Number($(selectores.empresa).val()) || null,
                id_rol: Number($(selectores.rol).val()) || null,
            };

            // La clave solo se captura al crear; después se cambia con su propio endpoint.
            if (!esEdicion) {
                payload.clave = String($(selectores.clave).val() ?? '');
            }

            return payload;
        },

        guardar: async function () {
            const selectores = Usuarios.config.selectores;
            const idUsuario = Number($(selectores.id).val() ?? 0);
            const payload = this.obtenerPayload(idUsuario > 0);
            const $boton = $(selectores.guardar);

            if (Usuarios.estado.guardando) return;

            Usuarios.errores.limpiar(false);
            Usuarios.estado.guardando = true;
            Usuarios.cargando.boton($boton, true, Usuarios.config.textos.guardando);
            Usuarios.cargando.modal(true);

            try {
                if (idUsuario > 0) {
                    await Usuarios.peticiones.actualizar(idUsuario, payload);
                } else {
                    await Usuarios.peticiones.crear(payload);
                }

                Usuarios.estado.guardando = false;
                Usuarios.modal.cerrar();
                Usuarios.cargarDatos(true);
            } catch (error) {
                if (!Usuarios.errores.mostrarDesdeServidor(error)) {
                    console.error(Usuarios.obtenerMensajeError(error));
                }
            } finally {
                Usuarios.estado.guardando = false;
                Usuarios.cargando.boton($boton, false);
                Usuarios.cargando.modal(false);
            }
        },

        inicializarEventos: function () {
            Object.entries(Usuarios.config.campos).forEach(([campo, { input }]) => {
                $(input).on('input change', function () {
                    Usuarios.errores.ocultar(campo);
                });
            });

            $(Usuarios.config.selectores.guardar).on('click', function (evento) {
                evento.preventDefault();
                Usuarios.formulario.guardar();
            });
        },
    },

    /**
     * Catálogos para los selects del formulario (roles y empresas).
     * Si alguno falla, el formulario sigue funcionando con la opción vacía.
     */
    cargarCatalogos: async function () {
        try {
            const [roles, empresas] = await Promise.all([
                this.peticiones.obtenerRoles(),
                this.peticiones.obtenerEmpresas(),
            ]);

            this.estado.roles = roles.data ?? [];
            this.estado.empresas = empresas.data ?? [];
        } catch (error) {
            console.error(this.obtenerMensajeError(error));
        }

        this.poblarSelects();
    },

    /**
     * Muestra solo roles/empresas activos, más el que el usuario ya tenga asignado
     * (aunque hoy esté inactivo) para no perderlo al editar.
     */
    poblarSelects: function (idEmpresa = null, idRol = null) {
        const s = Usuarios.config.selectores;

        const construir = (registros, campoId, campoNombre, seleccionado, textoVacio) => {
            const opciones = registros
                .filter(registro => Boolean(registro.activo) || Number(registro[campoId]) === Number(seleccionado))
                .map(registro => `<option value="${registro[campoId]}">${Usuarios.escaparHtml(registro[campoNombre])}</option>`);

            return `<option value="">${textoVacio}</option>${opciones.join('')}`;
        };

        $(s.empresa).html(construir(Usuarios.estado.empresas, 'id_empresa', 'nombre_empresa', idEmpresa, 'Sin empresa'));
        $(s.rol).html(construir(Usuarios.estado.roles, 'id_rol', 'nombre_rol', idRol, 'Sin rol'));
        $(s.empresa).val(idEmpresa ?? '');
        $(s.rol).val(idRol ?? '');
    },

    /**
     * Modal independiente para cambiar la clave (PATCH /usuarios/{id}/clave).
     */
    claveModal: {
        abrir: function (idUsuario) {
            const registro = Usuarios.obtenerRegistroPorId(idUsuario);
            if (!registro) return;

            const s = Usuarios.config.selectores;
            Usuarios.estado.modalClaveAbierto = true;
            Usuarios.errores.limpiar(false);
            Usuarios.estado.registroSeleccionado = registro;
            $(s.claveId).val(registro.id_usuario);
            $(s.claveInput).val('');
            $(s.claveSubtitulo).text(`${registro.nombres} ${registro.apellidos} · ${registro.correo_electronico}`);
            $(s.modalClave).removeClass('hidden').addClass('flex');
            $(s.claveInput).trigger('focus');
        },

        cerrar: function () {
            if (Usuarios.estado.guardando) return;

            const s = Usuarios.config.selectores;
            Usuarios.errores.limpiar(false);
            $(s.modalClave).removeClass('flex').addClass('hidden');
            $(s.claveInput).val('');
            Usuarios.estado.modalClaveAbierto = false;
            Usuarios.estado.registroSeleccionado = null;
        },

        guardar: async function () {
            const s = Usuarios.config.selectores;
            const idUsuario = Number($(s.claveId).val() ?? 0);
            const $boton = $(s.claveGuardar);
            const controles = `${s.claveInput}, ${s.claveCancelar}, ${s.claveCerrar}`;

            if (Usuarios.estado.guardando || idUsuario <= 0) return;

            Usuarios.errores.limpiar(false);
            Usuarios.estado.guardando = true;
            Usuarios.cargando.boton($boton, true, Usuarios.config.textos.guardando);
            $(controles).prop('disabled', true);

            try {
                await Usuarios.peticiones.cambiarClave(idUsuario, { clave: String($(s.claveInput).val() ?? '') });

                Usuarios.estado.guardando = false;
                Usuarios.claveModal.cerrar();
                Usuarios.cargarDatos(true);
            } catch (error) {
                if (!Usuarios.errores.mostrarDesdeServidor(error)) {
                    console.error(Usuarios.obtenerMensajeError(error));
                }
            } finally {
                Usuarios.estado.guardando = false;
                Usuarios.cargando.boton($boton, false);
                $(controles).prop('disabled', false);
            }
        },

        inicializarEventos: function () {
            const s = Usuarios.config.selectores;

            $(`${s.claveCancelar}, ${s.claveCerrar}`).on('click', () => Usuarios.claveModal.cerrar());

            $(s.modalClave).on('click', function (evento) {
                if ($(evento.target).is(s.modalClave)) {
                    Usuarios.claveModal.cerrar();
                }
            });

            Object.entries(Usuarios.config.camposClave).forEach(([campo, { input }]) => {
                $(input).on('input', () => Usuarios.errores.ocultar(campo));
            });

            $(s.claveGuardar).on('click', function (evento) {
                evento.preventDefault();
                Usuarios.claveModal.guardar();
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
        if (!$('#usuarios-config').length) return;

        this.config = crearUsuariosConfig();
        this.tabla.inicializar();
        this.modal.inicializarEventos();
        this.formulario.inicializarEventos();
        this.claveModal.inicializarEventos();
        this.cargarCatalogos();
        await this.cargarDatos(false);
    },
};

$(function () {
    Usuarios.inicializar();
});

export default Usuarios;
