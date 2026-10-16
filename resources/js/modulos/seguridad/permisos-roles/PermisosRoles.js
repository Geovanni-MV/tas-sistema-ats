import $ from 'jquery';
import { Api } from '../../../core/Api';
import { crearPermisosRolesConfig } from './PermisosRolesConfig';

/**
 * Módulo Roles y permisos (matriz de acceso).
 *
 * Filas: opciones del menú (respetando su jerarquía).
 * Columnas: permisos activos.
 * Cada casilla representa una asignación permiso + menú para el rol elegido.
 *
 * Flujo:
 *   1. Al elegir un rol se carga su estado guardado (estado.asignaciones).
 *   2. Marcar o desmarcar casillas (una a una, por fila o por columna) NO llama a la API:
 *      solo registra la diferencia respecto al estado guardado en estado.cambios.
 *   3. "Guardar cambios" envía todas las diferencias en una única petición
 *      (PUT permisos-roles/sincronizar/{id_rol}) y reemplaza el estado guardado
 *      con la respuesta del servidor.
 */
const PermisosRoles = {
    config: null,

    estado: {
        roles: [],
        permisos: [],
        menus: [],
        asignaciones: [],
        idRol: null,
        // Diferencias pendientes respecto a lo guardado: clave -> { idMenu, idPermiso, activar }
        cambios: new Map(),
        guardando: false,
        solicitud: 0,
        temporizadorAviso: null,
    },

    peticiones: {
        roles: async function () {
            return Api.get(PermisosRoles.config.urls.roles);
        },

        permisos: async function () {
            return Api.get(PermisosRoles.config.urls.permisos);
        },

        menus: async function () {
            return Api.get(PermisosRoles.config.urls.menu);
        },

        asignaciones: async function (idRol) {
            return Api.get(`${PermisosRoles.config.urls.obtener}?id_rol=${encodeURIComponent(idRol)}`);
        },

        sincronizar: async function (idRol, payload) {
            const url = PermisosRoles.config.urls.sincronizar.replace('__ID__', idRol);
            return Api.put(url, payload);
        },
    },

    /* ------------------------------------------------------------------
     * Datos derivados
     * ------------------------------------------------------------------ */

    clave: function (idMenu, idPermiso) {
        return `${Number(idMenu)}-${Number(idPermiso)}`;
    },

    permisosActivos: function () {
        return this.estado.permisos.filter(permiso => Boolean(permiso.activo));
    },

    /**
     * Menús activos en orden jerárquico: cada padre seguido de sus hijos.
     * Un menú cuyo padre está inactivo o no existe se trata como raíz,
     * para que nunca quede oculto por una rama rota.
     */
    menusOrdenados: function () {
        const activos = this.estado.menus.filter(menu => Boolean(menu.activo));
        const ids = new Set(activos.map(menu => Number(menu.id_menu)));
        const comparar = (a, b) => (a.orden ?? 9999) - (b.orden ?? 9999) || Number(a.id_menu) - Number(b.id_menu);

        const hijosDe = new Map();
        const raices = [];

        activos.forEach(menu => {
            const idPadre = menu.id_menu_padre === null ? null : Number(menu.id_menu_padre);

            if (idPadre === null || !ids.has(idPadre) || idPadre === Number(menu.id_menu)) {
                raices.push(menu);
                return;
            }

            if (!hijosDe.has(idPadre)) hijosDe.set(idPadre, []);
            hijosDe.get(idPadre).push(menu);
        });

        const resultado = [];
        const visitados = new Set();

        const recorrer = (menu, profundidad) => {
            if (visitados.has(Number(menu.id_menu))) return;

            visitados.add(Number(menu.id_menu));
            resultado.push({ menu, profundidad });
            (hijosDe.get(Number(menu.id_menu)) ?? []).sort(comparar).forEach(hijo => recorrer(hijo, profundidad + 1));
        };

        raices.sort(comparar).forEach(menu => recorrer(menu, 0));

        return resultado;
    },

    /** Estado guardado en el servidor, indexado por menú + permiso. */
    indice: function () {
        const indice = new Map();

        this.estado.asignaciones.forEach(asignacion => {
            indice.set(this.clave(asignacion.id_menu, asignacion.id_permiso), asignacion);
        });

        return indice;
    },

    estaGuardado: function (idMenu, idPermiso, indice = this.indice()) {
        return Boolean(indice.get(this.clave(idMenu, idPermiso))?.activo);
    },

    /** Valor que se muestra: el cambio pendiente si existe; si no, lo guardado. */
    estaMarcado: function (idMenu, idPermiso, indice = this.indice()) {
        const cambio = this.estado.cambios.get(this.clave(idMenu, idPermiso));

        return cambio ? cambio.activar : this.estaGuardado(idMenu, idPermiso, indice);
    },

    /**
     * Filas que se muestran según el buscador y el filtro "Solo con permisos".
     */
    filasVisibles: function () {
        const s = this.config.selectores;
        const texto = String($(s.buscar).val() ?? '').trim().toLowerCase();
        const soloAsignadas = $(s.soloAsignadas).is(':checked');
        const permisos = this.permisosActivos();
        const indice = this.indice();

        return this.menusOrdenados().filter(({ menu }) => {
            if (texto) {
                const contenido = `${menu.nombre_menu ?? ''} ${menu.ruta ?? ''}`.toLowerCase();
                if (!contenido.includes(texto)) return false;
            }

            if (soloAsignadas) {
                return permisos.some(permiso => this.estaMarcado(menu.id_menu, permiso.id_permiso, indice));
            }

            return true;
        });
    },

    /* ------------------------------------------------------------------
     * Carga de datos
     * ------------------------------------------------------------------ */

    cargarCatalogos: async function () {
        this.cargando.matriz(true);
        this.cargando.kpis(true);

        try {
            const [roles, permisos, menus] = await Promise.all([
                this.peticiones.roles(),
                this.peticiones.permisos(),
                this.peticiones.menus(),
            ]);

            this.estado.roles = roles.data ?? [];
            this.estado.permisos = permisos.data ?? [];
            this.estado.menus = menus.data ?? [];
            this.poblarRoles();
        } catch (error) {
            this.mostrarAviso(this.obtenerMensajeError(error), 'error');
        } finally {
            this.cargando.matriz(false);
            this.cargando.kpis(false);
        }
    },

    cargarRol: async function (idRol) {
        // Cada carga recibe un número; si el usuario cambia de rol antes de
        // que responda la anterior, la respuesta vieja se descarta.
        const solicitud = ++this.estado.solicitud;

        this.estado.idRol = idRol ? Number(idRol) : null;
        this.estado.asignaciones = [];
        this.estado.cambios.clear();

        if (!this.estado.idRol) {
            this.renderizar();
            return;
        }

        this.cargando.matriz(true);

        try {
            const respuesta = await this.peticiones.asignaciones(this.estado.idRol);

            if (solicitud !== this.estado.solicitud) return;

            this.estado.asignaciones = (respuesta.data ?? []).filter(
                asignacion => Number(asignacion.id_rol) === this.estado.idRol,
            );
        } catch (error) {
            if (solicitud === this.estado.solicitud) {
                this.mostrarAviso(this.obtenerMensajeError(error), 'error');
            }
        } finally {
            if (solicitud === this.estado.solicitud) {
                this.cargando.matriz(false);
                this.renderizar();
            }
        }
    },

    poblarRoles: function () {
        const opciones = this.estado.roles
            .filter(rol => Boolean(rol.activo))
            .map(rol => `<option value="${rol.id_rol}">${this.escaparHtml(rol.nombre_rol)}</option>`);

        $(this.config.selectores.rol).html(`<option value="">Selecciona un rol...</option>${opciones.join('')}`);
    },

    /* ------------------------------------------------------------------
     * Cambios pendientes y guardado
     * ------------------------------------------------------------------ */

    /**
     * Registra el estado deseado de una lista de celdas { idMenu, idPermiso, activar }.
     * Si el valor coincide con lo guardado, el cambio pendiente se elimina.
     * No hace ninguna petición.
     */
    marcar: function (celdas) {
        if (this.estado.guardando || !this.estado.idRol) return;

        const indice = this.indice();

        celdas.forEach(({ idMenu, idPermiso, activar }) => {
            const clave = this.clave(idMenu, idPermiso);

            if (activar === this.estaGuardado(idMenu, idPermiso, indice)) {
                this.estado.cambios.delete(clave);
            } else {
                this.estado.cambios.set(clave, { idMenu: Number(idMenu), idPermiso: Number(idPermiso), activar });
            }
        });

        this.renderizar();
    },

    descartar: function () {
        if (this.estado.guardando) return;

        this.estado.cambios.clear();
        this.renderizar();
    },

    /**
     * Envía todas las diferencias en una sola petición. Si falla, los cambios
     * pendientes se conservan para poder reintentar.
     */
    guardar: async function () {
        const idRol = this.estado.idRol;

        if (!idRol || this.estado.guardando || this.estado.cambios.size === 0) return;

        const payload = {
            asignaciones: [...this.estado.cambios.values()].map(cambio => ({
                id_permiso: cambio.idPermiso,
                id_menu: cambio.idMenu,
                activo: cambio.activar,
            })),
        };

        this.estado.guardando = true;
        this.renderizar();

        try {
            const respuesta = await this.peticiones.sincronizar(idRol, payload);

            this.estado.asignaciones = respuesta.data ?? [];
            this.estado.cambios.clear();
            this.mostrarAviso(this.textoResumen(respuesta.resumen), 'exito');
        } catch (error) {
            this.mostrarAviso(this.obtenerMensajeError(error), 'error');
        } finally {
            this.estado.guardando = false;
            this.renderizar();
        }
    },

    textoResumen: function (resumen = {}) {
        const asignados = Number(resumen.creadas ?? 0) + Number(resumen.activadas ?? 0);
        const quitados = Number(resumen.desactivadas ?? 0);
        const partes = [];

        if (asignados > 0) partes.push(`${asignados} permiso${asignados === 1 ? '' : 's'} asignado${asignados === 1 ? '' : 's'}`);
        if (quitados > 0) partes.push(`${quitados} quitado${quitados === 1 ? '' : 's'}`);

        return partes.length > 0
            ? `Cambios guardados: ${partes.join(' y ')}.`
            : 'No había cambios que guardar.';
    },

    /* ------------------------------------------------------------------
     * Interfaz
     * ------------------------------------------------------------------ */

    cargando: {
        matriz: function (activo) {
            const { cargando, matriz } = PermisosRoles.config.selectores;

            $(cargando).toggleClass('hidden', !activo).toggleClass('flex', activo);
            $(matriz).attr('aria-busy', activo);
        },

        kpis: function (activo) {
            const { kpiAsignados, kpiMenus, kpiPermisos } = PermisosRoles.config.selectores;

            $(`${kpiAsignados}, ${kpiMenus}, ${kpiPermisos}`).toggleClass('animate-pulse', activo);
        },
    },

    spinner: function (clase = 'h-4 w-4') {
        return `<svg class="${clase} animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path></svg>`;
    },

    mostrarVacio: function (texto) {
        const { matriz, vacio } = this.config.selectores;

        $(matriz).addClass('hidden').empty();
        $(vacio).text(texto).removeClass('hidden').addClass('flex');
    },

    renderizar: function () {
        const { matriz, vacio } = this.config.selectores;
        const textos = this.config.textos;

        this.actualizarKpis();
        this.actualizarBarra();

        if (!this.estado.idRol) {
            this.mostrarVacio(textos.sinRol);
            return;
        }

        const permisos = this.permisosActivos();

        if (permisos.length === 0 || this.menusOrdenados().length === 0) {
            this.mostrarVacio(textos.sinCatalogos);
            return;
        }

        const filas = this.filasVisibles();

        if (filas.length === 0) {
            this.mostrarVacio(textos.sinCoincidencias);
            return;
        }

        const indice = this.indice();
        const bloqueado = this.estado.guardando ? 'disabled' : '';

        const encabezados = permisos.map(permiso => `
            <th scope="col" class="px-3 py-3 text-center align-bottom">
                <div class="whitespace-nowrap text-xs font-semibold uppercase text-slate-500" title="${this.escaparHtml(permiso.descripcion ?? '')}">${this.escaparHtml(permiso.nombre_permiso)}</div>
                <input type="checkbox" class="chk-columna mt-2 h-4 w-4 cursor-pointer accent-blue-600 disabled:cursor-not-allowed" ${bloqueado} data-permiso="${permiso.id_permiso}" aria-label="Aplicar ${this.escaparHtml(permiso.nombre_permiso)} a todas las opciones visibles" title="Aplica a las opciones visibles">
            </th>`).join('');

        const cuerpo = filas.map(({ menu, profundidad }) => {
            const celdas = permisos.map(permiso => {
                const clave = this.clave(menu.id_menu, permiso.id_permiso);
                const etiqueta = `${this.escaparHtml(permiso.nombre_permiso)} en ${this.escaparHtml(menu.nombre_menu)}`;
                const marcada = this.estaMarcado(menu.id_menu, permiso.id_permiso, indice) ? 'checked' : '';
                const pendiente = this.estado.cambios.has(clave) ? 'bg-amber-50 ring-1 ring-inset ring-amber-200' : '';

                return `<td class="px-3 py-3 text-center ${pendiente}"><input type="checkbox" class="chk-celda h-4 w-4 cursor-pointer accent-blue-600 disabled:cursor-not-allowed" ${bloqueado} data-menu="${menu.id_menu}" data-permiso="${permiso.id_permiso}" ${marcada} aria-label="${etiqueta}"></td>`;
            }).join('');

            const ruta = menu.ruta
                ? `<div class="font-mono text-[11px] text-slate-400">${this.escaparHtml(menu.ruta)}</div>`
                : '';

            return `
            <tr class="group border-t border-slate-100 hover:bg-slate-50">
                <th scope="row" class="sticky left-0 z-10 bg-white py-3 pr-4 text-left font-normal group-hover:bg-slate-50" style="padding-left: ${1.25 + profundidad * 1.5}rem">
                    <div class="${menu.es_padre ? 'font-semibold text-slate-900' : 'text-slate-700'}">${this.escaparHtml(menu.nombre_menu)}</div>
                    ${ruta}
                </th>
                ${celdas}
                <td class="px-3 py-3 text-center"><input type="checkbox" class="chk-fila h-4 w-4 cursor-pointer accent-blue-600 disabled:cursor-not-allowed" ${bloqueado} data-menu="${menu.id_menu}" aria-label="Todos los permisos para ${this.escaparHtml(menu.nombre_menu)}"></td>
            </tr>`;
        }).join('');

        $(matriz).html(`
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="sticky left-0 z-20 bg-slate-50 px-5 py-3 text-left align-bottom text-xs font-semibold uppercase text-slate-500">Opción de menú</th>
                    ${encabezados}
                    <th scope="col" class="px-3 py-3 text-center align-bottom text-xs font-semibold uppercase text-slate-500">Todos</th>
                </tr>
            </thead>
            <tbody>${cuerpo}</tbody>`).removeClass('hidden');

        $(vacio).addClass('hidden').removeClass('flex');
        this.actualizarCasillasMaestras(filas, permisos, indice);
    },

    /**
     * Casillas de fila y de columna: marcadas si todo está marcado,
     * indeterminadas si solo una parte lo está.
     */
    actualizarCasillasMaestras: function (filas, permisos, indice) {
        const { matriz, casillaFila, casillaColumna } = this.config.selectores;

        const estadoDe = marcadas => (marcadas.length === 0 ? [false, false] : marcadas.every(Boolean) ? [true, false] : [false, marcadas.some(Boolean)]);

        $(matriz).find(casillaColumna).each((_, elemento) => {
            const idPermiso = Number($(elemento).data('permiso'));
            const [marcada, parcial] = estadoDe(filas.map(({ menu }) => this.estaMarcado(menu.id_menu, idPermiso, indice)));

            elemento.checked = marcada;
            elemento.indeterminate = parcial;
        });

        $(matriz).find(casillaFila).each((_, elemento) => {
            const idMenu = Number($(elemento).data('menu'));
            const [marcada, parcial] = estadoDe(permisos.map(permiso => this.estaMarcado(idMenu, permiso.id_permiso, indice)));

            elemento.checked = marcada;
            elemento.indeterminate = parcial;
        });
    },

    /** Barra inferior: contador de cambios y botones Guardar / Descartar. */
    actualizarBarra: function () {
        const s = this.config.selectores;
        const textos = this.config.textos;
        const total = this.estado.cambios.size;
        const guardando = this.estado.guardando;

        $(s.barra).toggleClass('hidden', !this.estado.idRol);
        $(s.rol).prop('disabled', guardando);

        $(s.contador)
            .text(guardando ? textos.guardando : (total === 0 ? textos.sinCambios : `${total} cambio${total === 1 ? '' : 's'} sin guardar`))
            .toggleClass('text-amber-700', total > 0 && !guardando)
            .toggleClass('text-slate-500', total === 0 || guardando);

        $(s.descartar).prop('disabled', total === 0 || guardando);

        const $guardar = $(s.guardar);

        if (guardando) {
            if ($guardar.data('html-original') === undefined) {
                $guardar.data('html-original', $guardar.html());
            }

            $guardar.html(`<span class="inline-flex items-center gap-2">${this.spinner()}${textos.guardando}</span>`);
        } else if ($guardar.data('html-original') !== undefined) {
            $guardar.html($guardar.data('html-original')).removeData('html-original');
        }

        $guardar.prop('disabled', total === 0 || guardando);
    },

    actualizarKpis: function () {
        const s = this.config.selectores;

        // Asignaciones activas guardadas, ajustadas por los cambios pendientes.
        const efectivos = this.estado.asignaciones.filter(asignacion => Boolean(asignacion.activo)).length
            + [...this.estado.cambios.values()].reduce((suma, cambio) => suma + (cambio.activar ? 1 : -1), 0);

        $(s.kpiAsignados).text(this.estado.idRol ? efectivos : '—');
        $(s.kpiMenus).text(this.estado.menus.filter(menu => Boolean(menu.activo)).length);
        $(s.kpiPermisos).text(this.permisosActivos().length);
    },

    /* ------------------------------------------------------------------
     * Avisos y errores
     * ------------------------------------------------------------------ */

    obtenerMensajeError: function (error) {
        const errores = error?.responseJSON?.errors ?? error?.responseJSON?.errores;
        const primerError = errores ? Object.values(errores).flat()[0] : null;

        return primerError
            ?? error?.responseJSON?.mensaje
            ?? error?.responseJSON?.message
            ?? error?.statusText
            ?? this.config.textos.errorGenerico;
    },

    mostrarAviso: function (mensaje, tipo = 'error') {
        const $aviso = $(this.config.selectores.aviso);
        const clases = {
            error: 'border-red-200 bg-red-50 text-red-700',
            exito: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        };

        clearTimeout(this.estado.temporizadorAviso);

        $aviso
            .removeClass(`hidden ${clases.error} ${clases.exito}`)
            .addClass(clases[tipo] ?? clases.error)
            .attr('data-tipo', tipo)
            .text(mensaje);

        this.estado.temporizadorAviso = setTimeout(
            () => $aviso.addClass('hidden').removeAttr('data-tipo').text(''),
            this.config.mensajes.duracionAvisoMs,
        );
    },

    /* ------------------------------------------------------------------
     * Eventos
     * ------------------------------------------------------------------ */

    inicializarEventos: function () {
        const s = this.config.selectores;

        $(s.rol).on('change', function () {
            const nuevo = $(this).val();

            // No se pierde trabajo sin avisar: si hay cambios pendientes se pide confirmación.
            if (PermisosRoles.estado.cambios.size > 0 && !window.confirm(PermisosRoles.config.textos.confirmarCambioRol)) {
                $(this).val(PermisosRoles.estado.idRol ?? '');
                return;
            }

            PermisosRoles.cargarRol(nuevo);
        });

        $(s.buscar).on('input', () => PermisosRoles.renderizar());
        $(s.soloAsignadas).on('change', () => PermisosRoles.renderizar());
        $(s.guardar).on('click', () => PermisosRoles.guardar());
        $(s.descartar).on('click', () => PermisosRoles.descartar());

        $(document).on('change', `${s.matriz} ${s.casilla}`, function () {
            PermisosRoles.marcar([{
                idMenu: Number($(this).data('menu')),
                idPermiso: Number($(this).data('permiso')),
                activar: this.checked,
            }]);
        });

        $(document).on('change', `${s.matriz} ${s.casillaFila}`, function () {
            const idMenu = Number($(this).data('menu'));
            const activar = this.checked;

            PermisosRoles.marcar(
                PermisosRoles.permisosActivos().map(permiso => ({ idMenu, idPermiso: Number(permiso.id_permiso), activar })),
            );
        });

        $(document).on('change', `${s.matriz} ${s.casillaColumna}`, function () {
            const idPermiso = Number($(this).data('permiso'));
            const activar = this.checked;

            PermisosRoles.marcar(
                PermisosRoles.filasVisibles().map(({ menu }) => ({ idMenu: Number(menu.id_menu), idPermiso, activar })),
            );
        });

        // Avisa al cerrar o recargar la página con cambios sin guardar.
        window.addEventListener('beforeunload', evento => {
            if (PermisosRoles.estado.cambios.size > 0) {
                evento.preventDefault();
                evento.returnValue = '';
            }
        });
    },

    escaparHtml: function (valor) {
        return $('<div>').text(String(valor ?? '')).html();
    },

    inicializar: async function () {
        if (!$('#permisos-roles-config').length) return;

        this.config = crearPermisosRolesConfig();
        this.inicializarEventos();
        await this.cargarCatalogos();

        // Permite llegar desde la lista de roles: /permisos-roles?id_rol=3
        const idRolUrl = new URLSearchParams(window.location.search).get('id_rol');
        const $select = $(this.config.selectores.rol);

        if (idRolUrl && $select.find('option').filter((_, opcion) => opcion.value === idRolUrl).length) {
            $select.val(idRolUrl);
            await this.cargarRol(idRolUrl);
            return;
        }

        this.renderizar();
    },
};

$(function () {
    PermisosRoles.inicializar();
});

export default PermisosRoles;
