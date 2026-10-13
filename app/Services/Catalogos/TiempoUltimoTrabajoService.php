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
        $tiempoUltimoTrabajo = TiempoUltimoTrabajoModel::query()->create([
            'nombre_tiempo_ultimo_trabajo' => $dto->nombreTiempoUltimoTrabajo,
            'descripcion' => $dto->descripcion,
            'activo' => $dto->activo,
        ]);

        return $tiempoUltimoTrabajo->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(TiempoUltimoTrabajoDTO $dto): TiempoUltimoTrabajoModel
    {
        $tiempoUltimoTrabajo = TiempoUltimoTrabajoModel::query()->findOrFail($dto->idTiempoUltimoTrabajo);

        $tiempoUltimoTrabajo->fill([
            'nombre_tiempo_ultimo_trabajo' => $dto->nombreTiempoUltimoTrabajo,
            'descripcion' => $dto->descripcion,
            'fecha_actualizacion' => now(),
        ])->save();

        return $tiempoUltimoTrabajo->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idTiempoUltimoTrabajo, bool $activo): TiempoUltimoTrabajoModel
    {
        $tiempoUltimoTrabajo = TiempoUltimoTrabajoModel::query()->findOrFail($idTiempoUltimoTrabajo);

        $tiempoUltimoTrabajo->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $tiempoUltimoTrabajo->refresh();
    }
}
