<?php

namespace App\Services\Seguridad;

use App\DTO\Seguridad\UsuarioDTO;
use App\Models\Seguridad\UsuarioModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Contiene la lógica de negocio para la entidad seguridad.usuarios
 */
final class UsuarioService
{
    private const RELACIONES = [
        'rol:id_rol,nombre_rol',
        'empresa:id_empresa,nombre_empresa',
    ];

    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return UsuarioModel::query()
            ->with(self::RELACIONES)
            ->orderBy('id_usuario')
            ->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el controlador.
     * La clave se almacena siempre como hash.
     */
    public function crear(UsuarioDTO $dto): UsuarioModel
    {
        $usuario = UsuarioModel::query()->create([
            'nombres' => $dto->nombres,
            'apellidos' => $dto->apellidos,
            'telefono' => $dto->telefono,
            'correo_electronico' => $dto->correo_electronico,
            'id_empresa' => $dto->id_empresa,
            'id_rol' => $dto->id_rol,
            'clave_hash' => Hash::make($dto->clave),
        ]);

        return $usuario->refresh()->load(self::RELACIONES);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * Ni "activo" (cambiarEstatus) ni la clave (cambiarClave) se modifican aquí.
     */
    public function actualizar(UsuarioDTO $dto): UsuarioModel
    {
        $usuario = UsuarioModel::query()->findOrFail($dto->id_usuario);

        $usuario->fill([
            'nombres' => $dto->nombres,
            'apellidos' => $dto->apellidos,
            'telefono' => $dto->telefono,
            'correo_electronico' => $dto->correo_electronico,
            'id_empresa' => $dto->id_empresa,
            'id_rol' => $dto->id_rol,
            'fecha_actualizacion' => now(),
        ])->save();

        return $usuario->refresh()->load(self::RELACIONES);
    }

    /**
     * Cambia únicamente la clave del registro (se almacena como hash).
     */
    public function cambiarClave(int $id_usuario, string $clave): UsuarioModel
    {
        $usuario = UsuarioModel::query()->findOrFail($id_usuario);

        $usuario->fill([
            'clave_hash' => Hash::make($clave),
            'fecha_actualizacion' => now(),
        ])->save();

        return $usuario->refresh()->load(self::RELACIONES);
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_usuario): UsuarioModel
    {
        $usuario = UsuarioModel::query()->findOrFail($id_usuario);

        $usuario->fill([
            'activo' => ! $usuario->activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $usuario->refresh()->load(self::RELACIONES);
    }
}
