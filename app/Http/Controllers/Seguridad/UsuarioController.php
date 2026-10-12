<?php

namespace App\Http\Controllers\Seguridad;

use App\DTO\Seguridad\UsuarioDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\UsuarioService;
use App\Validators\Seguridad\UsuarioValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Esquema: seguridad
 * Entidad: usuarios
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class UsuarioController extends Controller
{
    public function __construct(
        private readonly UsuarioService $service,
        private readonly UsuarioValidator $validator
    ) {}

    /**
     * Devuelve todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Usuarios obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Genera un nuevo registro.
     */
    public function crear(Request $request): JsonResponse
    {
        $datos_validados = $this->validator->validateCreate($request->all());

        $dto = UsuarioDTO::fromArray($datos_validados);
        $usuario = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Usuario registrado correctamente.',
            'data' => $usuario,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $id_usuario): JsonResponse
    {
        $datos_validados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_usuario' => $id_usuario,
        ]);

        $dto = UsuarioDTO::fromArray($datos_validados);
        $usuario = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Usuario actualizado correctamente.',
            'data' => $usuario,
        ]);
    }

    /**
     * Actualiza únicamente la clave de un registro existente.
     */
    public function cambiarClave(Request $request, int $id_usuario): JsonResponse
    {
        $datos_validados = $this->validator->validateClave([
            ...$request->all(),
            'id_usuario' => $id_usuario,
        ]);

        $usuario = $this->service->cambiarClave(
            (int) $datos_validados['id_usuario'],
            (string) $datos_validados['clave']
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Clave actualizada correctamente.',
            'data' => $usuario,
        ]);
    }

    /**
     * Activa o desactiva un registro (alterna el valor actual).
     */
    public function cambiarEstatus(int $id_usuario): JsonResponse
    {
        $datos_validados = $this->validator->validateStatus(['id_usuario' => $id_usuario]);

        $usuario = $this->service->cambiarEstatus((int) $datos_validados['id_usuario']);

        return response()->json([
            'estatus' => true,
            'mensaje' => $usuario->activo
                ? 'Usuario activado correctamente.'
                : 'Usuario desactivado correctamente.',
            'data' => $usuario,
        ]);
    }
}
