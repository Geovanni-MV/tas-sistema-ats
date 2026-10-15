<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\EstadoDTO;
use App\Models\Catalogos\EstadoModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Estado.
 */
final class EstadoService
{
    /**
     * Obtiene todos los estados ordenados por nombre.
     */
    public function listar(): Collection
    {
        return EstadoModel::query()
            ->orderBy('nombre_estado')
            ->get();
    }

    /**
     * Crea un estado a partir del DTO recibido.
     */
    public function crear(EstadoDTO $dto): EstadoModel
    {
        return EstadoModel::query()->create([
            'nombre_estado' => $dto->nombre_estado,
            'clave_estado' => $dto->clave_estado,
            
        ]);
    }

    /**
     * Actualiza nombre y clave del estado.
     * El estatus se administra de manera independiente.
     */
    public function actualizar(EstadoDTO $dto): EstadoModel
    {
        $estado = EstadoModel::query()->findOrFail($dto->id_estado);

        $estado->fill([
            'nombre_estado' => $dto->nombre_estado,
            'clave_estado' => $dto->clave_estado,
            
        ])->save();

        return $estado->refresh();
    }

    /**
     * Activa o desactiva un estado.
     */
    public function cambiarEstatus(int $id_estado): EstadoModel
    {
        $estado = EstadoModel::query()->findOrFail($id_estado);

        $estado->fill([
            'activo' => !$estado->activo,
            
        ])->save();

        return $estado->refresh();
    }
}
