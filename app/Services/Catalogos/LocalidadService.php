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
            'id_estado' => $dto->idEstado,
            'nombre_localidad' => $dto->nombreLocalidad,
            'codigo_postal' => $dto->codigoPostal,
            'activo' => $dto->activo,
        ]);

        return $localidad->refresh()->load(self::RELACIONES);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(LocalidadDTO $dto): LocalidadModel
    {
        $localidad = LocalidadModel::query()->findOrFail($dto->idLocalidad);

        $localidad->fill([
            'id_estado' => $dto->idEstado,
            'nombre_localidad' => $dto->nombreLocalidad,
            'codigo_postal' => $dto->codigoPostal,
            'fecha_actualizacion' => now(),
        ])->save();

        return $localidad->refresh()->load(self::RELACIONES);
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idLocalidad, bool $activo): LocalidadModel
    {
        $localidad = LocalidadModel::query()->findOrFail($idLocalidad);

        $localidad->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $localidad->refresh()->load(self::RELACIONES);
    }
}
