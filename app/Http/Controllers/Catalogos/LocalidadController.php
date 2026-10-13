<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\LocalidadDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\LocalidadService;
use App\Validators\Catalogos\LocalidadValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller del catálogo Localidad.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de LocalidadValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a LocalidadService.
 */
final class LocalidadController extends Controller
{
    public function __construct(
        private readonly LocalidadService $service,
        private readonly LocalidadValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Localidades obtenidas correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra una nueva localidad.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = LocalidadDTO::fromArray($datosValidados);
        $localidad = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Localidad registrada correctamente.',
            'data' => $localidad,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idLocalidad): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_localidad' => $idLocalidad,
        ]);

        $dto = LocalidadDTO::fromArray($datosValidados);
        $localidad = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Localidad actualizada correctamente.',
            'data' => $localidad,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idLocalidad): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_localidad' => $idLocalidad,
        ]);

        $localidad = $this->service->cambiarEstatus(
            (int) $datosValidados['id_localidad'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $localidad->activo ? 'Localidad activada correctamente.' : 'Localidad desactivada correctamente.',
            'data' => $localidad,
        ]);
    }
}
