<?php

namespace App\Validators\Catalogos;

use App\Models\Catalogos\Sexo;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación del catálogo Sexo.
 */
final class SexoValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_sexo' => $this->reglasNombre(),
            'descripcion' => ['nullable', 'string'],
            'activo' => ['sometimes', 'boolean'],
        ], $this->mensajes())->validate();
    }

    /**
     * En la actualización no se permite modificar "activo" (para eso existe
     * validateStatus) y el nombre no puede repetirse, excluyendo el propio registro.
     *
     * @throws ValidationException
     */
    public function validateUpdate(array $data): array
    {
        return Validator::make($data, [
            'id_sexo' => ['required', 'integer', 'min:1'],
            'nombre_sexo' => $this->reglasNombre((int) ($data['id_sexo'] ?? 0)),
            'descripcion' => ['nullable', 'string'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_sexo' => ['required', 'integer', 'min:1'],
            'activo' => ['required', 'boolean'],
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

            $existe = Sexo::query()
                ->whereRaw('LOWER(TRIM(nombre_sexo)) = ?', [$nombre])
                ->when($idExcluido, fn ($query) => $query->where('id_sexo', '!=', $idExcluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un sexo con este nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_sexo.required' => 'El nombre del sexo es obligatorio.',
            'nombre_sexo.string' => 'El nombre del sexo debe ser texto.',
            'nombre_sexo.max' => 'El nombre no puede exceder 150 caracteres.',
        ];
    }
}
