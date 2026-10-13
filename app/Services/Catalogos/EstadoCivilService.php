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
        $estadoCivil = EstadoCivilModel::query()->create([
            'nombre_estado_civil' => $dto->nombreEstadoCivil,
            'descripcion' => $dto->descripcion,
            'activo' => $dto->activo,
        ]);

        return $estadoCivil->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(EstadoCivilDTO $dto): EstadoCivilModel
    {
        $estadoCivil = EstadoCivilModel::query()->findOrFail($dto->idEstadoCivil);

        $estadoCivil->fill([
            'nombre_estado_civil' => $dto->nombreEstadoCivil,
            'descripcion' => $dto->descripcion,
            'fecha_actualizacion' => now(),
        ])->save();

        return $estadoCivil->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idEstadoCivil, bool $activo): EstadoCivilModel
    {
        $estadoCivil = EstadoCivilModel::query()->findOrFail($idEstadoCivil);

        $estadoCivil->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $estadoCivil->refresh();
    }
}
