<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Escolaridad.
 */
final readonly class EscolaridadDTO
{
    public function __construct(
        public ?int $idEscolaridad,
        public string $nombreEscolaridad,
        public string $descripcion,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idEscolaridad: isset($data['id_escolaridad']) ? (int) $data['id_escolaridad'] : null,
            nombreEscolaridad: trim((string) ($data['nombre_escolaridad'] ?? '')),
            descripcion: trim((string) ($data['descripcion'] ?? '')),
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
