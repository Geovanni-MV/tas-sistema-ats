<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Localidad.
 */
final readonly class LocalidadDTO
{
    public function __construct(
        public ?int $id_localidad,
        public int $id_estado,
        public string $nombre_localidad,
        public string $codigo_postal
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_localidad: isset($data['id_localidad']) ? (int) $data['id_localidad'] : null,
            id_estado: (int) ($data['id_estado'] ?? 0),
            nombre_localidad: trim((string) ($data['nombre_localidad'] ?? '')),
            codigo_postal: trim((string) ($data['codigo_postal'] ?? ''))
        );
    }
}
