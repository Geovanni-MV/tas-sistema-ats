<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Modalidad.
 */
final readonly class ModalidadDTO
{
    public function __construct(
        public ?int $id_modalidad,
        public string $nombre_modalidad,
        public ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_modalidad: isset($data['id_modalidad']) ? (int) $data['id_modalidad'] : null,
            nombre_modalidad: trim((string) ($data['nombre_modalidad'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null
        );
    }
}
