<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\ModalidadDTO;
use App\Models\Catalogos\ModalidadModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Modalidad.
 */
final class ModalidadService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return ModalidadModel::query()->orderBy('id_modalidad')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(ModalidadDTO $dto): ModalidadModel
    {
        $modalidad = ModalidadModel::query()->create([
            'nombre_modalidad' => $dto->nombreModalidad,
            'descripcion' => $dto->descripcion,
            'activo' => $dto->activo,
        ]);

        return $modalidad->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(ModalidadDTO $dto): ModalidadModel
    {
        $modalidad = ModalidadModel::query()->findOrFail($dto->idModalidad);

        $modalidad->fill([
            'nombre_modalidad' => $dto->nombreModalidad,
            'descripcion' => $dto->descripcion,
            'fecha_actualizacion' => now(),
        ])->save();

        return $modalidad->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idModalidad, bool $activo): ModalidadModel
    {
        $modalidad = ModalidadModel::query()->findOrFail($idModalidad);

        $modalidad->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $modalidad->refresh();
    }
}
