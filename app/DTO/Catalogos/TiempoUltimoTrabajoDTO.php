<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Tiempo del último trabajo.
 */
final readonly class TiempoUltimoTrabajoDTO
{
    public function __construct(
        public ?int $idTiempoUltimoTrabajo,
        public string $nombreTiempoUltimoTrabajo,
        public ?string $descripcion,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idTiempoUltimoTrabajo: isset($data['id_tiempo_ultimo_trabajo']) ? (int) $data['id_tiempo_ultimo_trabajo'] : null,
            nombreTiempoUltimoTrabajo: trim((string) ($data['nombre_tiempo_ultimo_trabajo'] ?? '')),
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null,
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
