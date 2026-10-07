<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\EstadoDTO;
use App\Models\Catalogos\Estado;
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
        return Estado::query()
            ->orderBy('nombre_estado')
            ->get();
    }

    /**
     * Crea un estado a partir del DTO recibido.
     */
    public function crear(EstadoDTO $dto): Estado
    {
        return Estado::query()->create([
            'nombre_estado' => $dto->nombreEstado,
            'clave_estado' => $dto->claveEstado,
            'activo' => $dto->activo,
        ]);
    }

    /**
     * Actualiza nombre y clave del estado.
     * El estatus se administra de manera independiente.
     */
    public function actualizar(EstadoDTO $dto): Estado
    {
        $estado = Estado::query()->findOrFail($dto->idEstado);

        $estado->fill([
            'nombre_estado' => $dto->nombreEstado,
            'clave_estado' => $dto->claveEstado,
            'fecha_actualizacion' => now(),
        ])->save();

        return $estado->refresh();
    }

    /**
     * Activa o desactiva un estado.
     */
    public function cambiarEstatus(int $idEstado, bool $activo): Estado
    {
        $estado = Estado::query()->findOrFail($idEstado);

        $estado->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $estado->refresh();
    }
}
