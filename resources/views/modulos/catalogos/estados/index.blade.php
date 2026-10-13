@extends('layouts.app')

@section('title', 'Estados | Nexus ATS')
@section('section', 'Catálogos')
@section('page', 'Estados')

@section('content')
<main class="p-4 sm:p-6 lg:p-8">
    <div class="space-y-6">

        <section class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Catálogos</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950">Estados</h1>
                <p class="mt-1 text-sm text-slate-500">Administra los estados disponibles dentro del sistema.</p>

                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
                    <span><strong id="kpi-total" class="animate-pulse text-slate-800">—</strong> registros</span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span><strong id="kpi-active" class="animate-pulse text-slate-800">—</strong> activos</span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-slate-300"></span><strong id="kpi-inactive" class="animate-pulse text-slate-800">—</strong> inactivos</span>
                </div>
            </div>

            <button id="new-record" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <span class="text-base leading-none">+</span>
                Nuevo estado
            </button>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex flex-1 flex-col gap-3 sm:flex-row">

                        <div class="relative flex-1">
                            <svg viewBox="0 0 24 24" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 fill-none stroke-slate-400" stroke-width="2">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>

                            <input id="table-search" type="search" placeholder="Buscar por estado o clave..." autocomplete="off" class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        </div>

                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <label for="per-page" class="text-sm font-medium text-slate-500">Filas</label>

                            <select id="per-page" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <select id="status-filter" class="rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>

                        <button id="clear-filters" type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                            Limpiar
                        </button>

                    </div>

                    <div class="whitespace-nowrap text-sm text-slate-500">
                        <span id="record-count">0</span> registros
                    </div>

                </div>
            </div>

            <div class="relative min-h-[200px]">

                <div class="overflow-x-auto">
                    <table id="tabla-estados" class="w-full min-w-full text-left text-sm" style="width: 100%" aria-busy="true"></table>
                </div>

                <div id="table-loading" role="status" aria-live="polite" class="absolute inset-0 z-10 flex items-center justify-center gap-3 bg-white/80 backdrop-blur-[1px]">
                    <svg class="h-6 w-6 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-20"></circle>
                        <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                    </svg>

                    <span class="text-sm font-medium text-slate-600">Cargando registros...</span>
                </div>

            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    Mostrando
                    <span id="visible-count" class="font-medium text-slate-700">0</span>
                    de
                    <span id="filtered-count" class="font-medium text-slate-700">0</span>
                    registros
                </div>

                <div id="table-pagination" class="flex items-center gap-1"></div>
            </div>

        </section>

    </div>
</main>

<div id="record-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 py-8 backdrop-blur-sm">

    <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl">

        <div class="border-b border-slate-200 px-7 pb-5 pt-6">
            <div class="flex items-start justify-between gap-4">

                <div class="min-w-0">
                    <h2 id="record-modal-title" class="text-xl font-bold tracking-tight text-slate-950">Registrar estado</h2>
                    <p class="mt-1 text-sm text-slate-500">Ingresa los datos para agregar o actualizar un estado.</p>
                </div>

                <button id="close-modal" type="button" aria-label="Cerrar" class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-40">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="2">
                        <path d="M6 6l12 12M18 6L6 18"></path>
                    </svg>
                </button>

            </div>
        </div>

        <div class="px-7 pb-3 pt-6">

            <input type="hidden" id="record-id" value="0">

            <div class="space-y-1">

                <div>
                    <label for="nombre_estado" class="mb-2 block text-sm font-semibold text-slate-800">
                        Nombre del estado
                        <span class="text-red-500">*</span>
                    </label>

                    <input id="nombre_estado" type="text" maxlength="150" autocomplete="off" placeholder="Ej. Yucatán" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                    <p id="error-nombre_estado" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>

                <div>
                    <label for="clave_estado" class="mb-2 block text-sm font-semibold text-slate-800">
                        Clave
                        <span class="text-red-500">*</span>
                    </label>

                    <input id="clave_estado" type="text" maxlength="10" autocomplete="off" placeholder="Ej. YUC" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3.5 py-3 uppercase text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">

                    <p id="error-clave_estado" role="alert" aria-live="polite" class="mt-1 h-5 overflow-hidden truncate text-xs font-medium leading-5 text-red-600"></p>
                </div>

            </div>

        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-7 py-4">

            <button id="cancel" type="button" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50">
                Cancelar
            </button>

            <button id="save" type="button" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                Guardar
            </button>

        </div>

    </div>

</div>

<div id="estados-config" class="hidden"
    data-url-obtener="{{ route('api.v1.catalogos.estados.obtener-datos') }}"
    data-url-crear="{{ route('api.v1.catalogos.estados.crear') }}"
    data-url-actualizar="{{ route('api.v1.catalogos.estados.actualizar', ['idEstado' => '__ID__']) }}"
    data-url-cambiar-estatus="{{ route('api.v1.catalogos.estados.cambiar-estatus', ['idEstado' => '__ID__']) }}">
</div>
@endsection

@push('scripts')
    @vite('resources/js/modulos/catalogos/estados/Estados.js')
@endpush
