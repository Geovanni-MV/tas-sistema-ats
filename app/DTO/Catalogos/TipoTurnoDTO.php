<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Tipo de turno.
 */
final readonly class TipoTurnoDTO
{
    public function __construct(
        public ?int $id_tipo_turno,
        public string $nombre_tipo_turno,
        public ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_tipo_turno: isset($data['id_tipo_turno']) ? (int) $data['id_tipo_turno'] : null,
            nombre_tipo_turno: trim((string) ($data['nombre_tipo_turno'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null
        );
    }
}
