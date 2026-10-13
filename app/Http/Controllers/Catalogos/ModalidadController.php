<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\ModalidadDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\ModalidadService;
use App\Validators\Catalogos\ModalidadValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller del catálogo Modalidad.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de ModalidadValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a ModalidadService.
 */
final class ModalidadController extends Controller
{
    public function __construct(
        private readonly ModalidadService $service,
        private readonly ModalidadValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Modalidades obtenidas correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra una nueva modalidad.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = ModalidadDTO::fromArray($datosValidados);
        $modalidad = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Modalidad registrada correctamente.',
            'data' => $modalidad,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idModalidad): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_modalidad' => $idModalidad,
        ]);

        $dto = ModalidadDTO::fromArray($datosValidados);
        $modalidad = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Modalidad actualizada correctamente.',
            'data' => $modalidad,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idModalidad): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_modalidad' => $idModalidad,
        ]);

        $modalidad = $this->service->cambiarEstatus(
            (int) $datosValidados['id_modalidad'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $modalidad->activo ? 'Modalidad activada correctamente.' : 'Modalidad desactivada correctamente.',
            'data' => $modalidad,
        ]);
    }
}
