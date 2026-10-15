<?php

namespace App\Validators\Catalogos;

use App\Models\Catalogos\EstadoCivilModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación del catálogo Estado civil.
 */
final class EstadoCivilValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_estado_civil' => $this->reglasNombre(),
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
        $idExcluido = (int) ($data['id_estado_civil'] ?? 0);

        return Validator::make($data, [
            'id_estado_civil' => ['required', 'integer', 'min:1'],
            'nombre_estado_civil' => $this->reglasNombre($idExcluido),
            'descripcion' => ['nullable', 'string'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_estado_civil' => ['required', 'integer', 'min:1'],
            
        ])->validate();
    }

    /**
     * Reglas del nombre. Si se recibe $idExcluido, ese registro se ignora
     * al buscar duplicados (caso de actualización).
     */
    private function reglasNombre(?int $idExcluido = null): array
    {
        return [
            'required',
            'string',
            'max:150',
            $this->nombreNoDuplicado($idExcluido),
        ];
    }

    /**
     * Comparación sin distinguir mayúsculas/minúsculas ni espacios en los extremos.
     */
    private function nombreNoDuplicado(?int $idExcluido): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($idExcluido): void {
            $nombre = mb_strtolower(trim((string) $valor));

            $existe = EstadoCivilModel::query()
                ->whereRaw('LOWER(TRIM(nombre_estado_civil)) = ?', [$nombre])
                ->when($idExcluido, fn ($query) => $query->where('id_estado_civil', '!=', $idExcluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un estado civil con este nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_estado_civil.required' => 'El nombre del estado civil es obligatorio.',
            'nombre_estado_civil.string' => 'El nombre del estado civil debe ser texto.',
            'nombre_estado_civil.max' => 'El nombre no puede exceder 150 caracteres.',
        ];
    }
}
