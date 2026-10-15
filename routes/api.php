<?php

/**ESQUEMA: RECLUTAMIENTO */
use App\Http\Controllers\Api\Reclutamiento\EmpresaController;

/**ESQUEMA: CATALOGOS */
use App\Http\Controllers\Api\Catalogos\DocumentoController;
use App\Http\Controllers\Api\Catalogos\EscolaridadController;
use App\Http\Controllers\Api\Catalogos\EstadoCivilController;
use App\Http\Controllers\Api\Catalogos\EstadoController;
use App\Http\Controllers\Api\Catalogos\LocalidadController;
use App\Http\Controllers\Api\Catalogos\ModalidadController;
use App\Http\Controllers\Api\Catalogos\SexoController;
use App\Http\Controllers\Api\Catalogos\TiempoUltimoTrabajoController;
use App\Http\Controllers\Api\Catalogos\TipoJornadaController;
use App\Http\Controllers\Api\Catalogos\TipoTurnoController;
/**ESQUEMA: SEGURIDAD */
use App\Http\Controllers\Api\Seguridad\MenuController;
use App\Http\Controllers\Api\Seguridad\PermisoController;
use App\Http\Controllers\Api\Seguridad\PermisoRolController;
use App\Http\Controllers\Api\Seguridad\RolController;
use App\Http\Controllers\Api\Seguridad\UsuarioController;
use App\Http\Controllers\Api\Seguridad\AuthController;
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
        /**
         * SEXOS
         */
        Route::prefix('sexos')->name('sexos.')->group(function () {
            Route::get('/', [SexoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [SexoController::class, 'crear'])->name('crear');
            Route::put('/{id_sexo}', [SexoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_sexo}/estatus-activo', [SexoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * ESTADOS
         */
        Route::prefix('estados')->name('estados.')->group(function () {
            Route::get('/', [EstadoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [EstadoController::class, 'crear'])->name('crear');
            Route::put('/{id_estado}', [EstadoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_estado}/estatus-activo', [EstadoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * ESTADOS CIVILES
         */
        Route::prefix('estados-civiles')->name('estados-civiles.')->group(function () {
            Route::get('/', [EstadoCivilController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [EstadoCivilController::class, 'crear'])->name('crear');
            Route::put('/{id_estado_civil}', [EstadoCivilController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_estado_civil}/estatus-activo', [EstadoCivilController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * ESCOLARIDADES
         */
        Route::prefix('escolaridades')->name('escolaridades.')->group(function () {
            Route::get('/', [EscolaridadController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [EscolaridadController::class, 'crear'])->name('crear');
            Route::put('/{id_escolaridad}', [EscolaridadController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_escolaridad}/estatus-activo', [EscolaridadController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * TIEMPO EN ULTIMO TRABAJO
         */
        Route::prefix('tiempos-ultimo-trabajo')->name('tiempos-ultimo-trabajo.')->group(function () {
            Route::get('/', [TiempoUltimoTrabajoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [TiempoUltimoTrabajoController::class, 'crear'])->name('crear');
            Route::put('/{id_tiempo_ultimo_trabajo}', [TiempoUltimoTrabajoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_tiempo_ultimo_trabajo}/estatus-activo', [TiempoUltimoTrabajoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * MODALIDADES
         */
        Route::prefix('modalidades')->name('modalidades.')->group(function () {
            Route::get('/', [ModalidadController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [ModalidadController::class, 'crear'])->name('crear');
            Route::put('/{id_modalidad}', [ModalidadController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_modalidad}/estatus-activo', [ModalidadController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * TIPOS DE JORNADA
         */
        Route::prefix('tipos-jornada')->name('tipos-jornada.')->group(function () {
            Route::get('/', [TipoJornadaController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [TipoJornadaController::class, 'crear'])->name('crear');
            Route::put('/{id_tipo_jornada}', [TipoJornadaController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_tipo_jornada}/estatus-activo', [TipoJornadaController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * TIPOS DE TURNO
         */
        Route::prefix('tipos-turno')->name('tipos-turno.')->group(function () {
            Route::get('/', [TipoTurnoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [TipoTurnoController::class, 'crear'])->name('crear');
            Route::put('/{id_tipo_turno}', [TipoTurnoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_tipo_turno}/estatus-activo', [TipoTurnoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * DOCUMENTOS
         */
        Route::prefix('documentos')->name('documentos.')->group(function () {
            Route::get('/', [DocumentoController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [DocumentoController::class, 'crear'])->name('crear');
            Route::put('/{id_documento}', [DocumentoController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_documento}/estatus-activo', [DocumentoController::class, 'cambiarEstatus'])->name('cambiar-estatus');
        });
        /**
         * LOCALIDADES
         */
        Route::prefix('localidades')->name('localidades.')->group(function () {
            Route::get('/', [LocalidadController::class, 'obtenerDatos'])->name('obtener-datos');
            Route::post('/', [LocalidadController::class, 'crear'])->name('crear');
            Route::put('/{id_localidad}', [LocalidadController::class, 'actualizar'])->name('actualizar');
            Route::patch('/{id_localidad}/estatus-activo', [LocalidadController::class, 'cambiarEstatus'])->name('cambiar-estatus');
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
