<?php

namespace App\Validators\Seguridad;

use App\Models\Seguridad\PermisoModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación de la entidad seguridad.permisos
 */
final class PermisoValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_permiso' => $this->reglasNombre(),
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
            'id_permiso' => ['required', 'integer', 'min:1'],
            'nombre_permiso' => $this->reglasNombre((int) ($data['id_permiso'] ?? 0)),
            'descripcion' => ['nullable', 'string'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_permiso' => ['required', 'integer', 'min:1'],
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
            $nombre_permiso = mb_strtolower(trim((string) $valor));

            $existe = PermisoModel::query()
                ->whereRaw('LOWER(TRIM(nombre_permiso)) = ?', [$nombre_permiso])
                ->when($id_excluido !== null, fn ($query) => $query->where('id_permiso', '<>', $id_excluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un permiso con el mismo nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_permiso.required' => 'El nombre del permiso es obligatorio.',
            'nombre_permiso.string' => 'El nombre del permiso debe ser texto.',
            'nombre_permiso.max' => 'El nombre no puede exceder 150 caracteres.',
        ];
    }
}
