<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\SexoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\SexoService;
use App\Validators\Catalogos\SexoValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller del catálogo Sexo.
 *
 * Responsabilidades:
 * - Entregar la vista Blade del módulo.
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de SexoValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a SexoService.
 */
final class SexoController extends Controller
{
    public function __construct(
        private readonly SexoService $service,
        private readonly SexoValidator $validator,
    ) {}

    /**
     * Renderiza únicamente la vista del catálogo.
     * Los datos se obtienen posteriormente mediante la API REST.
     */
    public function vista(): View
    {
        return view('catalogos.sexos.index');
    }

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Sexos obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra un nuevo sexo.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = SexoDTO::fromArray($datosValidados);
        $sexo = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Sexo registrado correctamente.',
            'data' => $sexo,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idSexo): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_sexo' => $idSexo,
        ]);

        $dto = SexoDTO::fromArray($datosValidados);
        $sexo = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Sexo actualizado correctamente.',
            'data' => $sexo,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idSexo): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_sexo' => $idSexo,
        ]);

        $sexo = $this->service->cambiarEstatus(
            (int) $datosValidados['id_sexo'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $sexo->activo ? 'Sexo activado correctamente.' : 'Sexo desactivado correctamente.',
            'data' => $sexo,
        ]);
    }
}
