<?php


namespace App\Http\Controllers\Reclutamiento;

use App\DTO\Reclutamiento\EmpresaDTO;
use App\Http\Controllers\Controller;
use App\Services\Reclutamiento\EmpresaService;
use App\Validators\Reclutamiento\EmpresaValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
     * Esquema: reclutamiento
     * Entidad: empresas
     * 
     * Responsabilidades:
     * - Entregar la vista Blade del módulo.
     * - Recibir, arma y responde peticiones REST.
     * - Validar la entrada por medio del Validator correspondiente.
     * - Generar DTO de transferencia.
     * - Delegar la lógica de negocio al service correspondiente.
     */

class EmpresaController extends Controller
{
    public function __construct(
        private readonly EmpresaService $service,
        private readonly EmpresaValidator $validator
    ){}

    /**
     * Renderiza únicamente la vista de la entidad
     * Los datos dinamicos se obtienen posteriormente la API REST.
     * Los datos estaticos se obtienen en esta funcion y se mandan por params
     */
    public function renderView(): View
    {
        return view('reclutamiento.empresas.index');
    }
    /**
     * devuelve todos los registros del catálogo.
     */
    public function obtenerDatos(): JsonResponse
    {
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Datos obtenidos correctamente.',
            'data' => $this->service->listar(),
        ]);
    }

    /**
     * Genera nuevos registros
     */
    public function crear(Request $request): JsonResponse
    {

        $datos_validados = $this->validator->validateCreate($request->all());

        $dto = EmpresaDTO::fromArray($datos_validados);
        $empresa = $this->service->crear($dto);
        
        return response()->json([
            'estatus' => true,
            'mensaje' => 'Datos registraods correctamente.',
            'datta' => $empresa
        ], 201);
    }
    /**
     * Actualiza registro existente.
     */
    public function actualizar(Request $request, int $id_empresa): JsonResponse
    {
        $datos_validados = $this->validator->validateUpdate([
            'id_empresa'=>$id_empresa,
            ...$request->all()
        ]);

        $dto = EmpresaDTO::fromArray($datos_validados);
        $empresa = $this->service->actualizar($dto);

        return response()->json([
            'estatus' => true,
            'mensaje' => 'Datos actualizados correctamente.',
            'data' => $empresa
        ]);
    }
}
