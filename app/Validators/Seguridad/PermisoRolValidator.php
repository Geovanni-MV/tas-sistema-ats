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
    /** Máximo de asignaciones aceptadas en una sola sincronización. */
    private const MAXIMO_SINCRONIZACION = 2000;

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

    /**
     * Sincronización de los permisos de un rol: lista de { id_permiso, id_menu, activo }.
     *
     * Primero se valida la estructura y, solo si es correcta, se verifican contra la base de datos
     * las combinaciones repetidas y la existencia de permisos y menús. Esas comprobaciones se
     * hacen con una consulta por catálogo (whereIn) y no una por elemento, porque la lista puede
     * traer cientos de asignaciones.
     *
     * @throws ValidationException
     */
    public function validateSync(array $data): array
    {
        $validator = Validator::make($data, [
            'id_rol' => ['bail', 'required', 'integer', 'min:1', Rule::exists(RolModel::class, 'id_rol')],
            'asignaciones' => ['bail', 'required', 'array', 'min:1', 'max:' . self::MAXIMO_SINCRONIZACION],
            'asignaciones.*.id_permiso' => ['bail', 'required', 'integer', 'min:1'],
            'asignaciones.*.id_menu' => ['bail', 'required', 'integer', 'min:1'],
            'asignaciones.*.activo' => ['required', 'boolean'],
        ], $this->mensajes());

        $validator->after(function ($validator) use ($data): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validarAsignacionesSincronizacion($validator, array_values($data['asignaciones']));
        });

        return $validator->validate();
    }

    /**
     * @param  \Illuminate\Validation\Validator  $validator
     * @param  list<array<string, mixed>>  $asignaciones
     */
    private function validarAsignacionesSincronizacion($validator, array $asignaciones): void
    {
        $vistas = [];

        foreach ($asignaciones as $indice => $asignacion) {
            $clave = ((int) $asignacion['id_menu']) . '-' . ((int) $asignacion['id_permiso']);

            if (isset($vistas[$clave])) {
                $validator->errors()->add(
                    "asignaciones.{$indice}.id_permiso",
                    'La combinación de permiso y menú está repetida en la solicitud.'
                );
            }

            $vistas[$clave] = true;
        }

        $catalogos = [
            'id_permiso' => [PermisoModel::class, 'id_permiso', 'El permiso seleccionado no existe.'],
            'id_menu' => [MenuModel::class, 'id_menu', 'El menú seleccionado no existe.'],
        ];

        foreach ($catalogos as $campo => [$modelo, $columna, $mensaje]) {
            $solicitados = array_values(array_unique(array_map(fn ($a) => (int) $a[$campo], $asignaciones)));

            $existentes = array_map('intval', $modelo::query()->whereIn($columna, $solicitados)->pluck($columna)->all());
            $inexistentes = array_diff($solicitados, $existentes);

            foreach ($asignaciones as $indice => $asignacion) {
                if (in_array((int) $asignacion[$campo], $inexistentes, true)) {
                    $validator->errors()->add("asignaciones.{$indice}.{$campo}", $mensaje);
                }
            }
        }
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
            'asignaciones.required' => 'Debes enviar al menos una asignación.',
            'asignaciones.array' => 'Las asignaciones deben enviarse como una lista.',
            'asignaciones.min' => 'Debes enviar al menos una asignación.',
            'asignaciones.max' => 'No se pueden sincronizar más de ' . self::MAXIMO_SINCRONIZACION . ' asignaciones a la vez.',
            'asignaciones.*.id_permiso.required' => 'Cada asignación requiere un permiso.',
            'asignaciones.*.id_permiso.integer' => 'El permiso de cada asignación debe ser numérico.',
            'asignaciones.*.id_menu.required' => 'Cada asignación requiere un menú.',
            'asignaciones.*.id_menu.integer' => 'El menú de cada asignación debe ser numérico.',
            'asignaciones.*.activo.required' => 'Cada asignación debe indicar si está activa.',
            'asignaciones.*.activo.boolean' => 'El campo activo de cada asignación debe ser verdadero o falso.',
        ];
    }
}
