<?php

namespace App\Services\Seguridad;

use App\DTO\Seguridad\PermisoRolDTO;
use App\DTO\Seguridad\PermisoRolSincronizacionDTO;
use App\Models\Seguridad\PermisoRolModel;
use App\Models\Seguridad\RolModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    /**
     * Sincroniza en una sola operación las asignaciones de un rol.
     *
     * Cada elemento del DTO indica el estado final deseado de una combinación permiso + menú:
     *  - no existe y activo = true  -> se inserta.
     *  - no existe y activo = false -> se omite (no tiene sentido crear un registro inactivo).
     *  - existe con otro estado     -> se actualiza "activo" y "fecha_actualizacion".
     *  - existe con el mismo estado -> sin cambios.
     * Las combinaciones del rol que no vienen en la lista no se tocan.
     *
     * Todo ocurre en una transacción y con un número fijo de consultas, sin importar el tamaño
     * de la lista (una de lectura, una de inserción masiva y dos de actualización masiva).
     *
     * @return array{resumen: array<string, int>, asignaciones: Collection}
     */
    public function sincronizar(PermisoRolSincronizacionDTO $dto): array
    {
        return DB::transaction(function () use ($dto): array {
            // Bloquea la fila del rol hasta terminar la transacción. La tabla no tiene una restricción
            // única (permiso, rol, menú), así que esto evita que dos sincronizaciones simultáneas del
            // mismo rol inserten la misma combinación dos veces.
            RolModel::query()->whereKey($dto->id_rol)->lockForUpdate()->firstOrFail();

            $existentes = PermisoRolModel::query()
                ->where('id_rol', $dto->id_rol)
                ->get()
                ->groupBy(fn (PermisoRolModel $asignacion) => $this->clave($asignacion->id_menu, $asignacion->id_permiso));

            $crear = [];
            $idsActivar = [];
            $idsDesactivar = [];
            $resumen = ['creadas' => 0, 'activadas' => 0, 'desactivadas' => 0, 'sin_cambios' => 0, 'omitidas' => 0];

            foreach ($dto->asignaciones as $deseada) {
                $registros = $existentes->get($this->clave($deseada->id_menu, $deseada->id_permiso));

                if ($registros === null) {
                    if ($deseada->activo) {
                        $crear[] = [
                            'id_permiso' => $deseada->id_permiso,
                            'id_rol' => $dto->id_rol,
                            'id_menu' => $deseada->id_menu,
                            'activo' => true,
                        ];
                        $resumen['creadas']++;
                    } else {
                        $resumen['omitidas']++;
                    }

                    continue;
                }

                $distintos = $registros->filter(fn (PermisoRolModel $r) => (bool) $r->activo !== $deseada->activo);

                if ($distintos->isEmpty()) {
                    $resumen['sin_cambios']++;
                    continue;
                }

                if ($deseada->activo) {
                    array_push($idsActivar, ...$distintos->pluck('id_permiso_rol')->all());
                    $resumen['activadas']++;
                } else {
                    array_push($idsDesactivar, ...$distintos->pluck('id_permiso_rol')->all());
                    $resumen['desactivadas']++;
                }
            }

            if ($crear !== []) {
                PermisoRolModel::query()->insert($crear);
            }


            foreach ([[$idsActivar, true], [$idsDesactivar, false]] as [$ids, $activo]) {
                if ($ids !== []) {
                    PermisoRolModel::query()
                        ->whereIn('id_permiso_rol', $ids)
                        ->update(['activo' => $activo]);
                }
            }

            return [
                'resumen' => $resumen,
                'asignaciones' => $this->listar($dto->id_rol),
            ];
        });
    }

    private function clave(int|string $id_menu, int|string $id_permiso): string
    {
        return ((int) $id_menu) . '-' . ((int) $id_permiso);
    }
}
