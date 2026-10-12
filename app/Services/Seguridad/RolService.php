<?php

namespace App\Services\Seguridad;

use App\DTO\Seguridad\RolDTO;
use App\Models\Seguridad\RolModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio para la entidad seguridad.roles
 */
final class RolService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return RolModel::query()->orderBy('id_rol')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el controlador.
     */
    public function crear(RolDTO $dto): RolModel
    {
        return RolModel::query()->create([
            'nombre_rol' => $dto->nombre_rol,
            'descripcion' => $dto->descripcion,
        ]);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona en cambiarEstatus().
     */
    public function actualizar(RolDTO $dto): RolModel
    {
        $registro = RolModel::query()->findOrFail($dto->id_rol);

        $registro->fill([
            'nombre_rol' => $dto->nombre_rol,
            'descripcion' => $dto->descripcion
        ])->save();

        return $registro->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_rol): RolModel
    {
        $registro = RolModel::query()->findOrFail($id_rol);

        $registro->fill([
            'activo' => ! $registro->activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $registro->refresh();
    }
}
