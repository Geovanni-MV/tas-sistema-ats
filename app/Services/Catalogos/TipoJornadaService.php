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
        $tipo_jornada = TipoJornadaModel::query()->create([
            'nombre_tipo_jornada' => $dto->nombre_tipo_jornada,
            'descripcion' => $dto->descripcion,
            
        ]);

        return $tipo_jornada->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
    */
    public function actualizar(TipoJornadaDTO $dto): TipoJornadaModel
    {
        $tipo_jornada = TipoJornadaModel::query()->findOrFail($dto->id_tipo_jornada);

        $tipo_jornada->fill([
            'nombre_tipo_jornada' => $dto->nombre_tipo_jornada,
            'descripcion' => $dto->descripcion,
            
        ])->save();

        return $tipo_jornada->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idTipoJornada): TipoJornadaModel
    {
        $tipo_jornada = TipoJornadaModel::query()->findOrFail($idTipoJornada);

        $tipo_jornada->fill([
            'activo' => !$tipo_jornada->activo,
            
        ])->save();

        return $tipo_jornada->refresh();
    }
}
