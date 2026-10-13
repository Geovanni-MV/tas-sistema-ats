<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\EstadoCivilDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\EstadoCivilService;
use App\Validators\Catalogos\EstadoCivilValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller del catálogo Estado civil.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de EstadoCivilValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a EstadoCivilService.
 */
final class EstadoCivilController extends Controller
{
    public function __construct(
        private readonly EstadoCivilService $service,
        private readonly EstadoCivilValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Estados civiles obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra un nuevo estado civil.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = EstadoCivilDTO::fromArray($datosValidados);
        $estadoCivil = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Estado civil registrado correctamente.',
            'data' => $estadoCivil,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idEstadoCivil): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_estado_civil' => $idEstadoCivil,
        ]);

        $dto = EstadoCivilDTO::fromArray($datosValidados);
        $estadoCivil = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Estado civil actualizado correctamente.',
            'data' => $estadoCivil,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idEstadoCivil): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_estado_civil' => $idEstadoCivil,
        ]);

        $estadoCivil = $this->service->cambiarEstatus(
            (int) $datosValidados['id_estado_civil'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $estadoCivil->activo ? 'Estado civil activado correctamente.' : 'Estado civil desactivado correctamente.',
            'data' => $estadoCivil,
        ]);
    }
}
