<?php

/**ESQUEMA: RECLUTAMIENTO */
use App\Http\Controllers\Reclutamiento\EmpresaController;

/**ESQUEMA: CATALOGOS */
use App\Http\Controllers\Catalogos\EstadoController;
use App\Http\Controllers\Catalogos\SexoController;
/**ESQUEMA: SEGURIDAD */
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API v1
|--------------------------------------------------------------------------
|
| Se conserva la misma división lógica de módulos que en web, reemplazando
| el prefijo "sistema-ats-tas" por "api/v1".
|
*/
Route::prefix('/v1')->name('api.v1.')->group(function () {
    Route::prefix('reclutamiento')->name('reclutamiento.')->group(function () {
        // Endpoints futuros del módulo Reclutamiento.
        Route::prefix('empresas')->name('empresas.')->group(function () {
            Route::get('/', [EmpresaController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [EmpresaController::class, 'crear'])->name('crear');
            Route::put('/{id_empresa}', [EmpresaController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_empresa}/estatus', [EmpresaController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
    });

    Route::prefix('reportes')->name('reportes.')->group(function () {
        // Endpoints futuros del módulo Reportes.
    });

    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::prefix('sexos')->name('sexos.')->group(function () {
            Route::get('/', [SexoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [SexoController::class, 'crear'])->name('crear');
            Route::put('/{idSexo}', [SexoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{idSexo}/estatus', [SexoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        Route::prefix('estados')->name('estados.')->group(function () {
            Route::get('/', [EstadoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [EstadoController::class, 'crear'])->name('crear');
            Route::put('/{idEstado}', [EstadoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{idEstado}/estatus', [EstadoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
    });

    Route::prefix('seguridad')->name('seguridad.')->group(function () {
        // Endpoints futuros del módulo Seguridad.
    });
});
