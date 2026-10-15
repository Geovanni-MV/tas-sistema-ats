<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\EscolaridadDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\EscolaridadService;
use App\Validators\Catalogos\EscolaridadValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Controller del catálogo Escolaridad.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de EscolaridadValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a EscolaridadService.
 */
final class EscolaridadController extends Controller
{
    public function __construct(
        private readonly EscolaridadService $service,
        private readonly EscolaridadValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Escolaridades obtenidas correctamente.',
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
     * Registra una nueva escolaridad.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = EscolaridadDTO::fromArray($datosValidados);
            $escolaridad = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Escolaridad registrada correctamente.',
                'data' => $escolaridad,
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
    public function actualizar(Request $request, int $id_escolaridad): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_escolaridad' => $id_escolaridad,
            ]);

            $dto = EscolaridadDTO::fromArray($datosValidados);
            $escolaridad = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Escolaridad actualizada correctamente.',
                'data' => $escolaridad,
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
    public function cambiarEstatus(int $id_escolaridad): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus([
                'id_escolaridad' => $id_escolaridad
            ]);

            $escolaridad = $this->service->cambiarEstatus(
                (int) $datosValidados['id_escolaridad']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => $escolaridad->activo ? 'Escolaridad activada correctamente.' : 'Escolaridad desactivada correctamente.',
                'data' => $escolaridad,
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
