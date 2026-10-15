<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\LocalidadDTO;
use App\Models\Catalogos\LocalidadModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Localidad.
 */
final class LocalidadService
{
    private const RELACIONES = ['estado:id_estado,nombre_estado'];

    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return LocalidadModel::query()->with(self::RELACIONES)->orderBy('id_localidad')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(LocalidadDTO $dto): LocalidadModel
    {
        $localidad = LocalidadModel::query()->create([
            'id_estado' => $dto->id_estado,
            'nombre_localidad' => $dto->nombre_localidad,
            'codigo_postal' => $dto->codigo_postal
        ]);

        return $localidad->refresh()->load(self::RELACIONES);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(LocalidadDTO $dto): LocalidadModel
    {
        $localidad = LocalidadModel::query()->findOrFail($dto->id_localidad);

        $localidad->fill([
            'id_estado' => $dto->id_estado,
            'nombre_localidad' => $dto->nombre_localidad,
            'codigo_postal' => $dto->codigo_postal,
            
        ])->save();

        return $localidad->refresh()->load(self::RELACIONES);
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_localidad): LocalidadModel
    {
        $localidad = LocalidadModel::query()->findOrFail($id_localidad);

        $localidad->fill([
            'activo' => !$localidad->activo,
        ])->save();

        return $localidad->refresh()->load(self::RELACIONES);
    }
}
