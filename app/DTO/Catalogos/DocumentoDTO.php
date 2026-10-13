<?php

namespace App\DTO\Catalogos;

/**
 * DTO inmutable que representa los datos transferidos del catálogo Documento.
 */
final readonly class DocumentoDTO
{
    public function __construct(
        public ?int $idDocumento,
        public string $nombreDocumento,
        public bool $requiereAmbosLados,
        public bool $permiteCapturaTexto,
        public int $maximoArchivos,
        public ?string $descripcion,
        public bool $activo = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            idDocumento: isset($data['id_documento']) ? (int) $data['id_documento'] : null,
            nombreDocumento: trim((string) ($data['nombre_documento'] ?? '')),
            requiereAmbosLados: array_key_exists('requiere_ambos_lados', $data) ? (bool) $data['requiere_ambos_lados'] : false,
            permiteCapturaTexto: array_key_exists('permite_captura_texto', $data) ? (bool) $data['permite_captura_texto'] : false,
            maximoArchivos: isset($data['maximo_archivos']) ? (int) $data['maximo_archivos'] : 1,
            descripcion: isset($data['descripcion']) && trim((string) $data['descripcion']) !== '' ? trim((string) $data['descripcion']) : null,
            activo: array_key_exists('activo', $data) ? (bool) $data['activo'] : true,
        );
    }
}
