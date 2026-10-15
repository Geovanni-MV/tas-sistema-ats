<?php

namespace App\Validators\Catalogos;

use App\Models\Catalogos\TipoJornadaModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación del catálogo Tipo de jornada.
 */
final class TipoJornadaValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_tipo_jornada' => $this->reglasNombre(),
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
        $idExcluido = (int) ($data['id_tipo_jornada'] ?? 0);

        return Validator::make($data, [
            'id_tipo_jornada' => ['required', 'integer', 'min:1'],
            'nombre_tipo_jornada' => $this->reglasNombre($idExcluido),
            'descripcion' => ['nullable', 'string'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_tipo_jornada' => ['required', 'integer', 'min:1'],
            
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

            $existe = TipoJornadaModel::query()
                ->whereRaw('LOWER(TRIM(nombre_tipo_jornada)) = ?', [$nombre])
                ->when($idExcluido, fn ($query) => $query->where('id_tipo_jornada', '!=', $idExcluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un tipo de jornada con este nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_tipo_jornada.required' => 'El nombre del tipo de jornada es obligatorio.',
            'nombre_tipo_jornada.string' => 'El nombre del tipo de jornada debe ser texto.',
            'nombre_tipo_jornada.max' => 'El nombre no puede exceder 150 caracteres.',
        ];
    }
}
