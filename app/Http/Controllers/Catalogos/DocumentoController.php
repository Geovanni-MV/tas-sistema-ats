<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\DocumentoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\DocumentoService;
use App\Validators\Catalogos\DocumentoValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller del catálogo Documento.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de DocumentoValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a DocumentoService.
 */
final class DocumentoController extends Controller
{
    public function __construct(
        private readonly DocumentoService $service,
        private readonly DocumentoValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Documentos obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Registra un nuevo documento.
     */
    public function crear(Request $request): JsonResponse
    {
        $datosValidados = $this->validator->validateCreate($request->all());
        $dto = DocumentoDTO::fromArray($datosValidados);
        $documento = $this->service->crear($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Documento registrado correctamente.',
            'data' => $documento,
        ], 201);
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $idDocumento): JsonResponse
    {
        $datosValidados = $this->validator->validateUpdate([
            ...$request->all(),
            'id_documento' => $idDocumento,
        ]);

        $dto = DocumentoDTO::fromArray($datosValidados);
        $documento = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Documento actualizado correctamente.',
            'data' => $documento,
        ]);
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $idDocumento): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            ...$request->all(),
            'id_documento' => $idDocumento,
        ]);

        $documento = $this->service->cambiarEstatus(
            (int) $datosValidados['id_documento'],
            (bool) $datosValidados['activo'],
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $documento->activo ? 'Documento activado correctamente.' : 'Documento desactivado correctamente.',
            'data' => $documento,
        ]);
    }
}
