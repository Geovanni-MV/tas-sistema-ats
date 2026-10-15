@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-slate-800">
                Dashboard
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Resumen general de reclutamiento
            </p>
        </div>

        <div class="text-sm text-slate-500">
            {{ now()->format('d/m/Y') }}
        </div>

    </div>


    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Vacantes activas --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Vacantes activas
                    </p>

                    <p class="mt-2 text-3xl font-semibold text-slate-800">
                        {{ $data['resumen']['vacantes_activas'] }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <i class="fa-solid fa-briefcase"></i>
                </div>

            </div>

        </div>


        {{-- Candidatos --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Candidatos registrados
                    </p>

                    <p class="mt-2 text-3xl font-semibold text-slate-800">
                        {{ $data['resumen']['candidatos'] }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-users"></i>
                </div>

            </div>

        </div>


        {{-- En proceso --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        En proceso
                    </p>

                    <p class="mt-2 text-3xl font-semibold text-slate-800">
                        {{ $data['resumen']['en_proceso'] }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-user-clock"></i>
                </div>

            </div>

        </div>


        {{-- Contrataciones --}}
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Contrataciones
                    </p>

                    <p class="mt-2 text-3xl font-semibold text-slate-800">
                        {{ $data['resumen']['contrataciones'] }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-user-check"></i>
                </div>

            </div>

        </div>

    </div>


    {{-- Segunda sección --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Vacantes recientes --}}
        <div class="xl:col-span-2">

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">

                    <div>
                        <h2 class="font-semibold text-slate-800">
                            Vacantes recientes
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Últimas vacantes registradas
                        </p>
                    </div>

                    <a href="#"
                       class="text-sm font-medium text-blue-600 hover:text-blue-700">
                        Ver todas
                    </a>

                </div>

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-slate-50">

                            <tr>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Vacante
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Empresa
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Candidatos
                                </th>

                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Estatus
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            <tr>

                                <td class="px-5 py-4 text-sm font-medium text-slate-700">
                                    Ayudante General
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-500">
                                    Empresa Demo
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-500">
                                    24
                                </td>

                                <td class="px-5 py-4">

                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Activa
                                    </span>

                                </td>

                            </tr>


                            <tr>

                                <td class="px-5 py-4 text-sm font-medium text-slate-700">
                                    Montacarguista
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-500">
                                    Empresa Demo
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-500">
                                    17
                                </td>

                                <td class="px-5 py-4">

                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        Activa
                                    </span>

                                </td>

                            </tr>


                            <tr>

                                <td class="px-5 py-4 text-sm font-medium text-slate-700">
                                    Supervisor de Operaciones
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-500">
                                    Empresa Demo
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-500">
                                    9
                                </td>

                                <td class="px-5 py-4">

                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                        En proceso
                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- Actividad --}}
        <div>

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-200 px-5 py-4">

                    <h2 class="font-semibold text-slate-800">
                        Actividad reciente
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Últimos movimientos
                    </p>

                </div>

                <div class="divide-y divide-slate-100">

                    <div class="px-5 py-4">

                        <div class="flex gap-3">

                            <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs text-blue-600">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>

                            <div>

                                <p class="text-sm text-slate-700">
                                    Se registró un nuevo candidato.
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Hace 10 minutos
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="px-5 py-4">

                        <div class="flex gap-3">

                            <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xs text-emerald-600">
                                <i class="fa-solid fa-check"></i>
                            </div>

                            <div>

                                <p class="text-sm text-slate-700">
                                    Un candidato cambió a contratado.
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Hace 35 minutos
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="px-5 py-4">

                        <div class="flex gap-3">

                            <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-xs text-amber-600">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>

                            <div>

                                <p class="text-sm text-slate-700">
                                    Se registró una nueva vacante.
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Hace 1 hora
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection