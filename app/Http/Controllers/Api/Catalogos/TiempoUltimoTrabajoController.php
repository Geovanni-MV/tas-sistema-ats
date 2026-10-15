<?php

namespace App\Http\Controllers\Api\Catalogos;

use App\DTO\Catalogos\TiempoUltimoTrabajoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\TiempoUltimoTrabajoService;
use App\Validators\Catalogos\TiempoUltimoTrabajoValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Controller del catálogo Tiempo del último trabajo.
 *
 * Responsabilidades:
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de TiempoUltimoTrabajoValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a TiempoUltimoTrabajoService.
 */
final class TiempoUltimoTrabajoController extends Controller
{
    public function __construct(
        private readonly TiempoUltimoTrabajoService $service,
        private readonly TiempoUltimoTrabajoValidator $validator,
    ) {}

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tiempos del último trabajo obtenidos correctamente.',
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
     * Registra un nuevo tiempo del último trabajo.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = TiempoUltimoTrabajoDTO::fromArray($datosValidados);
            $tiempoUltimoTrabajo = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tiempo del último trabajo registrado correctamente.',
                'data' => $tiempoUltimoTrabajo,
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
    public function actualizar(Request $request, int $id_tiempo_ultimo_trabajo): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_tiempo_ultimo_trabajo' => $id_tiempo_ultimo_trabajo,
            ]);

            $dto = TiempoUltimoTrabajoDTO::fromArray($datosValidados);
            $tiempoUltimoTrabajo = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Tiempo del último trabajo actualizado correctamente.',
                'data' => $tiempoUltimoTrabajo,
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
    public function cambiarEstatus(int $id_tiempo_ultimo_trabajo): JsonResponse
    {
        $datosValidados = $this->validator->validateStatus([
            'id_tiempo_ultimo_trabajo' => $id_tiempo_ultimo_trabajo
        ]);

        $tiempoUltimoTrabajo = $this->service->cambiarEstatus(
            (int) $datosValidados['id_tiempo_ultimo_trabajo']
        );

        return response()->json([
            'estatus' => true,
            'mensaje' => $tiempoUltimoTrabajo->activo ? 'Tiempo del último trabajo activado correctamente.' : 'Tiempo del último trabajo desactivado correctamente.',
            'data' => $tiempoUltimoTrabajo,
        ]);
    }
}
