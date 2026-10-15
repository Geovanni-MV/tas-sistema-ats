<?php

namespace App\Http\Controllers\Api\Seguridad;

use App\DTO\Seguridad\MenuDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\MenuService;
use App\Validators\Seguridad\MenuValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;
/**
 * Esquema: seguridad
 * Entidad: menu
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $service,
        private readonly MenuValidator $validator
    ) {}

    /**
     * Devuelve todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Menús obtenidos correctamente.',
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
     * Genera un nuevo registro.
     */
    public function crear(Request $request): JsonResponse
    {
        try{        
            $datos_validados = $this->validator->validateCreate($request->all());

            $dto = MenuDTO::fromArray($datos_validados);
            $registro = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Menú registrado correctamente.',
                'data' => $registro,
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
    public function actualizar(Request $request, int $id_menu): JsonResponse
    {
        try{        
            $datos_validados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_menu' => $id_menu,
            ]);

            $dto = MenuDTO::fromArray($datos_validados);
            $registro = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Menú actualizado correctamente.',
                'data' => $registro,
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
     * Activa o desactiva un registro (alterna el valor actual).
     */
    public function cambiarEstatus(int $id_menu): JsonResponse
    {
        try{        
            $datos_validados = $this->validator->validateStatus(['id_menu' => $id_menu]);

            $registro = $this->service->cambiarEstatus((int) $datos_validados['id_menu']);

            return response()->json([
                'estatus' => true,
                'mensaje' => $registro->activo
                    ? 'Menú activado correctamente.'
                    : 'Menú desactivado correctamente.',
                'data' => $registro,
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
