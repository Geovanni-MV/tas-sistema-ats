@extends('layouts.app')

@section('title', 'Usuarios | Nexus ATS')
@section('section', 'Seguridad')
@section('page', 'Usuarios')

@section('content')
<main class="p-4 sm:p-6 lg:p-8">
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Seguridad</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">Usuarios</h1>
                <p class="mt-1 text-sm text-slate-500">Administra las cuentas de acceso, su empresa y el rol asignado.</p>
                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                    <span><strong id="kpi-total" class="animate-pulse text-slate-800">—</strong> registros</span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span><strong id="kpi-active" class="animate-pulse text-slate-800">—</strong> activos</span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-slate-300"></span><strong id="kpi-inactive" class="animate-pulse text-slate-800">—</strong> inactivos</span>
                </div>
            </div>
            <button id="new-record" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"><span class="text-base leading-none">+</span>Nuevo usuario</button>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-1 flex-col gap-3 sm:flex-row">
                        <div class="relative flex-1">
                            <svg viewBox="0 0 24 24" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 fill-none stroke-slate-400" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                            <input id="table-search" type="search" placeholder="Buscar por nombre, correo o teléfono..." autocomplete="off" class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="flex items-center gap-2 whitespace-nowrap"><label for="per-page" class="text-sm font-medium text-slate-500">Filas</label><select id="per-page" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select></div>
                        <select id="status-filter" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"><option value="">Todos los estados</option><option value="1">Activos</option><option value="0">Inactivos</option></select>
                        <button id="clear-filters" type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Limpiar</button>
                    </div>
                    <div class="whitespace-nowrap text-sm text-slate-500"><span id="record-count">0</span> registros</div>
                </div>
            </div>

            <div class="relative min-h-[200px]">

                <div class="overflow-x-auto">
                    <table
                        id="tabla-principal"
                        class="w-full min-w-full text-left text-sm"
                        style="width: 100%"
                        aria-busy="true"
                    ></table>
                </div>

                {{-- Estado de carga de la tabla (visible hasta que llegan los datos) --}}
                <div id="table-loading" role="status" aria-live="polite" class="absolute inset-0 z-10 flex items-center justify-center gap-3 bg-white/80 backdrop-blur-[1px]">
                    <svg class="h-6 w-6 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-20"></circle><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path></svg>
                    <span class="text-sm font-medium text-slate-600">Cargando registros...</span>
                </div>

            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <div>Mostrando <span id="visible-count" class="font-medium text-slate-700">0</span> de <span id="filtered-count" class="font-medium text-slate-700">0</span> registros</div>
                <div id="table-pagination" class="flex items-center gap-1"></div>
            </div>
        </section>
    </div>
</main>

