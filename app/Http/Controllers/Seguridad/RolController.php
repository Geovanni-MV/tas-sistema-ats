<?php

namespace App\Http\Controllers\Seguridad;

use App\DTO\Seguridad\RolDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\RolService;
use App\Validators\Seguridad\RolValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Esquema: seguridad
 * Entidad: roles
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class RolController extends Controller
{
    public function __construct(
        private readonly RolService $service,
        private readonly RolValidator $validator
    ) {}

    /**
     * Renderiza únicamente la vista de la entidad
     * Los datos dinamicos se obtienen posteriormente la API REST.
     * Los datos estaticos se obtienen en esta funcion y se mandan por params
     */
    public function renderView(): View
    {
        return view('modulos.seguridad.roles.index');
    }

    /**
     * Devuelve todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{        
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Roles obtenidos correctamente.',
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

            $dto = RolDTO::fromArray($datos_validados);
            $registro = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Rol registrado correctamente.',
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
    public function actualizar(Request $request, int $id_rol): JsonResponse
    {
        try{        
            $datos_validados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_rol' => $id_rol,
            ]);

            $dto = RolDTO::fromArray($datos_validados);
            $registro = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Rol actualizado correctamente.',
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
    public function cambiarEstatus(int $id_rol): JsonResponse
    {
        try{        
            $datos_validados = $this->validator->validateStatus(['id_rol' => $id_rol]);

            $registro = $this->service->cambiarEstatus((int) $datos_validados['id_rol']);

            return response()->json([
                'estatus' => true,
                'mensaje' => $registro->activo
                    ? 'Rol activado correctamente.'
                    : 'Rol desactivado correctamente.',
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
