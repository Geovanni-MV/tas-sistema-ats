<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\EscolaridadDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\EscolaridadService;
use App\Validators\Catalogos\EscolaridadValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller del catálogo Escolaridad.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de EscolaridadValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a EscolaridadService.
 */
final class EscolaridadController extends Controller
{
    public function __construct(
        private readonly EscolaridadService $service,
        private readonly EscolaridadValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Escolaridades obtenidas correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra una nueva escolaridad.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = EscolaridadDTO::fromArray($datosValidados);
        $escolaridad = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Escolaridad registrada correctamente.',
            'data' => $escolaridad,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idEscolaridad): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_escolaridad' => $idEscolaridad,
        ]);

        $dto = EscolaridadDTO::fromArray($datosValidados);
        $escolaridad = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Escolaridad actualizada correctamente.',
            'data' => $escolaridad,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idEscolaridad): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_escolaridad' => $idEscolaridad,
        ]);

        $escolaridad = $this->service->cambiarEstatus(
            (int) $datosValidados['id_escolaridad'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $escolaridad->activo ? 'Escolaridad activada correctamente.' : 'Escolaridad desactivada correctamente.',
            'data' => $escolaridad,
        ]);
    }
}
