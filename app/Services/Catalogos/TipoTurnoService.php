<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\TipoTurnoDTO;
use App\Models\Catalogos\TipoTurnoModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Tipo de turno.
 */
final class TipoTurnoService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return TipoTurnoModel::query()->orderBy('id_tipo_turno')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(TipoTurnoDTO $dto): TipoTurnoModel
    {
        $tipo_turno = TipoTurnoModel::query()->create([
            'nombre_tipo_turno' => $dto->nombre_tipo_turno,
            'descripcion' => $dto->descripcion,
            
        ]);

        return $tipo_turno->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(TipoTurnoDTO $dto): TipoTurnoModel
    {
        $tipo_turno = TipoTurnoModel::query()->findOrFail($dto->id_tipo_turno);

        $tipo_turno->fill([
            'nombre_tipo_turno' => $dto->nombre_tipo_turno,
            'descripcion' => $dto->descripcion,
            
        ])->save();

        return $tipo_turno->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idTipoTurno): TipoTurnoModel
    {
        $tipo_turno = TipoTurnoModel::query()->findOrFail($idTipoTurno);

        $tipo_turno->fill([
            'activo' => !$tipo_turno->activo,
            
        ])->save();

        return $tipo_turno->refresh();
    }
}
