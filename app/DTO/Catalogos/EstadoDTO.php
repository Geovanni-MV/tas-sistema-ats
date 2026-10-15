<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Estado.
 */
final readonly class EstadoDTO
{
    public function __construct(
        public ?int $id_estado,
        public string $nombre_estado,
        public string $clave_estado
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_estado: isset($data['id_estado']) ? (int) $data['id_estado'] : null,
            nombre_estado: trim((string) ($data['nombre_estado'] ?? '')),
            clave_estado: mb_strtoupper(trim((string) ($data['clave_estado'] ?? '')))
        );
    }
}
