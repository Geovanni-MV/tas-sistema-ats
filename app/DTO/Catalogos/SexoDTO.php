<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Sexo.
 */
final readonly class SexoDTO
{
    public function __construct(
        public ?int $id_sexo,
        public string $nombre_sexo,
        public ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_sexo: isset($data['id_sexo']) ? (int) $data['id_sexo'] : null,
            nombre_sexo: trim((string) ($data['nombre_sexo'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null
        );
    }
}
