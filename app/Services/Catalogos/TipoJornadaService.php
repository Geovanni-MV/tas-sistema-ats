<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\TipoJornadaDTO;
use App\Models\Catalogos\TipoJornadaModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Tipo de jornada.
 */
final class TipoJornadaService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return TipoJornadaModel::query()->orderBy('id_tipo_jornada')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(TipoJornadaDTO $dto): TipoJornadaModel
    {
        $tipoJornada = TipoJornadaModel::query()->create([
            'nombre_tipo_jornada' => $dto->nombreTipoJornada,
            'descripcion' => $dto->descripcion,
            'activo' => $dto->activo,
        ]);

        return $tipoJornada->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(TipoJornadaDTO $dto): TipoJornadaModel
    {
        $tipoJornada = TipoJornadaModel::query()->findOrFail($dto->idTipoJornada);

        $tipoJornada->fill([
            'nombre_tipo_jornada' => $dto->nombreTipoJornada,
            'descripcion' => $dto->descripcion,
            'fecha_actualizacion' => now(),
        ])->save();

        return $tipoJornada->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idTipoJornada, bool $activo): TipoJornadaModel
    {
        $tipoJornada = TipoJornadaModel::query()->findOrFail($idTipoJornada);

        $tipoJornada->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $tipoJornada->refresh();
    }
}
