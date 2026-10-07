<?php

namespace App\Validators\Catalogos;

use App\Models\Catalogos\Estado;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación del catálogo Estado.
 */
final class EstadoValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_estado' => $this->reglasNombre(),
            'clave_estado' => $this->reglasClave(),
            'activo' => ['sometimes', 'boolean'],
        ], $this->mensajes())->validate();
    }

    /**
     * Para actualización se ignora el registro actual al validar duplicidad.
     *
     * @throws ValidationException
     */
    public function validateUpdate(array $data): array
    {
        $idEstado = (int) ($data['id_estado'] ?? 0);

        return Validator::make($data, [
            'id_estado' => ['required', 'integer', 'min:1'],
            'nombre_estado' => $this->reglasNombre($idEstado),
            'clave_estado' => $this->reglasClave($idEstado),
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_estado' => ['required', 'integer', 'min:1'],
            'activo' => ['required', 'boolean'],
        ])->validate();
    }

    private function reglasNombre(?int $idExcluido = null): array
    {
        return [
            'required',
            'string',
            'max:150',
            $this->valorNoDuplicado('nombre_estado', 'nombre', $idExcluido),
        ];
    }

    private function reglasClave(?int $idExcluido = null): array
    {
        return [
            'required',
            'string',
            'max:10',
            $this->valorNoDuplicado('clave_estado', 'clave', $idExcluido),
        ];
    }

    /**
     * Valida duplicados sin distinguir mayúsculas/minúsculas
     * ni espacios al inicio o final.
     */
    private function valorNoDuplicado(
        string $columna,
        string $etiqueta,
        ?int $idExcluido = null,
    ): Closure {
        return function (
            string $atributo,
            mixed $valor,
            Closure $fail
        ) use ($columna, $etiqueta, $idExcluido): void {
            $normalizado = mb_strtolower(trim((string) $valor));

            $existe = Estado::query()
                ->whereRaw("LOWER(TRIM({$columna})) = ?", [$normalizado])
                ->when(
                    $idExcluido,
                    fn ($query) => $query->where('id_estado', '!=', $idExcluido),
                )
                ->exists();

            if ($existe) {
                $fail("Ya existe un estado con esta {$etiqueta}.");
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_estado.required' => 'El nombre del estado es obligatorio.',
            'nombre_estado.string' => 'El nombre del estado debe ser texto.',
            'nombre_estado.max' => 'El nombre no puede exceder 150 caracteres.',
            'clave_estado.required' => 'La clave del estado es obligatoria.',
            'clave_estado.string' => 'La clave del estado debe ser texto.',
            'clave_estado.max' => 'La clave no puede exceder 10 caracteres.',
        ];
    }
}
