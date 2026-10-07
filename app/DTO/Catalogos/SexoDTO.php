<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Sexo.
 */
final readonly class SexoDTO
{
    public function __construct(
        public ?int $idSexo,
        public string $nombreSexo,
        public ?string $descripcion,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idSexo: isset($data['id_sexo']) ? (int) $data['id_sexo'] : null,
            nombreSexo: trim((string) ($data['nombre_sexo'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null,
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
