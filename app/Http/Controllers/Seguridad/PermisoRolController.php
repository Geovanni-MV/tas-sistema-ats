<?php

namespace App\Http\Controllers\Seguridad;

use App\DTO\Seguridad\PermisoRolDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\PermisoRolService;
use App\Validators\Seguridad\PermisoRolValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Esquema: seguridad
 * Entidad: permisos_roles
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class PermisoRolController extends Controller
{
    public function __construct(
        private readonly PermisoRolService $service,
        private readonly PermisoRolValidator $validator
    ) {}

    /**
     * Renderiza únicamente la vista de la entidad
     * Los datos dinamicos se obtienen posteriormente la API REST.
     * Los datos estaticos se obtienen en esta funcion y se mandan por params
     */
    public function renderView(): View
    {
        return view('modulos.seguridad.permisos-roles.index');
    }

    /**
     * Devuelve todos los registros.
     * Filtro opcional por query string: ?id_rol=1
     */
    public function obtenerDatos(Request $request): JsonResponse
    {
        try{        
            $filtros = $this->validator->validateFiltros($request->query());

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Asignaciones obtenidas correctamente.',
                'data' => $this->service->listar(
                    isset($filtros['id_rol']) ? (int) $filtros['id_rol'] : null
                ),
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

            $dto = PermisoRolDTO::fromArray($datos_validados);
            $registro = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Asignación registrada correctamente.',
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
    public function actualizar(Request $request, int $id_permiso_rol): JsonResponse
    {
        try{        
            $datos_validados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_permiso_rol' => $id_permiso_rol,
            ]);

            $dto = PermisoRolDTO::fromArray($datos_validados);
            $registro = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Asignación actualizada correctamente.',
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
    public function cambiarEstatus(int $id_permiso_rol): JsonResponse
    {
        try{
            $datos_validados = $this->validator->validateStatus(['id_permiso_rol' => $id_permiso_rol]);

            $registro = $this->service->cambiarEstatus((int) $datos_validados['id_permiso_rol']);

            return response()->json([
                'estatus' => true,
                'mensaje' => $registro->activo
                    ? 'Asignación activada correctamente.'
                    : 'Asignación desactivada correctamente.',
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
