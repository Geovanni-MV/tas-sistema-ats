<?php

namespace App\DTO\Seguridad;

/**
 * DTO inmutable que representa los datos transferidos de la entidad seguridad.menu
 */
final readonly class MenuDTO
{
    public function __construct(
        public ?int $id_menu,
        public ?int $id_menu_padre,
        public bool $es_padre,
        public bool $es_colapsable,
        public string $nombre_menu,
        public ?string $icono_clase,
        public ?string $descripcion,
        public ?int $orden,
        public ?string $ruta
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_menu: isset($data['id_menu']) ? (int) $data['id_menu'] : null,
            id_menu_padre: isset($data['id_menu_padre']) && $data['id_menu_padre'] !== ''
                ? (int) $data['id_menu_padre'] : null,
            es_padre: array_key_exists('es_padre', $data) ? (bool) $data['es_padre'] : false,
            es_colapsable: array_key_exists('es_colapsable', $data) ? (bool) $data['es_colapsable'] : false,
            nombre_menu: trim((string) ($data['nombre_menu'] ?? '')),
            icono_clase: self::textoONulo($data['icono_clase'] ?? null),
            descripcion: self::textoONulo($data['descripcion'] ?? null),
            orden: isset($data['orden']) && $data['orden'] !== '' ? (int) $data['orden'] : null,
            ruta: self::textoONulo($data['ruta'] ?? null)
        );
    }

    private static function textoONulo(mixed $valor): ?string
    {
        $texto = trim((string) ($valor ?? ''));

        return $texto !== '' ? $texto : null;
    }
}
