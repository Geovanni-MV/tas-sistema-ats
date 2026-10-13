<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Localidad.
 */
final readonly class LocalidadDTO
{
    public function __construct(
        public ?int $idLocalidad,
        public int $idEstado,
        public string $nombreLocalidad,
        public string $codigoPostal,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idLocalidad: isset($data['id_localidad']) ? (int) $data['id_localidad'] : null,
            idEstado: (int) ($data['id_estado'] ?? 0),
            nombreLocalidad: trim((string) ($data['nombre_localidad'] ?? '')),
            codigoPostal: trim((string) ($data['codigo_postal'] ?? '')),
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
