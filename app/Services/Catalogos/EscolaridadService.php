<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\EscolaridadDTO;
use App\Models\Catalogos\EscolaridadModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Escolaridad.
 */
final class EscolaridadService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return EscolaridadModel::query()->orderBy('id_escolaridad')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(EscolaridadDTO $dto): EscolaridadModel
    {
        $escolaridad = EscolaridadModel::query()->create([
            'nombre_escolaridad' => $dto->nombre_escolaridad,
            'descripcion' => $dto->descripcion
        ]);

        return $escolaridad->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(EscolaridadDTO $dto): EscolaridadModel
    {
        $escolaridad = EscolaridadModel::query()->findOrFail($dto->id_escolaridad);

        $escolaridad->fill([
            'nombre_escolaridad' => $dto->nombre_escolaridad,
            'descripcion' => $dto->descripcion,
            
        ])->save();

        return $escolaridad->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_escolaridad): EscolaridadModel
    {
        $escolaridad = EscolaridadModel::query()->findOrFail($id_escolaridad);

        $escolaridad->fill([
            'activo' => !$escolaridad->activo,
            
        ])->save();

        return $escolaridad->refresh();
    }
}
