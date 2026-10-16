@extends('layouts.app')

@section('title', 'Roles y permisos | Nexus ATS')
@section('section', 'Seguridad')
@section('page', 'Roles y permisos')

@section('content')
<main class="p-4 sm:p-6 lg:p-8">
    <div class="space-y-6">
        <section>
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Seguridad</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950">Roles y permisos</h1>
            <p class="mt-1 text-sm text-slate-500">Define qué permisos tiene cada rol sobre las opciones del menú. Marca las casillas y presiona «Guardar cambios» para aplicarlos todos juntos.</p>
            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-blue-500"></span><strong id="kpi-asignados" class="animate-pulse text-slate-800">—</strong> permisos asignados al rol</span>
                <span class="text-slate-300">•</span>
                <span><strong id="kpi-menus" class="animate-pulse text-slate-800">—</strong> opciones de menú</span>
                <span class="text-slate-300">•</span>
                <span><strong id="kpi-permisos" class="animate-pulse text-slate-800">—</strong> permisos disponibles</span>
            </div>
        </section>

        <div id="matrix-aviso" role="status" aria-live="polite" class="hidden rounded-lg border px-4 py-3 text-sm font-medium"></div>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                    <div class="w-full lg:max-w-xs">
                        <label for="rol-select" class="mb-1.5 block text-sm font-semibold text-slate-800">Rol</label>
                        <select id="rol-select" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"><option value="">Selecciona un rol...</option></select>
                    </div>
                    <div class="relative flex-1">
                        <svg viewBox="0 0 24 24" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 fill-none stroke-slate-400" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                        <input id="matrix-search" type="search" placeholder="Buscar opción de menú..." autocomplete="off" class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <label for="solo-asignadas" class="inline-flex cursor-pointer items-center gap-2 whitespace-nowrap py-2.5 text-sm font-medium text-slate-600">
                        <input id="solo-asignadas" type="checkbox" class="h-4 w-4 rounded border-slate-300 accent-blue-600">
                        Solo con permisos
                    </label>
                </div>
            </div>

            <div class="relative min-h-[220px]">
                <div class="overflow-x-auto">
                    <table id="matriz" class="hidden w-full min-w-full text-left text-sm" aria-busy="false"></table>
                </div>

                <div id="matrix-empty" class="flex min-h-[220px] items-center justify-center px-6 py-10 text-center text-sm text-slate-500">Selecciona un rol para configurar sus permisos.</div>

                {{-- Estado de carga (visible mientras llegan los datos del rol) --}}
                <div id="matrix-loading" role="status" aria-live="polite" class="absolute inset-0 z-30 hidden items-center justify-center gap-3 bg-white/80 backdrop-blur-[1px]">
                    <svg class="h-6 w-6 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-20"></circle><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path></svg>
                    <span class="text-sm font-medium text-slate-600">Cargando permisos...</span>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 border-t border-slate-200 px-5 py-4 text-xs text-slate-500 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-5">
                <span class="inline-flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-amber-100 ring-1 ring-amber-300"></span>Cambio sin guardar</span>
                <span>Las casillas de columna y de fila aplican a las opciones y permisos visibles.</span>
                <span>Solo se muestran permisos y opciones de menú activos.</span>
            </div>
        </section>

        {{-- Barra de guardado: queda fija al fondo de la pantalla mientras se desplaza la matriz --}}
        <div id="barra-cambios" class="sticky bottom-4 z-30 hidden items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white/95 px-5 py-3 shadow-lg backdrop-blur [&:not(.hidden)]:flex">
            <span id="contador-cambios" role="status" aria-live="polite" class="text-sm font-semibold text-slate-500">Sin cambios pendientes</span>
            <div class="flex items-center gap-3">
                <button id="descartar-cambios" type="button" disabled class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">Descartar</button>
                <button id="guardar-cambios" type="button" disabled class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-blue-600">Guardar cambios</button>
            </div>
        </div>
    </div>
</main>

<div id="permisos-roles-config" class="hidden"
     data-url-obtener="{{ route('api.v1.seguridad.permisos-roles.obtener-datos') }}"
     data-url-sincronizar="{{ route('api.v1.seguridad.permisos-roles.sincronizar', ['id_rol' => '__ID__']) }}"
     data-url-roles="{{ route('api.v1.seguridad.roles.obtener-datos') }}"
     data-url-permisos="{{ route('api.v1.seguridad.permisos.obtener-datos') }}"
     data-url-menu="{{ route('api.v1.seguridad.menu.obtener-datos') }}"></div>
@endsection

@push('scripts')
    @vite('resources/js/modulos/seguridad/permisos-roles/PermisosRoles.js')
@endpush
