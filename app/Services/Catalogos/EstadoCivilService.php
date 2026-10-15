<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\EstadoCivilDTO;
use App\Models\Catalogos\EstadoCivilModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Estado civil.
 */
final class EstadoCivilService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return EstadoCivilModel::query()->orderBy('id_estado_civil')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(EstadoCivilDTO $dto): EstadoCivilModel
    {
        $estado_civil = EstadoCivilModel::query()->create([
            'nombre_estado_civil' => $dto->nombre_estado_civil,
            'descripcion' => $dto->descripcion
        ]);

        return $estado_civil->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(EstadoCivilDTO $dto): EstadoCivilModel
    {
        $estado_civil = EstadoCivilModel::query()->findOrFail($dto->id_estado_civil);

        $estado_civil->fill([
            'nombre_estado_civil' => $dto->nombre_estado_civil,
            'descripcion' => $dto->descripcion
        ])->save();

        return $estado_civil->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_estado_civil): EstadoCivilModel
    {
        $estado_civil = EstadoCivilModel::query()->findOrFail($id_estado_civil);

        $estado_civil->fill([
            'activo' => !$estado_civil->activo
        ])->save();

        return $estado_civil->refresh();
    }
}
