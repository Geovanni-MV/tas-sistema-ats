<?php

namespace App\Validators\Catalogos;

use App\Models\Catalogos\DocumentoModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación del catálogo Documento.
 */
final class DocumentoValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_documento' => $this->reglasNombre(),
            'requiere_ambos_lados' => ['sometimes', 'boolean'],
            'maximo_archivos' => ['sometimes', 'integer', 'between:1,10'],
            'descripcion' => ['nullable', 'string'],
            
        ], $this->mensajes())->validate();
    }

    /**
     * En la actualización no se permite modificar "activo" (para eso existe
     * validateStatus) y los duplicados excluyen el propio registro.
     *
     * @throws ValidationException
     */
    public function validateUpdate(array $data): array
    {
        $id_excluido = (int) ($data['id_documento'] ?? 0);

        return Validator::make($data, [
            'id_documento' => ['required', 'integer', 'min:1'],
            'nombre_documento' => $this->reglasNombre($id_excluido),
            'requiere_ambos_lados' => ['required', 'boolean'],
            'maximo_archivos' => ['required', 'integer', 'between:1,10'],
            'descripcion' => ['nullable', 'string'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_documento' => ['required', 'integer', 'min:1']
        ])
        ->validate();
    }

    /**
     * Reglas del nombre. Si se recibe $id_excluido, ese registro se ignora
     * al buscar duplicados (caso de actualización).
     */
    private function reglasNombre(?int $id_excluido = null): array
    {
        return [
            'required',
            'string',
            'max:150',
            $this->nombreNoDuplicado($id_excluido),
        ];
    }

    /**
     * Comparación sin distinguir mayúsculas/minúsculas ni espacios en los extremos.
     */
    private function nombreNoDuplicado(?int $id_excluido): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($id_excluido): void {
            $nombre = mb_strtolower(trim((string) $valor));

            $existe = DocumentoModel::query()
                ->whereRaw('LOWER(TRIM(nombre_documento)) = ?', [$nombre])
                ->when($id_excluido, fn ($query) => $query->where('id_documento', '!=', $id_excluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un documento con este nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_documento.required' => 'El nombre del documento es obligatorio.',
            'nombre_documento.string' => 'El nombre del documento debe ser texto.',
            'nombre_documento.max' => 'El nombre no puede exceder 150 caracteres.',
            'requiere_ambos_lados.required' => 'Indica si el documento requiere ambos lados (verdadero o falso).',
            'requiere_ambos_lados.boolean' => 'Indica si el documento requiere ambos lados (verdadero o falso).',
            'maximo_archivos.required' => 'El máximo de archivos es obligatorio.',
            'maximo_archivos.integer' => 'El máximo de archivos debe ser un número entero.',
            'maximo_archivos.between' => 'El máximo de archivos debe estar entre 1 y 10.',
        ];
    }
}
