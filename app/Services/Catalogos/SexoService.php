<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\SexoDTO;
use App\Models\Catalogos\Sexo;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Sexo.
 */
final class SexoService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return Sexo::query()->orderBy('id_sexo')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(SexoDTO $dto): Sexo
    {
        return Sexo::query()->create([
            'nombre_sexo' => $dto->nombreSexo,
            'descripcion' => $dto->descripcion,
            'activo' => $dto->activo,
        ]);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(SexoDTO $dto): Sexo
    {
        $sexo = Sexo::query()->findOrFail($dto->idSexo);

        $sexo->fill([
            'nombre_sexo' => $dto->nombreSexo,
            'descripcion' => $dto->descripcion,
            'fecha_actualizacion' => now(),
        ])->save();

        return $sexo->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idSexo, bool $activo): Sexo
    {
        $sexo = Sexo::query()->findOrFail($idSexo);

        $sexo->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $sexo->refresh();
    }
}
