<?php

namespace App\Http\Controllers\Seguridad;

use App\DTO\Seguridad\PermisoDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\PermisoService;
use App\Validators\Seguridad\PermisoValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Esquema: seguridad
 * Entidad: permisos
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class PermisoController extends Controller
{
    public function __construct(
        private readonly PermisoService $service,
        private readonly PermisoValidator $validator
    ) {}

    /**
     * Devuelve todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Permisos obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Genera un nuevo registro.
     */
    public function crear(Request $request): JsonResponse
    {
        $datos_validados = $this->validator->validateCreate($request->all());

        $dto = PermisoDTO::fromArray($datos_validados);
        $registro = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Permiso registrado correctamente.',
            'data' => $registro,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $id_permiso): JsonResponse
    {
        $datos_validados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_permiso' => $id_permiso,
        ]);

        $dto = PermisoDTO::fromArray($datos_validados);
        $registro = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Permiso actualizado correctamente.',
            'data' => $registro,
        ]);
    }

    /**
     * Activa o desactiva un registro (alterna el valor actual).
     */
    public function cambiarEstatus(int $id_permiso): JsonResponse
    {
        $datos_validados = $this->validator->validateStatus(['id_permiso' => $id_permiso]);

        $registro = $this->service->cambiarEstatus((int) $datos_validados['id_permiso']);

        return response()->json([
            'estatus' => true,
            'mensaje' => $registro->activo
                ? 'Permiso activado correctamente.'
                : 'Permiso desactivado correctamente.',
            'data' => $registro,
        ]);
    }
}
