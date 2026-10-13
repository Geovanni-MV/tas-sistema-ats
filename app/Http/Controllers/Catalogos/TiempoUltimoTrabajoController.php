<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\TiempoUltimoTrabajoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\TiempoUltimoTrabajoService;
use App\Validators\Catalogos\TiempoUltimoTrabajoValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller del catálogo Tiempo del último trabajo.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de TiempoUltimoTrabajoValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a TiempoUltimoTrabajoService.
 */
final class TiempoUltimoTrabajoController extends Controller
{
    public function __construct(
        private readonly TiempoUltimoTrabajoService $service,
        private readonly TiempoUltimoTrabajoValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tiempos del último trabajo obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra un nuevo tiempo del último trabajo.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = TiempoUltimoTrabajoDTO::fromArray($datosValidados);
        $tiempoUltimoTrabajo = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tiempo del último trabajo registrado correctamente.',
            'data' => $tiempoUltimoTrabajo,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idTiempoUltimoTrabajo): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_tiempo_ultimo_trabajo' => $idTiempoUltimoTrabajo,
        ]);

        $dto = TiempoUltimoTrabajoDTO::fromArray($datosValidados);
        $tiempoUltimoTrabajo = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Tiempo del último trabajo actualizado correctamente.',
            'data' => $tiempoUltimoTrabajo,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idTiempoUltimoTrabajo): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_tiempo_ultimo_trabajo' => $idTiempoUltimoTrabajo,
        ]);

        $tiempoUltimoTrabajo = $this->service->cambiarEstatus(
            (int) $datosValidados['id_tiempo_ultimo_trabajo'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $tiempoUltimoTrabajo->activo ? 'Tiempo del último trabajo activado correctamente.' : 'Tiempo del último trabajo desactivado correctamente.',
            'data' => $tiempoUltimoTrabajo,
        ]);
    }
}
