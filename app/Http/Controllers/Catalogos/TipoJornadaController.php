<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\TipoJornadaDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\TipoJornadaService;
use App\Validators\Catalogos\TipoJornadaValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller del catálogo Tipo de jornada.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de TipoJornadaValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a TipoJornadaService.
 */
final class TipoJornadaController extends Controller
{
    public function __construct(
        private readonly TipoJornadaService $service,
        private readonly TipoJornadaValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tipos de jornada obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra un nuevo tipo de jornada.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = TipoJornadaDTO::fromArray($datosValidados);
        $tipoJornada = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tipo de jornada registrado correctamente.',
            'data' => $tipoJornada,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idTipoJornada): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_tipo_jornada' => $idTipoJornada,
        ]);

        $dto = TipoJornadaDTO::fromArray($datosValidados);
        $tipoJornada = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tipo de jornada actualizado correctamente.',
            'data' => $tipoJornada,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idTipoJornada): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_tipo_jornada' => $idTipoJornada,
        ]);

        $tipoJornada = $this->service->cambiarEstatus(
            (int) $datosValidados['id_tipo_jornada'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $tipoJornada->activo ? 'Tipo de jornada activado correctamente.' : 'Tipo de jornada desactivado correctamente.',
            'data' => $tipoJornada,
        ]);
    }
}
