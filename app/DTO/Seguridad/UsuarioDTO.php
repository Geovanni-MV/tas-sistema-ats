<?php

namespace App\DTO\Seguridad;

/**
 * DTO inmutable que representa los datos transferidos de la entidad seguridad.usuarios
 */
final readonly class UsuarioDTO
{
    public function __construct(
        public ?int $id_usuario,
        public string $nombres,
        public string $apellidos,
        public string $telefono,
        public string $correo_electronico,
        public ?int $id_empresa,
        public ?int $id_rol,
        public ?string $clave
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id_usuario: isset($data['id_usuario']) ? (int) $data['id_usuario'] : null,
            nombres: trim((string) ($data['nombres'] ?? '')),
            apellidos: trim((string) ($data['apellidos'] ?? '')),
            telefono: trim((string) ($data['telefono'] ?? '')),
            correo_electronico: mb_strtolower(trim((string) ($data['correo_electronico'] ?? ''))),
            id_empresa: isset($data['id_empresa']) && $data['id_empresa'] !== '' ? (int) $data['id_empresa'] : null,
            id_rol: isset($data['id_rol']) && $data['id_rol'] !== '' ? (int) $data['id_rol'] : null,
            clave: isset($data['clave']) && (string) $data['clave'] !== '' ? (string) $data['clave'] : null
        );
    }
}
