<?php
namespace App\DTO\Seguridad;
class loginDTO
{
    public function __construct(
        public readonly string $correo_electronico,
        public readonly string $clave)
    {}
}
?>