<?php

namespace App\Validators\Seguridad;

use App\Models\Seguridad\RolModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación de la entidad seguridad.roles
 */
final class RolValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_rol' => $this->reglasNombre(),
            'descripcion' => ['nullable', 'string'],
        ], $this->mensajes())->validate();
    }

    /**
     * El campo "activo" no se modifica aquí; para eso existe validateStatus().
     *
     * @throws ValidationException
     */
    public function validateUpdate(array $data): array
    {
        return Validator::make($data, [
            'id_rol' => ['required', 'integer', 'min:1'],
            'nombre_rol' => $this->reglasNombre((int) ($data['id_rol'] ?? 0)),
            'descripcion' => ['nullable', 'string'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_rol' => ['required', 'integer', 'min:1'],
        ])->validate();
    }

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
            $nombre_rol = mb_strtolower(trim((string) $valor));

            $existe = RolModel::query()
                ->whereRaw('LOWER(TRIM(nombre_rol)) = ?', [$nombre_rol])
                ->when($id_excluido !== null, fn ($query) => $query->where('id_rol', '<>', $id_excluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un rol con el mismo nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_rol.required' => 'El nombre del rol es obligatorio.',
            'nombre_rol.string' => 'El nombre del rol debe ser texto.',
            'nombre_rol.max' => 'El nombre no puede exceder 150 caracteres.',
        ];
    }
}
