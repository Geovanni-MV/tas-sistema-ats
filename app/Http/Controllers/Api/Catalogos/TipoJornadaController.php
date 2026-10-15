<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\TipoJornadaDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\TipoJornadaService;
use App\Validators\Catalogos\TipoJornadaValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Controller del catálogo Tipo de jornada.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de TipoJornadaValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a TipoJornadaService.
 */
final class TipoJornadaController extends Controller
{
    public function __construct(
        private readonly TipoJornadaService $service,
        private readonly TipoJornadaValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tipos de jornada obtenidos correctamente.',
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
     * Registra un nuevo tipo de jornada.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = TipoJornadaDTO::fromArray($datosValidados);
            $tipoJornada = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tipo de jornada registrado correctamente.',
                'data' => $tipoJornada,
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
    public function actualizar(Request $request, int $id_tipo_jornada): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_tipo_jornada' => $id_tipo_jornada,
            ]);

            $dto = TipoJornadaDTO::fromArray($datosValidados);
            $tipoJornada = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tipo de jornada actualizado correctamente.',
                'data' => $tipoJornada,
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
    public function cambiarEstatus(int $id_tipo_jornada): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus([
                'id_tipo_jornada' => $id_tipo_jornada,
            ]);

            $tipoJornada = $this->service->cambiarEstatus(
                (int) $datosValidados['id_tipo_jornada']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => $tipoJornada->activo ? 'Tipo de jornada activado correctamente.' : 'Tipo de jornada desactivado correctamente.',
                'data' => $tipoJornada,
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
