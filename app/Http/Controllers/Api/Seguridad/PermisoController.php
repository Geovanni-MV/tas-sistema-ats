<?php

namespace App\Http\Controllers\Api\Seguridad;

use App\DTO\Seguridad\PermisoDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\PermisoService;
use App\Validators\Seguridad\PermisoValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Esquema: seguridad
 * Entidad: permisos
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class PermisoController extends Controller
{
    public function __construct(
        private readonly PermisoService $service,
        private readonly PermisoValidator $validator
    ) {}

    /**
     * Renderiza únicamente la vista de la entidad
     * Los datos dinamicos se obtienen posteriormente la API REST.
     * Los datos estaticos se obtienen en esta funcion y se mandan por params
     */
    public function renderView(): View
    {
        return view('modulos.seguridad.permisos.index');
    }
    /**
     * Devuelve todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Permisos obtenidos correctamente.',
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

            $dto = PermisoDTO::fromArray($datos_validados);
            $registro = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Permiso registrado correctamente.',
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
    public function actualizar(Request $request, int $id_permiso): JsonResponse
    {
        try{
            $datos_validados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_permiso' => $id_permiso,
            ]);

            $dto = PermisoDTO::fromArray($datos_validados);
            $registro = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Permiso actualizado correctamente.',
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
    public function cambiarEstatus(int $id_permiso): JsonResponse
    {
        try{
            $datos_validados = $this->validator->validateStatus(['id_permiso' => $id_permiso]);

            $registro = $this->service->cambiarEstatus((int) $datos_validados['id_permiso']);

            return response()->json([
                'estatus' => true,
                'mensaje' => $registro->activo
                    ? 'Permiso activado correctamente.'
                    : 'Permiso desactivado correctamente.',
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
