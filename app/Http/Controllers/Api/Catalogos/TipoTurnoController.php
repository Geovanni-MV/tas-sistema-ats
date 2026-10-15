<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\TipoTurnoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\TipoTurnoService;
use App\Validators\Catalogos\TipoTurnoValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Controller del catálogo Tipo de turno.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de TipoTurnoValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a TipoTurnoService.
 */
final class TipoTurnoController extends Controller
{
    public function __construct(
        private readonly TipoTurnoService $service,
        private readonly TipoTurnoValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tipos de turno obtenidos correctamente.',
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
     * Registra un nuevo tipo de turno.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = TipoTurnoDTO::fromArray($datosValidados);
            $tipoTurno = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tipo de turno registrado correctamente.',
                'data' => $tipoTurno,
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
    public function actualizar(Request $request, int $id_tipo_turno): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_tipo_turno' => $id_tipo_turno,
            ]);

            $dto = TipoTurnoDTO::fromArray($datosValidados);
            $tipoTurno = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tipo de turno actualizado correctamente.',
                'data' => $tipoTurno,
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
    public function cambiarEstatus(int $id_tipo_turno): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus([
                'id_tipo_turno' => $id_tipo_turno
            ]);

            $tipoTurno = $this->service->cambiarEstatus(
                (int) $datosValidados['id_tipo_turno']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => $tipoTurno->activo ? 'Tipo de turno activado correctamente.' : 'Tipo de turno desactivado correctamente.',
                'data' => $tipoTurno,
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
