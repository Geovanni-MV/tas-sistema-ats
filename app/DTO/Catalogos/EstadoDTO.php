<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Estado.
 */
final readonly class EstadoDTO
{
    public function __construct(
        public ?int $idEstado,
        public string $nombreEstado,
        public string $claveEstado,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idEstado: isset($data['id_estado']) ? (int) $data['id_estado'] : null,
            nombreEstado: trim((string) ($data['nombre_estado'] ?? '')),
            claveEstado: mb_strtoupper(trim((string) ($data['clave_estado'] ?? ''))),
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
