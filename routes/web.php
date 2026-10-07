<?php

use App\Http\Controllers\Catalogos\SexoController;
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
    Route::get('/dashboard', fn () => redirect('#'))->name('dashboard');

    Route::prefix('reclutamiento')->name('reclutamiento.')->group(function () {
        Route::get('/', fn () => redirect('#'))->name('index');
    });

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', fn () => redirect('#'))->name('index');
    });

    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::get('/sexos', [SexoController::class, 'vista'])->name('sexos.index');
        Route::get('/estados', [EstadoController::class, 'vista'])->name('estados.index');

    });

    Route::prefix('seguridad')->name('seguridad.')->group(function () {
        Route::get('/', fn () => redirect('#'))->name('index');
    });
});
