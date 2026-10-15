<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\LocalidadDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\LocalidadService;
use App\Validators\Catalogos\LocalidadValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

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
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Localidades obtenidas correctamente.',
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
     * Registra una nueva localidad.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = LocalidadDTO::fromArray($datosValidados);
            $localidad = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Localidad registrada correctamente.',
                'data' => $localidad,
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
    public function actualizar(Request $request, int $id_localidad): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_localidad' => $id_localidad,
            ]);

            $dto = LocalidadDTO::fromArray($datosValidados);
            $localidad = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Localidad actualizada correctamente.',
                'data' => $localidad,
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
    public function cambiarEstatus(int $id_localidad): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus([
                'id_localidad' => $id_localidad,
            ]);

            $localidad = $this->service->cambiarEstatus(
                (int) $datosValidados['id_localidad']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => $localidad->activo ? 'Localidad activada correctamente.' : 'Localidad desactivada correctamente.',
                'data' => $localidad,
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
