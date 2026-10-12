<?php

namespace App\Http\Controllers\Seguridad;

use App\DTO\Seguridad\PermisoRolDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\PermisoRolService;
use App\Validators\Seguridad\PermisoRolValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Esquema: seguridad
 * Entidad: permisos_roles
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class PermisoRolController extends Controller
{
    public function __construct(
        private readonly PermisoRolService $service,
        private readonly PermisoRolValidator $validator
    ) {}

    /**
     * Devuelve todos los registros.
     * Filtro opcional por query string: ?id_rol=1
     */
    public function obtenerDatos(Request $request): JsonResponse
    {
        $filtros = $this->validator->validateFiltros($request->query());

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Asignaciones obtenidas correctamente.',
            'data' => $this->service->listar(
                isset($filtros['id_rol']) ? (int) $filtros['id_rol'] : null
            ),
        ]);
    }

    /**
     * Genera un nuevo registro.
     */
    public function crear(Request $request): JsonResponse
    {
        $datos_validados = $this->validator->validateCreate($request->all());

        $dto = PermisoRolDTO::fromArray($datos_validados);
        $registro = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Asignación registrada correctamente.',
            'data' => $registro,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $id_permiso_rol): JsonResponse
    {
        $datos_validados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_permiso_rol' => $id_permiso_rol,
        ]);

        $dto = PermisoRolDTO::fromArray($datos_validados);
        $registro = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Asignación actualizada correctamente.',
            'data' => $registro,
        ]);
    }

    /**
     * Activa o desactiva un registro (alterna el valor actual).
     */
    public function cambiarEstatus(int $id_permiso_rol): JsonResponse
    {
        $datos_validados = $this->validator->validateStatus(['id_permiso_rol' => $id_permiso_rol]);

        $registro = $this->service->cambiarEstatus((int) $datos_validados['id_permiso_rol']);

        return response()->json([
            'estatus' => true,
            'mensaje' => $registro->activo
                ? 'Asignación activada correctamente.'
                : 'Asignación desactivada correctamente.',
            'data' => $registro,
        ]);
    }
}
