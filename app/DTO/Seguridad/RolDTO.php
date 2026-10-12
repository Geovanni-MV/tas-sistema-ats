<?php

namespace App\DTO\Seguridad;

/**
 * DTO inmutable que representa los datos transferidos de la entidad seguridad.roles
 */
final readonly class RolDTO
{
    public function __construct(
        public ?int $id_rol,
        public string $nombre_rol,
        public ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_rol: isset($data['id_rol']) ? (int) $data['id_rol'] : null,
            nombre_rol: trim((string) ($data['nombre_rol'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== ''
                ? trim((string) $data['descripcion']) : null
        );
    }
}
