<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Tiempo del último trabajo.
 */
final readonly class TiempoUltimoTrabajoDTO
{
    public function __construct(
        public ?int $id_tiempo_ultimo_trabajo,
        public string $nombre_tiempo_ultimo_trabajo,
        public ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_tiempo_ultimo_trabajo: isset($data['id_tiempo_ultimo_trabajo']) ? (int) $data['id_tiempo_ultimo_trabajo'] : null,
            nombre_tiempo_ultimo_trabajo: trim((string) ($data['nombre_tiempo_ultimo_trabajo'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null
        );
    }
}
