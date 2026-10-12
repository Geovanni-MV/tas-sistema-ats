<?php

/**ESQUEMA: RECLUTAMIENTO */
use App\Http\Controllers\Reclutamiento\EmpresaController;

/**ESQUEMA: CATALOGOS */
use App\Http\Controllers\Catalogos\EstadoController;
use App\Http\Controllers\Catalogos\SexoController;
/**ESQUEMA: SEGURIDAD */
use App\Http\Controllers\Seguridad\MenuController;
use App\Http\Controllers\Seguridad\PermisoController;
use App\Http\Controllers\Seguridad\PermisoRolController;
use App\Http\Controllers\Seguridad\RolController;
use App\Http\Controllers\Seguridad\UsuarioController;
/** */
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
            Route::patch('/{id_empresa}/estatus-activo', [EmpresaController::class, 'cambiarEstatus'])->name('cambiar-estatus');
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
            Route::patch('/{idSexo}/estatus-activo', [SexoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        Route::prefix('estados')->name('estados.')->group(function () {
            Route::get('/', [EstadoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [EstadoController::class, 'crear'])->name('crear');
            Route::put('/{idEstado}', [EstadoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{idEstado}/estatus-activo', [EstadoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
    });

    Route::prefix('seguridad')->name('seguridad.')->group(function () {
        // Endpoints futuros del módulo Seguridad.
        /**
         * ROLES
         */
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [RolController::class, 'crear'])->name('crear');
            Route::put('/{id_rol}', [RolController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_rol}estatus-activo', [RolController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * PERMISOS
         */
        Route::prefix('permisos')->name('permisos.')->group(function () {
            Route::get('/', [PermisoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [PermisoController::class, 'crear'])->name('crear');
            Route::put('/{id_permiso}', [PermisoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_permiso}/estatus-activo', [PermisoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * MENU
         */
        Route::prefix('menu')->name('menu.')->group(function () {
            Route::get('/', [MenuController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [MenuController::class, 'crear'])->name('crear');
            Route::put('/{id_menu}', [MenuController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_menu}/estatus-activo', [MenuController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * CONFIGURACIÓN DE PERMISOS - ROLES
         */
        Route::prefix('permisos-roles')->name('permisos-roles.')->group(function () {
            Route::get('/', [PermisoRolController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [PermisoRolController::class, 'crear'])->name('crear');
            Route::put('/{id_permiso_rol}', [PermisoRolController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_permiso_rol}/estatus-activo', [PermisoRolController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * USUARIOS
         */
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('/', [UsuarioController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [UsuarioController::class, 'crear'])->name('crear');
            Route::put('/{id_usuario}', [UsuarioController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_usuario}/clave', [UsuarioController::class, 'cambiarClave'])->name('cambiar-clave');
            Route::patch('/{id_usuario}/estatus-activo', [UsuarioController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
    });
});
