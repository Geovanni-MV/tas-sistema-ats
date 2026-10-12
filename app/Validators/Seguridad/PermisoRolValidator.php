<?php

namespace App\Validators\Seguridad;

use App\Models\Seguridad\PermisoRolModel;
use App\Models\Seguridad\PermisoModel;
use App\Models\Seguridad\RolModel;
use App\Models\Seguridad\MenuModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación de la entidad seguridad.permisos_roles
 */
final class PermisoRolValidator
{
    /** @throws ValidationException */
    public function validateFiltros(array $data): array
    {
        return Validator::make($data, [
            'id_rol' => ['nullable', 'integer', 'min:1'],
        ])->validate();
    }

    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, $this->reglasAsignacion($data), $this->mensajes())->validate();
    }

    /**
     * El campo "activo" no se modifica aquí; para eso existe validateStatus().
     *
     * @throws ValidationException
     */
    public function validateUpdate(array $data): array
    {
        return Validator::make($data, [
            'id_permiso_rol' => ['required', 'integer', 'min:1'],
            ...$this->reglasAsignacion($data, (int) ($data['id_permiso_rol'] ?? 0)),
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_permiso_rol' => ['required', 'integer', 'min:1'],
        ])->validate();
    }

    private function reglasAsignacion(array $data, ?int $id_excluido = null): array
    {
        return [
            'id_permiso' => [
                'bail',
                'required',
                'integer',
                'min:1',
                Rule::exists(PermisoModel::class, 'id_permiso'),
                $this->asignacionNoDuplicada($data, $id_excluido),
            ],
            'id_rol' => ['bail', 'required', 'integer', 'min:1', Rule::exists(RolModel::class, 'id_rol')],
            'id_menu' => ['bail', 'required', 'integer', 'min:1', Rule::exists(MenuModel::class, 'id_menu')],
        ];
    }

    /**
     * La combinación permiso + rol + menú no puede repetirse.
     * La base de datos no tiene una restricción única para esto, por eso se valida aquí.
     */
    private function asignacionNoDuplicada(array $data, ?int $id_excluido): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($data, $id_excluido): void {
            $id_rol = $data['id_rol'] ?? null;
            $id_menu = $data['id_menu'] ?? null;

            if (! ctype_digit((string) $id_rol) || ! ctype_digit((string) $id_menu)) {
                return;
            }

            $existe = PermisoRolModel::query()
                ->where('id_permiso', (int) $valor)
                ->where('id_rol', (int) $id_rol)
                ->where('id_menu', (int) $id_menu)
                ->when($id_excluido !== null, fn ($query) => $query->where('id_permiso_rol', '<>', $id_excluido))
                ->exists();

            if ($existe) {
                $fail('El rol ya tiene asignado este permiso sobre el menú indicado.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'id_permiso.required' => 'El permiso es obligatorio.',
            'id_permiso.exists' => 'El permiso seleccionado no existe.',
            'id_rol.required' => 'El rol es obligatorio.',
            'id_rol.exists' => 'El rol seleccionado no existe.',
            'id_menu.required' => 'El menú es obligatorio.',
            'id_menu.exists' => 'El menú seleccionado no existe.',
        ];
    }
}
