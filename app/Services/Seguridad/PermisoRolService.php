<?php

namespace App\Services\Seguridad;

use App\DTO\Seguridad\PermisoRolDTO;
use App\Models\Seguridad\PermisoRolModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio para la entidad seguridad.permisos_roles
 */
final class PermisoRolService
{
    private const RELACIONES = [
        'permiso:id_permiso,nombre_permiso',
        'rol:id_rol,nombre_rol',
        'menu:id_menu,nombre_menu',
    ];

    /**
     * Obtiene los registros ordenados por identificador.
     * Si se recibe $id_rol, solo devuelve las asignaciones de ese rol.
     */
    public function listar(?int $id_rol = null): Collection
    {
        return PermisoRolModel::query()
            ->with(self::RELACIONES)
            ->when($id_rol !== null, fn ($query) => $query->where('id_rol', $id_rol))
            ->orderBy('id_permiso_rol')
            ->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el controlador.
     */
    public function crear(PermisoRolDTO $dto): PermisoRolModel
    {
        $asignacion = PermisoRolModel::query()->create([
            'id_permiso' => $dto->id_permiso,
            'id_rol' => $dto->id_rol,
            'id_menu' => $dto->id_menu,
        ]);

        return $asignacion->refresh()->load(self::RELACIONES);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona en cambiarEstatus().
     */
    public function actualizar(PermisoRolDTO $dto): PermisoRolModel
    {
        $asignacion = PermisoRolModel::query()->findOrFail($dto->id_permiso_rol);

        $asignacion->fill([
            'id_permiso' => $dto->id_permiso,
            'id_rol' => $dto->id_rol,
            'id_menu' => $dto->id_menu,
            'fecha_actualizacion' => now(),
        ])->save();

        return $asignacion->refresh()->load(self::RELACIONES);
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_permiso_rol): PermisoRolModel
    {
        $asignacion = PermisoRolModel::query()->findOrFail($id_permiso_rol);

        $asignacion->fill([
            'activo' => ! $asignacion->activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $asignacion->refresh()->load(self::RELACIONES);
    }
}
