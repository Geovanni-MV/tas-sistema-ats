<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | ATS TAS</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-semibold text-gray-900">Iniciar sesión</h1>
            <p class="mt-2 text-sm text-gray-500">Sistema ATS TAS</p>
        </div>

        @if ($errors->has('login'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first('login') }}
            </div>
        @endif

        <form method="POST" action="{{ route('sistema-ats-tas.login.procesar') }}" class="space-y-5">
            @csrf

            <div>
                <label for="correo_electronico" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input type="email" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico') }}" autocomplete="username" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                @error('correo_electronico')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="clave" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" id="clave" name="clave" autocomplete="current-clave" required class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                @error('clave')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                Iniciar sesión
            </button>
        </form>
    </div>
</body>
</html>
