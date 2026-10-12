<?php

namespace App\Validators\Seguridad;

use App\Models\Seguridad\MenuModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación de la entidad seguridad.menu
 */
final class MenuValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'id_menu_padre' => $this->reglasPadre(),
            'es_padre' => ['sometimes', 'boolean'],
            'es_colapsable' => ['sometimes', 'boolean'],
            'nombre_menu' => $this->reglasNombre(),
            'icono_clase' => ['nullable', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'ruta' => ['nullable', 'string', 'max:150'],
        ], $this->mensajes())->validate();
    }

    /**
     * El campo "activo" no se modifica aquí; para eso existe validateStatus().
     *
     * @throws ValidationException
     */
    public function validateUpdate(array $data): array
    {
        $id_menu = (int) ($data['id_menu'] ?? 0);

        return Validator::make($data, [
            'id_menu' => ['required', 'integer', 'min:1'],
            'id_menu_padre' => $this->reglasPadre($id_menu),
            'es_padre' => ['sometimes', 'boolean', $this->padreConHijos($id_menu)],
            'es_colapsable' => ['sometimes', 'boolean'],
            'nombre_menu' => $this->reglasNombre($id_menu),
            'icono_clase' => ['nullable', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'ruta' => ['nullable', 'string', 'max:150'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_menu' => ['required', 'integer', 'min:1'],
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

    private function reglasPadre(?int $id_excluido = null): array
    {
        return [
            'bail',
            'nullable',
            'integer',
            'min:1',
            Rule::exists(MenuModel::class, 'id_menu'),
            $this->padreValido($id_excluido),
        ];
    }

    /**
     * Comparación sin distinguir mayúsculas/minúsculas ni espacios en los extremos.
     */
    private function nombreNoDuplicado(?int $id_excluido): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($id_excluido): void {
            $nombre_menu = mb_strtolower(trim((string) $valor));

            $existe = MenuModel::query()
                ->whereRaw('LOWER(TRIM(nombre_menu)) = ?', [$nombre_menu])
                ->when($id_excluido !== null, fn ($query) => $query->where('id_menu', '<>', $id_excluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un menú con el mismo nombre.');
            }
        };
    }

    /**
     * El padre debe estar marcado como "es_padre" y no puede generarse un ciclo
     * (un menú no puede ser su propio padre ni colgar de uno de sus descendientes).
     */
    private function padreValido(?int $id_excluido): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($id_excluido): void {
            if ($valor === null || $valor === '') {
                return;
            }

            $id_padre = (int) $valor;

            if ($id_excluido !== null && $id_padre === $id_excluido) {
                $fail('Un menú no puede ser su propio padre.');
                return;
            }

            $padre = MenuModel::query()->find($id_padre);

            if ($padre === null) {
                return;
            }

            if (! $padre->es_padre) {
                $fail('El menú seleccionado como padre no está marcado como padre.');
                return;
            }

            if ($id_excluido === null) {
                return;
            }

            $visitados = [];
            $actual = $padre;

            while ($actual?->id_menu_padre !== null && ! in_array($actual->id_menu, $visitados, true)) {
                if ((int) $actual->id_menu_padre === $id_excluido) {
                    $fail('El menú padre seleccionado es descendiente de este menú.');
                    return;
                }

                $visitados[] = $actual->id_menu;
                $actual = MenuModel::query()->find($actual->id_menu_padre);
            }
        };
    }

    /**
     * No se puede quitar la marca "es_padre" a un menú que todavía tiene hijos.
     */
    private function padreConHijos(int $id_menu): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($id_menu): void {
            if (filter_var($valor, FILTER_VALIDATE_BOOLEAN)) {
                return;
            }

            if (MenuModel::query()->where('id_menu_padre', $id_menu)->exists()) {
                $fail('No se puede quitar la marca de padre a un menú que tiene submenús.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'id_menu_padre.exists' => 'El menú padre seleccionado no existe.',
            'nombre_menu.required' => 'El nombre del menú es obligatorio.',
            'nombre_menu.string' => 'El nombre del menú debe ser texto.',
            'nombre_menu.max' => 'El nombre no puede exceder 150 caracteres.',
            'icono_clase.max' => 'La clase del icono no puede exceder 150 caracteres.',
            'orden.integer' => 'El orden debe ser un número entero.',
            'orden.min' => 'El orden no puede ser negativo.',
            'ruta.max' => 'La ruta no puede exceder 150 caracteres.',
        ];
    }
}
