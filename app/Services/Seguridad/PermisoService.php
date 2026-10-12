<?php

namespace App\Services\Seguridad;

use App\DTO\Seguridad\PermisoDTO;
use App\Models\Seguridad\PermisoModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio para la entidad seguridad.permisos
 */
final class PermisoService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return PermisoModel::query()->orderBy('id_permiso')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el controlador.
     */
    public function crear(PermisoDTO $dto): PermisoModel
    {
        return PermisoModel::query()->create([
            'nombre_permiso' => $dto->nombre_permiso,
            'descripcion' => $dto->descripcion,
        ]);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona en cambiarEstatus().
     */
    public function actualizar(PermisoDTO $dto): PermisoModel
    {
        $registro = PermisoModel::query()->findOrFail($dto->id_permiso);

        $registro->fill([
            'nombre_permiso' => $dto->nombre_permiso,
            'descripcion' => $dto->descripcion,
            'fecha_actualizacion' => now(),
        ])->save();

        return $registro->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_permiso): PermisoModel
    {
        $registro = PermisoModel::query()->findOrFail($id_permiso);

        $registro->fill([
            'activo' => ! $registro->activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $registro->refresh();
    }
}
