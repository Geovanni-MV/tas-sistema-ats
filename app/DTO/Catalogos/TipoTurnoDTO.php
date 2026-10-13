<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Tipo de turno.
 */
final readonly class TipoTurnoDTO
{
    public function __construct(
        public ?int $idTipoTurno,
        public string $nombreTipoTurno,
        public ?string $descripcion,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idTipoTurno: isset($data['id_tipo_turno']) ? (int) $data['id_tipo_turno'] : null,
            nombreTipoTurno: trim((string) ($data['nombre_tipo_turno'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null,
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
