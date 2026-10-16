<?php

namespace App\DTO\Seguridad;

/**
 * DTO inmutable con el estado deseado de una asignación permiso + menú dentro de un rol.
 * Es cada elemento de la lista que recibe la sincronización de permisos de un rol.
 */
final readonly class PermisoRolEstadoDTO
{
    public function __construct(
        public int $id_permiso,
        public int $id_menu,
        public bool $activo
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_permiso: (int) ($data['id_permiso'] ?? 0),
            id_menu: (int) ($data['id_menu'] ?? 0),
            activo: (bool) ($data['activo'] ?? false)
        );
    }
}
