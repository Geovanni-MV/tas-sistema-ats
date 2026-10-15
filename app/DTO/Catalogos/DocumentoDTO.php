<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Documento.
 */
final readonly class DocumentoDTO
{
    public function __construct(
        public ?int $id_documento,
        public string $nombre_documento,
        public bool $requiere_ambos_lados,
        public int $maximo_archivos,
        public ?string $descripcion
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_documento: isset($data['id_documento']) ? (int) $data['id_documento'] : null,
            nombre_documento: trim((string) ($data['nombre_documento'] ?? '')),
            requiere_ambos_lados: array_key_exists('requiere_ambos_lados', $data) ? (bool) $data['requiere_ambos_lados'] : false,
            maximo_archivos: isset($data['maximo_archivos']) ? (int) $data['maximo_archivos'] : 1,
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null
        );
    }
}
