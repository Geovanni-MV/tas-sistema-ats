<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Estado civil.
 */
final readonly class EstadoCivilDTO
{
    public function __construct(
        public ?int $id_estado_civil,
        public string $nombre_estado_civil,
        public ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_estado_civil: isset($data['id_estado_civil']) ? (int) $data['id_estado_civil'] : null,
            nombre_estado_civil: trim((string) ($data['nombre_estado_civil'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null
        );
    }
}
