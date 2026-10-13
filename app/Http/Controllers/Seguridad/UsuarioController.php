<?php

namespace App\Http\Controllers\Seguridad;

use App\DTO\Seguridad\UsuarioDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\UsuarioService;
use App\Validators\Seguridad\UsuarioValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Esquema: seguridad
 * Entidad: usuarios
 *
 * Responsabilidades:
 * - Recibir, armar y responder peticiones REST.
 * - Validar la entrada por medio del Validator correspondiente.
 * - Generar el DTO de transferencia.
 * - Delegar la lógica de negocio al service correspondiente.
 */
final class UsuarioController extends Controller
{
    public function __construct(
        private readonly UsuarioService $service,
        private readonly UsuarioValidator $validator
    ) {}

    /**
     * Renderiza únicamente la vista de la entidad
     * Los datos dinamicos se obtienen posteriormente la API REST.
     * Los datos estaticos se obtienen en esta funcion y se mandan por params
     */
    public function renderView(): View
    {
        return view('modulos.seguridad.usuarios.index');
    }

    /**
     * Devuelve todos los registros.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Usuarios obtenidos correctamente.',
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

            $dto = UsuarioDTO::fromArray($datos_validados);
            $usuario = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Usuario registrado correctamente.',
                'data' => $usuario,
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
    public function actualizar(Request $request, int $id_usuario): JsonResponse
    {
        try{        
            $datos_validados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_usuario' => $id_usuario,
            ]);

            $dto = UsuarioDTO::fromArray($datos_validados);
            $usuario = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Usuario actualizado correctamente.',
                'data' => $usuario,
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
     * Actualiza únicamente la clave de un registro existente.
     */
    public function cambiarClave(Request $request, int $id_usuario): JsonResponse
    {
        try{
            $datos_validados = $this->validator->validateClave([
                ...$request->all(),
                'id_usuario' => $id_usuario,
            ]);

            $usuario = $this->service->cambiarClave(
                (int) $datos_validados['id_usuario'],
                (string) $datos_validados['clave']
            );

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Clave actualizada correctamente.',
                'data' => $usuario,
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
                'mensaje'=>'No fue posible actualizar la clave.'
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
    public function cambiarEstatus(int $id_usuario): JsonResponse
    {
        try{
            $datos_validados = $this->validator->validateStatus(['id_usuario' => $id_usuario]);

            $usuario = $this->service->cambiarEstatus((int) $datos_validados['id_usuario']);

            return response()->json([
                'estatus' => true,
                'mensaje' => $usuario->activo
                    ? 'Usuario activado correctamente.'
                    : 'Usuario desactivado correctamente.',
                'data' => $usuario,
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
