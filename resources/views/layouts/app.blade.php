<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nexus ATS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6f8fc] text-slate-900">
<div class="min-h-screen lg:flex">

    <aside class="hidden w-[250px] flex-shrink-0 border-r border-slate-200 bg-[#edf3ff] lg:flex lg:min-h-screen lg:flex-col">

        <div class="flex h-[62px] items-center gap-3 border-b border-slate-200 px-5">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white">ATS</div>

            <div class="min-w-0">
                <div class="truncate text-lg font-bold leading-tight text-slate-950">Nexus ATS</div>
                <div class="mt-0.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">Enterprise HR</div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">

            <a href="#" class="mb-2 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                <span>Dashboard</span>
            </a>

            @php
                $groups = [
                    'reclutamiento' => [
                        'label' => 'Reclutamiento',
                        'items' => [
                            ['label' => 'Vacantes', 'href' => '#'],
                            ['label' => 'Candidatos', 'href' => '#'],
                            ['label' => 'Entrevistas', 'href' => '#'],
                        ],
                    ],

                    'reportes' => [
                        'label' => 'Reportes',
                        'items' => [
                            ['label' => 'Indicadores', 'href' => '#'],
                            ['label' => 'Contrataciones', 'href' => '#'],
                        ],
                    ],

                    'catalogos' => [
                        'label' => 'Catálogos',
                        'items' => [
                            ['label' => 'Sexos', 'href' => route('sistema-ats-tas.catalogos.sexos.index')],
                            ['label' => 'Estados', 'href' => route('sistema-ats-tas.catalogos.estados.index')],
                            ['label' => 'Escolaridades', 'href' => '#'],
                            ['label' => 'Estados civiles', 'href' => '#'],
                        ],
                    ],

                    'seguridad' => [
                        'label' => 'Seguridad',
                        'items' => [
                            ['label' => 'Usuarios', 'href' => '#'],
                            ['label' => 'Roles', 'href' => '#'],
                            ['label' => 'Permisos', 'href' => '#'],
                            ['label' => 'Menú', 'href' => '#'],
                        ],
                    ],
                ];
            @endphp

            @foreach ($groups as $key => $group)

                <div class="mb-2">

                    <button type="button" data-collapse-button="{{ $key }}" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm font-semibold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">
                        <span>{{ $group['label'] }}</span>
                        <span data-collapse-icon="{{ $key }}" class="text-slate-400 transition-transform">⌄</span>
                    </button>

                    <div data-collapse-panel="{{ $key }}" class="ml-3 mt-1 hidden space-y-1 border-l border-slate-200 pl-3">

                        @foreach ($group['items'] as $item)

                            <a href="{{ $item['href'] }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-300 group-hover:bg-blue-500"></span>
                                <span>{{ $item['label'] }}</span>
                            </a>

                        @endforeach

                    </div>

                </div>

            @endforeach

        </nav>

        <div class="border-t border-slate-200 px-4 py-4">

            <div class="flex items-center justify-between">
                <span class="text-[11px] text-slate-500">v1.0.0</span>

                <div class="flex items-center gap-2 text-xs font-medium text-emerald-600">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    En línea
                </div>
            </div>

        </div>

    </aside>

    <div class="min-w-0 flex-1">

        <header class="sticky top-0 z-40 flex h-[62px] items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-8">

            <div class="hidden min-w-0 items-center gap-2 text-sm sm:flex">
                <span class="text-slate-500">@yield('section', 'Sistema')</span>
                <span class="text-slate-300">/</span>
                <span class="font-medium text-slate-900">@yield('page', 'Inicio')</span>
            </div>

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-slate-200 bg-slate-100 font-semibold text-slate-600">
                    AS
                </div>

                <div class="hidden text-left md:block">
                    <div class="text-sm font-semibold text-slate-900">Admin Sistema</div>
                    <div class="text-xs text-slate-500">Administrador</div>
                </div>

                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50">
                    Cerrar sesión
                </button>

            </div>

        </header>

        @yield('content')

    </div>

</div>

@stack('scripts')
</body>
</html>
