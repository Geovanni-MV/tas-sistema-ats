<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\DocumentoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\DocumentoService;
use App\Validators\Catalogos\DocumentoValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

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
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Documentos obtenidos correctamente.',
                'data' => $this->service->listar(),
            ]);
        }catch(Throwable $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'Ocurrió un error interno al obtener los datos.'
            ]);
        }
        
    }

    /**
     * Registra un nuevo documento.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = DocumentoDTO::fromArray($datosValidados);
            $documento = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Documento registrado correctamente.',
                'data' => $documento,
            ], 201);
        }catch(ValidationException $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'Los datos enviados no son válidos',
                'errores'=> $e->errors()
            ], 422);

        }catch(QueryException $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'No fue posible generar el registro.'
            ]);

        }catch(Throwable $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'Ocurrió un error interno.'
            ], 500);
        }
        
    }

    /**
     * Actualiza un registro existente.
     */
    public function actualizar(Request $request, int $id_documento): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_documento' => $id_documento,
            ]);

            $dto = DocumentoDTO::fromArray($datosValidados);
            $documento = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Documento actualizado correctamente.',
                'data' => $documento,
            ]);
        }catch(ValidationException $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'Los datos enviados no son válidos',
                'errores'=> $e->errors()
            ], 422);
        
        }catch(ModelNotFoundException $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'El registro no existe.'
            ]);
        }catch(QueryException $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'No fue posible actualizar el registro.'
            ]);
        }catch(Throwable $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'Ocurrió un error interno.'
            ], 500);
        }
        
    }

    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(Request $request, int $id_documento): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus([
                'id_documento' => $id_documento
            ]);

            $documento = $this->service->cambiarEstatus(
                (int) $datosValidados['id_documento']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => $documento->activo ? 'Documento activado correctamente.' : 'Documento desactivado correctamente.',
                'data' => $documento,
            ]);
        }catch(ModelNotFoundException $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'No fue posible cambiar el estatus'
            ], 500);
        }catch(QueryException $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'No fue posible cambiar el estatus'
            ], 500);
        }catch(Throwable $e){
            return response()->json([
                'estatus'=>false,
                'mensaje'=>'Ocurrió un error interno.'
            ], 500);
        }
        
    }
}
