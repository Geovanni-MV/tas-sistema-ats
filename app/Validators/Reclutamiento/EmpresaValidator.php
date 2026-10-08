<?php

namespace App\Validators\Reclutamiento;

use App\Models\reclutamiento\EmpresasModel;

use Closure;

use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación de la entidad de reclutamiento.empresas
 */
final class EmpresaValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_empresa' => $this->reglasNombre(),
            'img_logo_url' => ['nullable', 'string']
        ], $this->mensajes())->validate();
    }

    public function validateUpdate(array $data): array
    {
        return Validator::make($data, [
            'id_empresa' => ['required', 'integer', 'min:1'],
            'nombre_empresa' => $this->reglasNombre((int) ($data['id_empresa'] ?? 0)),
            'img_logo_url' => ['nullable', 'string']
        ], $this->mensajes())->validate();
    }

    private function reglasNombre(?int $id_excluido = null): array
    {
        return [
            'required',
            'string',
            'max:150',
            $this->nombreNoDuplicado($id_excluido)
        ];
    }

    private function nombreNoDuplicado(?int $id_excluido): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($id_excluido): void {
            $nombre_empresa = mb_strtolower(trim((string) $valor));

            $existe = EmpresasModel::query()
                ->whereRaw('LOWER(TRIM(nombre_empresa)) = ?', [$nombre_empresa])
                ->when($id_excluido !== null, fn ($query) => $query->where('id_empresa', '<>', $id_excluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe una empresa con el mismo nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_empresa.required' => 'El nombre de la empresa es obligatorio.',
            'nombre_empresa.string' => 'El nombre de la empresa debe ser texto.',
            'nombre_empresa.max' => 'El nombre no puede exceder 150 caracteres.'
        ];
    }
}

?>