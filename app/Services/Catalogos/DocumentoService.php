<?php

namespace App\Services\Catalogos;

use App\DTO\Catalogos\DocumentoDTO;
use App\Models\Catalogos\DocumentoModel;
use Illuminate\Support\Collection;

/**
 * Contiene la lógica de negocio del catálogo Documento.
 */
final class DocumentoService
{
    /**
     * Obtiene todos los registros ordenados por identificador.
     */
    public function listar(): Collection
    {
        return DocumentoModel::query()->orderBy('id_documento')->get();
    }

    /**
     * Crea un registro a partir del DTO recibido por el Controller.
     */
    public function crear(DocumentoDTO $dto): DocumentoModel
    {
        $documento = DocumentoModel::query()->create([
            'nombre_documento' => $dto->nombre_documento,
            'requiere_ambos_lados' => $dto->requiere_ambos_lados,
            'maximo_archivos' => $dto->maximo_archivos,
            'descripcion' => $dto->descripcion
        ]);

        return $documento->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(DocumentoDTO $dto): DocumentoModel
    {
        $documento = DocumentoModel::query()->findOrFail($dto->id_documento);

        $documento->fill([
            'nombre_documento' => $dto->nombre_documento,
            'requiere_ambos_lados' => $dto->requiere_ambos_lados,
            'maximo_archivos' => $dto->maximo_archivos,
            'descripcion' => $dto->descripcion,
            
        ])->save();

        return $documento->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $id_documento): DocumentoModel
    {
        $documento = DocumentoModel::query()->findOrFail($id_documento);
        $documento->fill([
            'activo' => !$documento->activo,
            
        ])->save();

        return $documento->refresh();
    }
}
