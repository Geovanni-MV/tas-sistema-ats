<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\EstadoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\EstadoService;
use App\Validators\Catalogos\EstadoValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller del catálogo Estado.
 *
 * Responsabilidades:
 * - Entregar la vista Blade.
 * - Recibir peticiones REST.
 * - Delegar validación a EstadoValidator.
 * - Construir el DTO.
 * - Delegar la lógica a EstadoService.
 */
final class EstadoController extends Controller
{
    public function __construct(
        private readonly EstadoService $service,
        private readonly EstadoValidator $validator,
    ) {}

    /**
     * Renderiza la vista del catálogo.
     */
    public function vista(): View
    {
        return view('catalogos.estados.index');
    }

    /**
     * Obtiene todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Estados obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra un nuevo estado.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = EstadoDTO::fromArray($datosValidados);
        $estado = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Estado registrado correctamente.',
            'data' => $estado,
        ], 201);
    }

    /**
     * Actualiza un estado existente.
     */
    public function actualizar(Request $request, int $idEstado): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_estado' => $idEstado,
        ]);

        $dto = EstadoDTO::fromArray($datosValidados);
        $estado = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Estado actualizado correctamente.',
            'data' => $estado,
        ]);
    }

    /**
     * Activa o desactiva un estado.
     */
    public function cambiarEstatus(
        Request $request,
        int $idEstado,
    ): JsonResponse {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_estado' => $idEstado,
        ]);

        $estado = $this->service->cambiarEstatus(
            (int) $datosValidados['id_estado'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $estado->activo
                ? 'Estado activado correctamente.'
                : 'Estado desactivado correctamente.',
            'data' => $estado,
        ]);
    }
}
