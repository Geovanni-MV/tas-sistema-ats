<?php

namespace App\Http\Controllers\Api\Seguridad;

use App\DTO\Seguridad\PermisoRolDTO;
use App\DTO\Seguridad\PermisoRolSincronizacionDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\PermisoRolService;
use App\Validators\Seguridad\PermisoRolValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

final class PermisoRolController extends Controller
{
    public function __construct(
        private readonly PermisoRolService $service,
        private readonly PermisoRolValidator $validator
    ) {}

    /**
     * Devuelve todos los registros.
     * Filtro opcional por query string: ?id_rol=1
     */
    public function obtenerDatos(Request $request): JsonResponse
    {
        try {

            $filtros = $this->validator->validateFiltros(
                $request->query()
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Asignaciones obtenidas correctamente.',
                'data' => $this->service->listar(
                    isset($filtros['id_rol'])
                        ? (int) $filtros['id_rol']
                        : null
                ),
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Los datos enviados no son válidos',
                'errores' => $e->errors(),
            ], 422);

        } catch (QueryException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'No fue posible obtener los datos.',
            ], 500);

        } catch (Throwable $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Ocurrió un error interno al obtener los datos.',
            ], 500);
        }
    }


    /**
     * Genera un nuevo registro.
     */
    public function crear(Request $request): JsonResponse
    {
        try {

            $datos_validados = $this->validator
                ->validateCreate(
                    $request->all()
                );

            $dto = PermisoRolDTO::fromArray(
                $datos_validados
            );

            $registro = $this->service
                ->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Asignación registrada correctamente.',
                'data' => $registro,
            ], 201);

        } catch (ValidationException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Los datos enviados no son válidos',
                'errores' => $e->errors(),
            ], 422);

        } catch (QueryException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'No fue posible generar el registro.',
            ], 500);

        } catch (Throwable $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Ocurrió un error interno.',
            ], 500);
        }
    }


    /**
     * Actualiza un registro existente.
     */
    public function actualizar(
        Request $request,
        int $id_permiso_rol
    ): JsonResponse {

        try {

            $datos_validados = $this->validator
                ->validateUpdate([
                    ...$request->all(),
                    'id_permiso_rol' => $id_permiso_rol,
                ]);

            $dto = PermisoRolDTO::fromArray(
                $datos_validados
            );

            $registro = $this->service
                ->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Asignación actualizada correctamente.',
                'data' => $registro,
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Los datos enviados no son válidos',
                'errores' => $e->errors(),
            ], 422);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'El registro no existe.',
            ], 404);

        } catch (QueryException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'No fue posible actualizar el registro.',
            ], 500);

        } catch (Throwable $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Ocurrió un error interno.',
            ], 500);
        }
    }


    /**
     * Activa o desactiva un registro.
     */
    public function cambiarEstatus(
        int $id_permiso_rol
    ): JsonResponse {

        try {

            $datos_validados = $this->validator
                ->validateStatus([
                    'id_permiso_rol' => $id_permiso_rol,
                ]);

            $registro = $this->service
                ->cambiarEstatus(
                    (int) $datos_validados['id_permiso_rol']
                );

            return response()->json([
                'estatus' => true,
                'mensaje' => $registro->activo
                    ? 'Asignación activada correctamente.'
                    : 'Asignación desactivada correctamente.',
                'data' => $registro,
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Los datos enviados no son válidos',
                'errores' => $e->errors(),
            ], 422);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'El registro no existe.',
            ], 404);

        } catch (QueryException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'No fue posible cambiar el estatus.',
            ], 500);

        } catch (Throwable $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Ocurrió un error interno.',
            ], 500);
        }
    }


    /**
     * Sincroniza en una sola petición
     * las asignaciones de un rol.
     */
    public function sincronizar(
        Request $request,
        int $id_rol
    ): JsonResponse {

        try {

            $datos_validados = $this->validator
                ->validateSync([
                    ...$request->all(),
                    'id_rol' => $id_rol,
                ]);

            $dto = PermisoRolSincronizacionDTO::fromArray(
                $datos_validados
            );

            $resultado = $this->service
                ->sincronizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Permisos del rol sincronizados correctamente.',
                'data' => $resultado['asignaciones'],
                'resumen' => $resultado['resumen'],
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Los datos enviados no son válidos',
                'errores' => $e->errors(),
            ], 422);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'El rol no existe.',
            ], 404);

        } catch (QueryException $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'No fue posible sincronizar los permisos del rol.',
            ], 500);

        } catch (Throwable $e) {

            return response()->json([
                'estatus' => false,
                'mensaje' => 'Ocurrió un error interno.',
            ], 500);
        }
    }
}