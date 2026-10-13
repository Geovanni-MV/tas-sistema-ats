<?php

namespace App\Http\Controllers\Catalogos;

use App\DTO\Catalogos\SexoDTO;
use App\Http\Controllers\Controller;
use App\Services\Catalogos\SexoService;
use App\Validators\Catalogos\SexoValidator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Controller del catálogo Sexo.
 *
 * Responsabilidades:
 * - Entregar la vista Blade del módulo.
 * - Recibir y responder peticiones REST.
 * - Validar la entrada por medio de SexoValidator.
 * - Crear el DTO de transferencia.
 * - Delegar la lógica de negocio a SexoService.
 */
final class SexoController extends Controller
{
    public function __construct(
        private readonly SexoService $service,
        private readonly SexoValidator $validator,
    ) {}

    /**
     * Renderiza únicamente la vista del catálogo.
     * Los datos se obtienen posteriormente mediante la API REST.
     */
    public function vista(): View
    {
        return view('catalogos.sexos.index');
    }

    /**
     * Devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        try{
            return response()->json([
                'estatus' => true,
                'mensaje' => 'Sexos obtenidos correctamente.',
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
     * Registra un nuevo sexo.
     */
    public function crear(Request $request): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateCreate($request->all());
            $dto = SexoDTO::fromArray($datosValidados);
            $sexo = $this->service->crear($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Datos registrados correctamente.',
                'data' => $sexo,
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
    public function actualizar(Request $request, int $idSexo): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateUpdate([
                ...$request->all(),
                'id_sexo' => $idSexo,
            ]);

            $dto = SexoDTO::fromArray($datosValidados);
            $sexo = $this->service->actualizar($dto);

            return response()->json([
                'estatus' => true,
                'mensaje' => 'Datos actualizados correctamente.',
                'data' => $sexo,
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
    public function cambiarEstatus(Request $request, int $id_sexo): JsonResponse
    {
        try{
            $datosValidados = $this->validator->validateStatus(['id_sexo' => $id_sexo,]);

            $sexo = $this->service->cambiarEstatus((int) $datosValidados['id_sexo']);

            return response()->json([
                'estatus' => true,
                'mensaje' => $sexo->activo ? 'Sexo activado correctamente.' : 'Sexo desactivado correctamente.',
                'data' => $sexo,
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
