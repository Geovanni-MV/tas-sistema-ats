<?php

namespace App\DTO\Seguridad;

/**
 * DTO inmutable que representa los datos transferidos de la entidad seguridad.permisos_roles
 */
final readonly class PermisoRolDTO
{
    public function __construct(
        public ?int $id_permiso_rol,
        public int $id_permiso,
        public int $id_rol,
        public int $id_menu
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_permiso_rol: isset($data['id_permiso_rol']) ? (int) $data['id_permiso_rol'] : null,
            id_permiso: (int) ($data['id_permiso'] ?? 0),
            id_rol: (int) ($data['id_rol'] ?? 0),
            id_menu: (int) ($data['id_menu'] ?? 0)
        );
    }
}
