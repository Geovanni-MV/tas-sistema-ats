<?php

namespace App\Services\Seguridad;

use App\DTO\Seguridad\LoginDTO;
use App\Models\Seguridad\UsuarioModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

final class AuthService
{
    /**
     * Intenta autenticar al usuario.
     */
    public function login(
        LoginDTO $dto,
        Request $request
    ): UsuarioModel {

        $correo = mb_strtolower(
            trim($dto->correo_electronico)
        );

        $usuario = UsuarioModel::query()
            ->where('correo_electronico', $correo)
            ->first();

        if (!$usuario) {
            throw new RuntimeException(
                'Credenciales incorrectas.'
            );
        }

        if (!Hash::check(
            $dto->clave,
            $usuario->clave_hash
        )) {
            throw new RuntimeException(
                'Credenciales incorrectas.'
            );
        }

        if (!$usuario->activo) {
            throw new RuntimeException(
                'El usuario se encuentra inactivo.'
            );
        }
        Auth::login($usuario);

        $request->session()->regenerate();

        return $usuario;
    }

    /**
     * Cierra la sesión del usuario autenticado.
     */
    public function logout(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }

    /**
     * Obtiene el usuario actualmente autenticado.
     */
    public function usuarioAutenticado(): ?UsuarioModel
    {
        return Auth::user();
    }
}