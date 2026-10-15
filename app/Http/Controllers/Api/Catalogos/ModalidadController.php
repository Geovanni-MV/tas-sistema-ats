<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\ModalidadDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\ModalidadService;
use App\Validators\Catalogos\ModalidadValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

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
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Modalidades obtenidas correctamente.',
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
     * Registra una nueva modalidad.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = ModalidadDTO::fromArray($datosValidados);
            $modalidad = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Modalidad registrada correctamente.',
                'data' => $modalidad,
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
    public function actualizar(Request $request, int $id_modalidad): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_modalidad' => $id_modalidad,
            ]);

            $dto = ModalidadDTO::fromArray($datosValidados);
            $modalidad = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Modalidad actualizada correctamente.',
                'data' => $modalidad,
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
    public function cambiarEstatus(int $id_modalidad): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus([
                'id_modalidad' => $id_modalidad
            ]);

            $modalidad = $this->service->cambiarEstatus(
                (int) $datosValidados['id_modalidad']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => $modalidad->activo ? 'Modalidad activada correctamente.' : 'Modalidad desactivada correctamente.',
                'data' => $modalidad,
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
