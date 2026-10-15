<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\EstadoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\EstadoService;
use App\Validators\Catalogos\EstadoValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Controller del catálogo Estado.
 *
 * Responsabilidades:
 * - Entregar la vista Blade.
 * - Recibir peticiones REST.
 * - Delegar validación a EstadoValidator.
 * - Construir el DTO.
 * - Delegar la lógica a EstadoService.
 */
final class EstadoController extends Controller
{
    public function __construct(
        private readonly EstadoService $service,
        private readonly EstadoValidator $validator,
    ) {}

    /**
     * Obtiene todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Estados obtenidos correctamente.',
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
     * Registra un nuevo estado.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = EstadoDTO::fromArray($datosValidados);
            $estado = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Estado registrado correctamente.',
                'data' => $estado,
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
                'mensaje'=>'Ocurrió un error interno.',
                'mensaje_error'=>$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza un estado existente.
     */
    public function actualizar(Request $request, int $id_estado): JsonResponse
    {
        try{        
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_estado' => $id_estado,
            ]);

            $dto = EstadoDTO::fromArray($datosValidados);
            $estado = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Estado actualizado correctamente.',
                'data' => $estado,
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
     * Activa o desactiva un estado.
     */
    public function cambiarEstatus(int $id_estado,): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus([
                'id_estado' => $id_estado
            ]);

            $estado = $this->service->cambiarEstatus(
                (int) $datosValidados['id_estado']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => $estado->activo
                    ? 'Estado activado correctamente.'
                    : 'Estado desactivado correctamente.',
                'data' => $estado,
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
