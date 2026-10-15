<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Escolaridad.
 */
final readonly class EscolaridadDTO
{
    public function __construct(
        public ?int $id_escolaridad,
        public string $nombre_escolaridad,
        public string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_escolaridad: isset($data['id_escolaridad']) ? (int) $data['id_escolaridad'] : null,
            nombre_escolaridad: trim((string) ($data['nombre_escolaridad'] ?? '')),
            descripcion: trim((string) ($data['descripcion'] ?? ''))
        );
    }
}
