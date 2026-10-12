<?php

namespace App\DTO\Seguridad;

/**
 * DTO inmutable que representa los datos transferidos de la entidad seguridad.permisos
 */
final readonly class PermisoDTO
{
    public function __construct(
        public ?int $id_permiso,
        public string $nombre_permiso,
        public ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_permiso: isset($data['id_permiso']) ? (int) $data['id_permiso'] : null,
            nombre_permiso: trim((string) ($data['nombre_permiso'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== ''
                ? trim((string) $data['descripcion']) : null
        );
    }
}
