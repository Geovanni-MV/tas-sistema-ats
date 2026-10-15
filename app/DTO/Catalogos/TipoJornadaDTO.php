<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Tipo de jornada.
 */
final readonly class TipoJornadaDTO
{
    public function __construct(
        public ?int $id_tipo_jornada,
        public string $nombre_tipo_jornada,
        public ?string $descripcion,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_tipo_jornada: isset($data['id_tipo_jornada']) ? (int) $data['id_tipo_jornada'] : null,
            nombre_tipo_jornada: trim((string) ($data['nombre_tipo_jornada'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null,
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
