import $ from 'jquery';
import { Api } from '../../../core/Api';
import { crearPermisosRolesConfig } from './PermisosRolesConfig';

/**
 * Módulo Roles y permisos (matriz de acceso).
 *
 * Filas: opciones del menú (respetando su jerarquía).
 * Columnas: permisos activos.
 * Cada casilla representa un registro de seguridad.permisos_roles para el rol elegido.
 *
 * Flujo de una casilla:
 *   - marcar sin registro previo   -> POST   crear
 *   - marcar con registro inactivo -> PATCH  estatus (activa)
 *   - desmarcar con registro activo -> PATCH estatus (desactiva)
 *
 * Todo cambio (casilla, fila o columna) pasa por aplicar(), que solo envía a la
 * API lo que realmente difiere del estado actual y refleja la respuesta del servidor.
 */
const PermisosRoles = {
    config: null,

    estado: {
        roles: [],
        permisos: [],
        menus: [],
        asignaciones: [],
        idRol: null,
        pendientes: new Set(),
        solicitud: 0,
        temporizadorError: null,
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

        crear: async function (payload) {
            return Api.post(PermisosRoles.config.urls.crear, payload);
        },

        // La API alterna el valor actual de "activo", por eso no requiere cuerpo.
        cambiarEstatus: async function (idPermisoRol) {
            const url = PermisosRoles.config.urls.cambiarEstatus.replace('__ID__', idPermisoRol);
            return Api.patch(url, {});
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

    indice: function () {
        const indice = new Map();

        this.estado.asignaciones.forEach(asignacion => {
            indice.set(this.clave(asignacion.id_menu, asignacion.id_permiso), asignacion);
        });

        return indice;
    },

    estaAsignado: function (idMenu, idPermiso, indice = this.indice()) {
        return Boolean(indice.get(this.clave(idMenu, idPermiso))?.activo);
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
                return permisos.some(permiso => this.estaAsignado(menu.id_menu, permiso.id_permiso, indice));
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
            this.mostrarError(this.obtenerMensajeError(error));
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
        this.estado.pendientes.clear();

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
                this.mostrarError(this.obtenerMensajeError(error));
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
     * Cambios de permisos
     * ------------------------------------------------------------------ */

    /**
     * Aplica una lista de cambios { idMenu, idPermiso, activar }.
     * Descarta los que ya coinciden con el estado actual y los que están en curso.
     */
    aplicar: async function (cambios) {
        const idRol = this.estado.idRol;
        const indice = this.indice();

        const lista = cambios.filter(cambio => {
            const clave = this.clave(cambio.idMenu, cambio.idPermiso);

            return !this.estado.pendientes.has(clave)
                && this.estaAsignado(cambio.idMenu, cambio.idPermiso, indice) !== cambio.activar;
        });

        if (!idRol || lista.length === 0) {
            // Restaura el estado visual de las casillas que el navegador ya alternó.
            this.renderizar();
            return;
        }

        lista.forEach(cambio => this.estado.pendientes.add(this.clave(cambio.idMenu, cambio.idPermiso)));
        this.renderizar();

        const { loteMaximo } = this.config.mensajes;
        const fallos = [];

        for (let inicio = 0; inicio < lista.length; inicio += loteMaximo) {
            const lote = lista.slice(inicio, inicio + loteMaximo);
            const resultados = await Promise.allSettled(lote.map(cambio => this.ejecutarCambio(idRol, cambio)));

            resultados.forEach((resultado, posicion) => {
                if (resultado.status === 'rejected') {
                    fallos.push({ cambio: lote[posicion], error: resultado.reason });
                }
            });
        }

        // El rol pudo cambiar mientras se guardaba; en ese caso no se toca la vista nueva.
        if (this.estado.idRol === idRol) {
            lista.forEach(cambio => this.estado.pendientes.delete(this.clave(cambio.idMenu, cambio.idPermiso)));
            this.renderizar();
        }

        if (fallos.length > 0) {
            const detalle = this.obtenerMensajeError(fallos[0].error);
            const total = fallos.length;

            this.mostrarError(`No se pudo guardar ${total} cambio${total === 1 ? '' : 's'}. ${detalle}`);
        }
    },

    ejecutarCambio: async function (idRol, { idMenu, idPermiso, activar }) {
        const existente = this.indice().get(this.clave(idMenu, idPermiso));
        let respuesta;

        if (!existente) {
            if (!activar) return;

            respuesta = await this.peticiones.crear({ id_permiso: idPermiso, id_rol: idRol, id_menu: idMenu });
        } else {
            if (Boolean(existente.activo) === activar) return;

            respuesta = await this.peticiones.cambiarEstatus(existente.id_permiso_rol);
        }

        this.registrarAsignacion(idRol, respuesta.data);
    },

    registrarAsignacion: function (idRol, registro) {
        if (!registro || this.estado.idRol !== idRol) return;

        const posicion = this.estado.asignaciones.findIndex(
            asignacion => Number(asignacion.id_permiso_rol) === Number(registro.id_permiso_rol),
        );

        if (posicion >= 0) {
            this.estado.asignaciones[posicion] = registro;
        } else {
            this.estado.asignaciones.push(registro);
        }
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

    spinner: function () {
        return '<svg class="mx-auto h-4 w-4 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" role="img" aria-label="Guardando"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path></svg>';
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
        const encabezados = permisos.map(permiso => `
            <th scope="col" class="px-3 py-3 text-center align-bottom">
                <div class="whitespace-nowrap text-xs font-semibold uppercase text-slate-500" title="${this.escaparHtml(permiso.descripcion ?? '')}">${this.escaparHtml(permiso.nombre_permiso)}</div>
                <input type="checkbox" class="chk-columna mt-2 h-4 w-4 cursor-pointer accent-blue-600" data-permiso="${permiso.id_permiso}" aria-label="Aplicar ${this.escaparHtml(permiso.nombre_permiso)} a todas las opciones visibles" title="Aplica a las opciones visibles">
            </th>`).join('');

        const cuerpo = filas.map(({ menu, profundidad }) => {
            const celdas = permisos.map(permiso => {
                const clave = this.clave(menu.id_menu, permiso.id_permiso);
                const etiqueta = `${this.escaparHtml(permiso.nombre_permiso)} en ${this.escaparHtml(menu.nombre_menu)}`;

                if (this.estado.pendientes.has(clave)) {
                    return `<td class="px-3 py-3 text-center">${this.spinner()}</td>`;
                }

                const marcada = this.estaAsignado(menu.id_menu, permiso.id_permiso, indice) ? 'checked' : '';

                return `<td class="px-3 py-3 text-center"><input type="checkbox" class="chk-celda h-4 w-4 cursor-pointer accent-blue-600" data-menu="${menu.id_menu}" data-permiso="${permiso.id_permiso}" ${marcada} aria-label="${etiqueta}"></td>`;
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
                <td class="px-3 py-3 text-center"><input type="checkbox" class="chk-fila h-4 w-4 cursor-pointer accent-blue-600" data-menu="${menu.id_menu}" aria-label="Todos los permisos para ${this.escaparHtml(menu.nombre_menu)}"></td>
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
     * Casillas de fila y de columna: marcadas si todo está asignado,
     * indeterminadas si solo una parte lo está.
     */
    actualizarCasillasMaestras: function (filas, permisos, indice) {
        const { matriz, casillaFila, casillaColumna } = this.config.selectores;

        const estadoDe = marcadas => (marcadas.length === 0 ? [false, false] : marcadas.every(Boolean) ? [true, false] : [false, marcadas.some(Boolean)]);

        $(matriz).find(casillaColumna).each((_, elemento) => {
            const idPermiso = Number($(elemento).data('permiso'));
            const [marcada, parcial] = estadoDe(filas.map(({ menu }) => this.estaAsignado(menu.id_menu, idPermiso, indice)));

            elemento.checked = marcada;
            elemento.indeterminate = parcial;
        });

        $(matriz).find(casillaFila).each((_, elemento) => {
            const idMenu = Number($(elemento).data('menu'));
            const [marcada, parcial] = estadoDe(permisos.map(permiso => this.estaAsignado(idMenu, permiso.id_permiso, indice)));

            elemento.checked = marcada;
            elemento.indeterminate = parcial;
        });
    },

    actualizarKpis: function () {
        const s = this.config.selectores;
        const asignados = this.estado.asignaciones.filter(asignacion => Boolean(asignacion.activo)).length;

        $(s.kpiAsignados).text(this.estado.idRol ? asignados : '—');
        $(s.kpiMenus).text(this.estado.menus.filter(menu => Boolean(menu.activo)).length);
        $(s.kpiPermisos).text(this.permisosActivos().length);
    },

    /* ------------------------------------------------------------------
     * Errores
     * ------------------------------------------------------------------ */

    obtenerMensajeError: function (error) {
        const errores = error?.responseJSON?.errors;
        const primerError = errores ? Object.values(errores).flat()[0] : null;

        return primerError
            ?? error?.responseJSON?.mensaje
            ?? error?.responseJSON?.message
            ?? error?.statusText
            ?? this.config.textos.errorGenerico;
    },

    mostrarError: function (mensaje) {
        const $error = $(this.config.selectores.error);

        clearTimeout(this.estado.temporizadorError);
        $error.text(mensaje).removeClass('hidden');

        this.estado.temporizadorError = setTimeout(
            () => $error.addClass('hidden').text(''),
            this.config.mensajes.duracionErrorMs,
        );
    },

    /* ------------------------------------------------------------------
     * Eventos
     * ------------------------------------------------------------------ */

    inicializarEventos: function () {
        const s = this.config.selectores;

        $(s.rol).on('change', function () {
            PermisosRoles.cargarRol($(this).val());
        });

        $(s.buscar).on('input', () => PermisosRoles.renderizar());
        $(s.soloAsignadas).on('change', () => PermisosRoles.renderizar());

        $(document).on('change', `${s.matriz} ${s.casilla}`, function () {
            PermisosRoles.aplicar([{
                idMenu: Number($(this).data('menu')),
                idPermiso: Number($(this).data('permiso')),
                activar: this.checked,
            }]);
        });

        $(document).on('change', `${s.matriz} ${s.casillaFila}`, function () {
            const idMenu = Number($(this).data('menu'));
            const activar = this.checked;

            PermisosRoles.aplicar(
                PermisosRoles.permisosActivos().map(permiso => ({ idMenu, idPermiso: Number(permiso.id_permiso), activar })),
            );
        });

        $(document).on('change', `${s.matriz} ${s.casillaColumna}`, function () {
            const idPermiso = Number($(this).data('permiso'));
            const activar = this.checked;

            PermisosRoles.aplicar(
                PermisosRoles.filasVisibles().map(({ menu }) => ({ idMenu: Number(menu.id_menu), idPermiso, activar })),
            );
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
