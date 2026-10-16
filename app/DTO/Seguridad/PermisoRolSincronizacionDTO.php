<?php

namespace App\DTO\Seguridad;

/**
 * DTO inmutable de la sincronización de permisos de un rol:
 * el rol y la lista de asignaciones (permiso + menú + activo) que deben quedar aplicadas.
 */
final readonly class PermisoRolSincronizacionDTO
{
    /**
     * @param  list<PermisoRolEstadoDTO>  $asignaciones
     */
    public function __construct(
        public int $id_rol,
        public array $asignaciones
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_rol: (int) ($data['id_rol'] ?? 0),
            asignaciones: array_map(
                fn (array $asignacion) => PermisoRolEstadoDTO::fromArray($asignacion),
                array_values($data['asignaciones'] ?? [])
            )
        );
    }
}