<div id="record-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 py-8 backdrop-blur-sm">
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="border-b border-slate-200 px-7 pb-5 pt-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h2 id="record-modal-title" class="text-xl font-bold tracking-tight text-slate-950">Registrar usuario</h2>
                    <p class="mt-1 text-sm text-slate-500">Ingresa los datos para agregar o actualizar un usuario.</p>
                </div>
                <button id="close-modal" type="button" aria-label="Cerrar" class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-40"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"></path></svg></button>
            </div>
        </div>
        <div class="max-h-[65vh] overflow-y-auto px-7 pb-3 pt-6">
            <input type="hidden" id="record-id" value="0">
            <div class="grid grid-cols-1 gap-x-5 sm:grid-cols-2">
                <div>
                    <label for="nombres" class="mb-2 block text-sm font-semibold text-slate-800">Nombres <span class="text-red-500">*</span></label>
                    <input id="nombres" type="text" maxlength="150" autocomplete="off" placeholder="Ej. María Fernanda" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <p id="error-nombres" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>
                <div>
                    <label for="apellidos" class="mb-2 block text-sm font-semibold text-slate-800">Apellidos <span class="text-red-500">*</span></label>
                    <input id="apellidos" type="text" maxlength="150" autocomplete="off" placeholder="Ej. López Pérez" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <p id="error-apellidos" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-x-5 sm:grid-cols-2">
                <div>
                    <label for="telefono" class="mb-2 block text-sm font-semibold text-slate-800">Teléfono <span class="text-red-500">*</span></label>
                    <input id="telefono" type="tel" maxlength="15" autocomplete="off" placeholder="Ej. 9991234567" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <p id="error-telefono" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>
                <div>
                    <label for="correo_electronico" class="mb-2 block text-sm font-semibold text-slate-800">Correo electrónico <span class="text-red-500">*</span></label>
                    <input id="correo_electronico" type="email" maxlength="150" autocomplete="off" placeholder="nombre@empresa.com" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <p id="error-correo_electronico" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-x-5 sm:grid-cols-2">
                <div>
                    <div class="mb-2 flex items-center justify-between gap-4"><label for="id_empresa" class="text-sm font-semibold text-slate-800">Empresa</label><span class="text-xs text-slate-400">Opcional</span></div>
                    <select id="id_empresa" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"><option value="">Sin empresa</option></select>
                    <p id="error-id_empresa" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>
                <div>
                    <div class="mb-2 flex items-center justify-between gap-4"><label for="id_rol" class="text-sm font-semibold text-slate-800">Rol</label><span class="text-xs text-slate-400">Opcional</span></div>
                    <select id="id_rol" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"><option value="">Sin rol</option></select>
                    <p id="error-id_rol" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>
            </div>
            <div id="grupo-clave">
                <div>
                    <label for="clave" class="mb-2 block text-sm font-semibold text-slate-800">Clave <span class="text-red-500">*</span></label>
                    <input id="clave" type="password" maxlength="72" placeholder="Mínimo 8 caracteres" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    <p id="error-clave" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>
                <p class="mb-3 text-xs text-slate-400">Solo se captura al crear. Después se cambia desde el botón «Clave».</p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-7 py-4">
            <button id="cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50">Cancelar</button>
            <button id="save" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Guardar</button>
        </div>
    </div>
</div>

<div id="clave-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 py-8 backdrop-blur-sm">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="border-b border-slate-200 px-7 pb-5 pt-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-xl font-bold tracking-tight text-slate-950">Cambiar clave</h2>
                    <p id="clave-modal-subtitle" class="mt-1 truncate text-sm text-slate-500"></p>
                </div>
                <button id="clave-close" type="button" aria-label="Cerrar" class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-40"><svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"></path></svg></button>
            </div>
        </div>
        <div class="px-7 pb-3 pt-6">
            <input type="hidden" id="clave-id" value="0">
            <div>
                <label for="nueva-clave" class="mb-2 block text-sm font-semibold text-slate-800">Nueva clave <span class="text-red-500">*</span></label>
                <input id="nueva-clave" type="password" maxlength="72" autocomplete="new-password" placeholder="Mínimo 8 caracteres" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                <p id="error-nueva-clave" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-7 py-4">
            <button id="clave-cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50">Cancelar</button>
            <button id="clave-save" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Actualizar clave</button>
        </div>
    </div>
</div>

<div id="usuarios-config" class="hidden"
     data-url-obtener="{{ route('api.v1.seguridad.usuarios.obtener-datos') }}"
     data-url-crear="{{ route('api.v1.seguridad.usuarios.crear') }}"
     data-url-actualizar="{{ route('api.v1.seguridad.usuarios.actualizar', ['id_usuario' => '__ID__']) }}"
     data-url-cambiar-estatus="{{ route('api.v1.seguridad.usuarios.cambiar-estatus', ['id_usuario' => '__ID__']) }}"
     data-url-cambiar-clave="{{ route('api.v1.seguridad.usuarios.cambiar-clave', ['id_usuario' => '__ID__']) }}"
     data-url-roles="{{ route('api.v1.seguridad.roles.obtener-datos') }}"
     data-url-empresas="{{ route('api.v1.reclutamiento.empresas.obtener-datos') }}"></div>
@endsection

@push('scripts')
    @vite('resources/js/modulos/seguridad/usuarios/Usuarios.js')
@endpush
