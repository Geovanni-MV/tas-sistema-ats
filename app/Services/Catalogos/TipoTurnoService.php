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
        $tipoTurno = TipoTurnoModel::query()->create([
            'nombre_tipo_turno' => $dto->nombreTipoTurno,
            'descripcion' => $dto->descripcion,
            'activo' => $dto->activo,
        ]);

        return $tipoTurno->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(TipoTurnoDTO $dto): TipoTurnoModel
    {
        $tipoTurno = TipoTurnoModel::query()->findOrFail($dto->idTipoTurno);

        $tipoTurno->fill([
            'nombre_tipo_turno' => $dto->nombreTipoTurno,
            'descripcion' => $dto->descripcion,
            'fecha_actualizacion' => now(),
        ])->save();

        return $tipoTurno->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idTipoTurno, bool $activo): TipoTurnoModel
    {
        $tipoTurno = TipoTurnoModel::query()->findOrFail($idTipoTurno);

        $tipoTurno->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $tipoTurno->refresh();
    }
}
