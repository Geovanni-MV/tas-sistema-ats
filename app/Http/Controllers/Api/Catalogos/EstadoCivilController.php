<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\EstadoCivilDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\EstadoCivilService;
use App\Validators\Catalogos\EstadoCivilValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Controller del catálogo Estado civil.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de EstadoCivilValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a EstadoCivilService.
 */
final class EstadoCivilController extends Controller
{
    public function __construct(
        private readonly EstadoCivilService $service,
        private readonly EstadoCivilValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Estados civiles obtenidos correctamente.',
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
     * Registra un nuevo estado civil.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = EstadoCivilDTO::fromArray($datosValidados);
            $estadoCivil = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Estado civil registrado correctamente.',
                'data' => $estadoCivil,
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
    public function actualizar(Request $request, int $id_estado_civil): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_estado_civil' => $id_estado_civil,
            ]);

            $dto = EstadoCivilDTO::fromArray($datosValidados);
            $estadoCivil = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Estado civil actualizado correctamente.',
                'data' => $estadoCivil,
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
    public function cambiarEstatus(int $id_estado_civil): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus([
                'id_estado_civil' => $id_estado_civil
            ]);

            $estadoCivil = $this->service->cambiarEstatus(
                (int) $datosValidados['id_estado_civil']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => $estadoCivil->activo ? 'Estado civil activado correctamente.' : 'Estado civil desactivado correctamente.',
                'data' => $estadoCivil,
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
