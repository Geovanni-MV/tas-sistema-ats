<?php

namespace App\Http\Controllers\Seguridad;

use App\DTO\Seguridad\RolDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\RolService;
use App\Validators\Seguridad\RolValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Esquema: seguridad
 * Entidad: roles
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class RolController extends Controller
{
    public function __construct(
        private readonly RolService $service,
        private readonly RolValidator $validator
    ) {}

    /**
     * Devuelve todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Roles obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Genera un nuevo registro.
     */
    public function crear(Request $request): JsonResponse
    {
        $datos_validados = $this->validator->validateCreate($request->all());

        $dto = RolDTO::fromArray($datos_validados);
        $registro = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Rol registrado correctamente.',
            'data' => $registro,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $id_rol): JsonResponse
    {
        $datos_validados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_rol' => $id_rol,
        ]);

        $dto = RolDTO::fromArray($datos_validados);
        $registro = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Rol actualizado correctamente.',
            'data' => $registro,
        ]);
    }

    /**
     * Activa o desactiva un registro (alterna el valor actual).
     */
    public function cambiarEstatus(int $id_rol): JsonResponse
    {
        $datos_validados = $this->validator->validateStatus(['id_rol' => $id_rol]);

        $registro = $this->service->cambiarEstatus((int) $datos_validados['id_rol']);

        return response()->json([
            'estatus' => true,
            'mensaje' => $registro->activo
                ? 'Rol activado correctamente.'
                : 'Rol desactivado correctamente.',
            'data' => $registro,
        ]);
    }
}
