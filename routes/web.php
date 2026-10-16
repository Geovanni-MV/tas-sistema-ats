<?php
use App\Http\Controllers\Web\DashboardController;
//Reclutamiento

//Catalogos
use App\Http\Controllers\Web\Catalogos\DocumentoController;
use App\Http\Controllers\Web\Catalogos\EscolaridadController;
use App\Http\Controllers\Web\Catalogos\EstadoCivilController;
use App\Http\Controllers\Web\Catalogos\ModalidadController;
use App\Http\Controllers\Web\Catalogos\SexoController;
use App\Http\Controllers\Web\Catalogos\TiempoUltimoTrabajoController;
use App\Http\Controllers\Web\Catalogos\TipoJornadaController;
use App\Http\Controllers\Web\Catalogos\TipoTurnoController;
//Seguridad
use App\Http\Controllers\Web\Seguridad\MenuController;
use App\Http\Controllers\Web\Seguridad\PermisoController;
use App\Http\Controllers\Web\Seguridad\PermisoRolController;
use App\Http\Controllers\Web\Seguridad\RolController;
use App\Http\Controllers\Web\Seguridad\UsuarioController;
use App\Http\Controllers\Web\Seguridad\AuthController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web - Sistema ATS TAS
|--------------------------------------------------------------------------
|
| Todas las vistas del sistema cuelgan del prefijo "sistema-ats-tas".
| Cada módulo utiliza su propio prefijo para mantener una estructura clara.
|
*/

Route::redirect('/', '/sistema-ats-tas/dashboard');

Route::prefix('sistema-ats-tas')->name('sistema-ats-tas.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'renderView'])->name('dashboard.index');

    Route::prefix('reclutamiento')->name('reclutamiento.')->group(function () {
        Route::get('/', fn () => redirect('#'))
            ->name('index');
    });

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', fn () => redirect('#'))
            ->name('index');
    });

    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::get('/documentos', [DocumentoController::class, 'renderView'])
            ->name('documentos.index');

        Route::get('/escolaridades', [EscolaridadController::class, 'renderView'])
            ->name('escolaridades.index');

        Route::get('/estados-civiles', [EstadoCivilController::class, 'renderView'])
            ->name('estados-civiles.index');

        Route::get('/modalidades', [ModalidadController::class, 'renderView'])
            ->name('modalidades.index');

        Route::get('/sexos', [SexoController::class, 'renderView'])
            ->name('sexos.index');

        Route::get('/tiempos-ultimo-trabajo', [TiempoUltimoTrabajoController::class, 'renderView'])
            ->name('tiempos-ultimo-trabajo.index');

        Route::get('/tipos-jornada', [TipoJornadaController::class, 'renderView'])
            ->name('tipos-jornada.index');

        Route::get('/tipos-turno', [TipoTurnoController::class, 'renderView'])
            ->name('tipos-turno.index');
    });
    
    Route::prefix('seguridad')->name('seguridad.')->group(function () {
        Route::get('/', fn () => redirect('#'))
            ->name('index');
        Route::get('/usuarios', [UsuarioController::class, 'renderView'])
            ->name('usuarios.index');
        Route::get('/roles', [RolController::class, 'renderView'])
            ->name('roles.index');
        Route::get('/permisos', [PermisoController::class, 'renderView'])
            ->name('permisos.index');
        Route::get('/menu', [MenuController::class, 'renderView'])
            ->name('menu.index');
        Route::get('/permisos-roles', [PermisoRolController::class, 'renderView'])
            ->name('permisos-roles.index');
    });

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'renderView'])
            ->name('login');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('login.procesar');
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

});
