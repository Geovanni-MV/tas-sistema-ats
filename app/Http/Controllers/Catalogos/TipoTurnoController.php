<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\TipoTurnoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\TipoTurnoService;
use App\Validators\Catalogos\TipoTurnoValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller del catálogo Tipo de turno.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de TipoTurnoValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a TipoTurnoService.
 */
final class TipoTurnoController extends Controller
{
    public function __construct(
        private readonly TipoTurnoService $service,
        private readonly TipoTurnoValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tipos de turno obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra un nuevo tipo de turno.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = TipoTurnoDTO::fromArray($datosValidados);
        $tipoTurno = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tipo de turno registrado correctamente.',
            'data' => $tipoTurno,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idTipoTurno): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_tipo_turno' => $idTipoTurno,
        ]);

        $dto = TipoTurnoDTO::fromArray($datosValidados);
        $tipoTurno = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tipo de turno actualizado correctamente.',
            'data' => $tipoTurno,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idTipoTurno): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_tipo_turno' => $idTipoTurno,
        ]);

        $tipoTurno = $this->service->cambiarEstatus(
            (int) $datosValidados['id_tipo_turno'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $tipoTurno->activo ? 'Tipo de turno activado correctamente.' : 'Tipo de turno desactivado correctamente.',
            'data' => $tipoTurno,
        ]);
    }
}
