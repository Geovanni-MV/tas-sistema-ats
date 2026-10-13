<?php

namespace App\Validators\Catalogos;

use App\Models\Catalogos\Estado;
use App\Models\Catalogos\LocalidadModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación del catálogo Localidad.
 */
final class LocalidadValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'id_estado' => ['required', 'integer', 'min:1', Rule::exists(Estado::class, 'id_estado')],
            'nombre_localidad' => $this->reglasNombre($data),
            'codigo_postal' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'activo' => ['sometimes', 'boolean'],
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
        $idExcluido = (int) ($data['id_localidad'] ?? 0);

        return Validator::make($data, [
            'id_localidad' => ['required', 'integer', 'min:1'],
            'id_estado' => ['required', 'integer', 'min:1', Rule::exists(Estado::class, 'id_estado')],
            'nombre_localidad' => $this->reglasNombre($data, $idExcluido),
            'codigo_postal' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_localidad' => ['required', 'integer', 'min:1'],
            'activo' => ['required', 'boolean'],
        ])->validate();
    }

    /**
     * Reglas del nombre. Si se recibe $idExcluido, ese registro se ignora
     * al buscar duplicados (caso de actualización).
     */
    private function reglasNombre(array $data, ?int $idExcluido = null): array
    {
        return [
            'required',
            'string',
            'max:150',
            $this->localidadNoDuplicada($data, $idExcluido),
        ];
    }

    /**
     * Una localidad no puede repetirse con el mismo nombre y código postal dentro
     * del mismo estado. Comparación sin distinguir mayúsculas/minúsculas ni espacios.
     */
    private function localidadNoDuplicada(array $data, ?int $idExcluido): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($data, $idExcluido): void {
            $idEstado = $data['id_estado'] ?? null;
            $codigoPostal = trim((string) ($data['codigo_postal'] ?? ''));

            if (! ctype_digit((string) $idEstado) || $codigoPostal === '') {
                return;
            }

            $nombre = mb_strtolower(trim((string) $valor));

            $existe = LocalidadModel::query()
                ->where('id_estado', (int) $idEstado)
                ->where('codigo_postal', $codigoPostal)
                ->whereRaw('LOWER(TRIM(nombre_localidad)) = ?', [$nombre])
                ->when($idExcluido, fn ($query) => $query->where('id_localidad', '!=', $idExcluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe una localidad con este nombre y código postal en el estado seleccionado.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'id_estado.required' => 'El estado es obligatorio.',
            'id_estado.exists' => 'El estado seleccionado no existe.',
            'nombre_localidad.required' => 'El nombre de la localidad es obligatorio.',
            'nombre_localidad.string' => 'El nombre de la localidad debe ser texto.',
            'nombre_localidad.max' => 'El nombre no puede exceder 150 caracteres.',
            'codigo_postal.required' => 'El código postal es obligatorio.',
            'codigo_postal.regex' => 'El código postal debe tener 5 dígitos.',
        ];
    }
}
