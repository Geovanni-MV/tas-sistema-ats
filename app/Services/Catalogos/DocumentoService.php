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
            'nombre_documento' => $dto->nombreDocumento,
            'requiere_ambos_lados' => $dto->requiereAmbosLados,
            'permite_captura_texto' => $dto->permiteCapturaTexto,
            'maximo_archivos' => $dto->maximoArchivos,
            'descripcion' => $dto->descripcion,
            'activo' => $dto->activo,
        ]);

        return $documento->refresh();
    }

    /**
     * Actualiza los datos editables de un registro existente.
     * El campo "activo" no se modifica aquí; se gestiona con cambiarEstatus().
     */
    public function actualizar(DocumentoDTO $dto): DocumentoModel
    {
        $documento = DocumentoModel::query()->findOrFail($dto->idDocumento);

        $documento->fill([
            'nombre_documento' => $dto->nombreDocumento,
            'requiere_ambos_lados' => $dto->requiereAmbosLados,
            'permite_captura_texto' => $dto->permiteCapturaTexto,
            'maximo_archivos' => $dto->maximoArchivos,
            'descripcion' => $dto->descripcion,
            'fecha_actualizacion' => now(),
        ])->save();

        return $documento->refresh();
    }

    /**
     * Cambia únicamente el campo activo del registro.
     */
    public function cambiarEstatus(int $idDocumento, bool $activo): DocumentoModel
    {
        $documento = DocumentoModel::query()->findOrFail($idDocumento);

        $documento->fill([
            'activo' => $activo,
            'fecha_actualizacion' => now(),
        ])->save();

        return $documento->refresh();
    }
}
