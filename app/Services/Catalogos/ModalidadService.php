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
            'nombre_modalidad' => $dto->nombre_modalidad,
            'descripcion' => $dto->descripcion,
            
        ]);

        return $modalidad->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(ModalidadDTO $dto): ModalidadModel
    {
        $modalidad = ModalidadModel::query()->findOrFail($dto->id_modalidad);

        $modalidad->fill([
            'nombre_modalidad' => $dto->nombre_modalidad,
            'descripcion' => $dto->descripcion,
            
        ])->save();

        return $modalidad->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_modalidad): ModalidadModel
    {
        $modalidad = ModalidadModel::query()->findOrFail($id_modalidad);

        $modalidad->fill([
            'activo' => !$modalidad->activo,
            
        ])->save();

        return $modalidad->refresh();
    }
}
