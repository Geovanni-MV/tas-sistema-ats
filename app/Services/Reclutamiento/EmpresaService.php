<?php
namespace App\Services\Reclutamiento;

use App\DTO\Reclutamiento\EmpresaDTO;
use App\Models\Reclutamiento\EmpresasModel;
use Illuminate\Support\Collection;
/**
 * Conteiene la lógica de negocio para la entidad de reclutamiento.empresas
 */

final class EmpresaService
{
    /**
     * Obtiene todos los registros ordenados por identificados
     */
    public function listar(): Collection
    {
        return EmpresasModel::query()->orderBy('id_empresa')->get();
    }

    /**
     * Crea un registro apartir del DTO recibido por el controlador
     */
    public function crear(EmpresaDTO $dto): EmpresasModel
    {
        return EmpresasModel::create([
            'nombre_empresa' => $dto->nombre_empresa,
            'img_logo_url' => $dto->img_logo_url
        ]);
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica en esta funcion; se gestiona en cambiarEstatus()
     */
    public function actualizar(EmpresaDTO $dto): EmpresasModel
    {
        $empresa = EmpresasModel::query()->findOrFail($dto->id_empresa);
        
        $empresa->fill([
            'nombre_empresa' => $dto->nombre_empresa,
            'img_logo_url' => $dto->img_logo_url
        ])->save();
        return $empresa->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_empresa): EmpresasModel
    {
        $empresa = EmpresasModel::findOrFail($id_empresa);

        $empresa->fill([
            'activo' => !$empresa->activo
        ])->save();

        return $empresa->refresh();
    }

}
?>