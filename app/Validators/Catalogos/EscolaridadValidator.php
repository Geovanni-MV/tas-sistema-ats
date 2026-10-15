<?php

namespace App\Validators\Catalogos;

use App\Models\Catalogos\EscolaridadModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación del catálogo Escolaridad.
 */
final class EscolaridadValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_escolaridad' => $this->reglasNombre(),
            'descripcion' => ['required', 'string']
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
        $idExcluido = (int) ($data['id_escolaridad'] ?? 0);

        return Validator::make($data, [
            'id_escolaridad' => ['required', 'integer', 'min:1'],
            'nombre_escolaridad' => $this->reglasNombre($idExcluido),
            'descripcion' => ['required', 'string'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_escolaridad' => ['required', 'integer', 'min:1']
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

            $existe = EscolaridadModel::query()
                ->whereRaw('LOWER(TRIM(nombre_escolaridad)) = ?', [$nombre])
                ->when($idExcluido, fn ($query) => $query->where('id_escolaridad', '!=', $idExcluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe una escolaridad con este nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_escolaridad.required' => 'El nombre de la escolaridad es obligatorio.',
            'nombre_escolaridad.string' => 'El nombre de la escolaridad debe ser texto.',
            'nombre_escolaridad.max' => 'El nombre no puede exceder 150 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser texto.',
        ];
    }
}
