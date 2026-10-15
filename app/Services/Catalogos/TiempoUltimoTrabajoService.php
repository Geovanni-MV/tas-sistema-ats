<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\TiempoUltimoTrabajoDTO;
use App\Models\Catalogos\TiempoUltimoTrabajoModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Tiempo del último trabajo.
 */
final class TiempoUltimoTrabajoService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return TiempoUltimoTrabajoModel::query()->orderBy('id_tiempo_ultimo_trabajo')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(TiempoUltimoTrabajoDTO $dto): TiempoUltimoTrabajoModel
    {
        $tiempo_ultimo_trabajo = TiempoUltimoTrabajoModel::query()->create([
            'nombre_tiempo_ultimo_trabajo' => $dto->nombre_tiempo_ultimo_trabajo,
            'descripcion' => $dto->descripcion,
            
        ]);

        return $tiempo_ultimo_trabajo->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(TiempoUltimoTrabajoDTO $dto): TiempoUltimoTrabajoModel
    {
        $tiempo_ultimo_trabajo = TiempoUltimoTrabajoModel::query()->findOrFail($dto->id_tiempo_ultimo_trabajo);

        $tiempo_ultimo_trabajo->fill([
            'nombre_tiempo_ultimo_trabajo' => $dto->nombre_tiempo_ultimo_trabajo,
            'descripcion' => $dto->descripcion,
            
        ])->save();

        return $tiempo_ultimo_trabajo->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_tiempo_ultimo_trabajo): TiempoUltimoTrabajoModel
    {
        $tiempo_ultimo_trabajo = TiempoUltimoTrabajoModel::query()->findOrFail($id_tiempo_ultimo_trabajo);

        $tiempo_ultimo_trabajo->fill([
            'activo' => !$tiempo_ultimo_trabajo->activo,
            
        ])->save();

        return $tiempo_ultimo_trabajo->refresh();
    }
}
