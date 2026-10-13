<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Estado civil.
 */
final readonly class EstadoCivilDTO
{
    public function __construct(
        public ?int $idEstadoCivil,
        public string $nombreEstadoCivil,
        public ?string $descripcion,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idEstadoCivil: isset($data['id_estado_civil']) ? (int) $data['id_estado_civil'] : null,
            nombreEstadoCivil: trim((string) ($data['nombre_estado_civil'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null,
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
