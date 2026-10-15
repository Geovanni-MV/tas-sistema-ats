<?php

namespace App\Validators\Catalogos;

use App\Models\Catalogos\TiempoUltimoTrabajoModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación del catálogo Tiempo del último trabajo.
 */
final class TiempoUltimoTrabajoValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombre_tiempo_ultimo_trabajo' => $this->reglasNombre(),
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
        $idExcluido = (int) ($data['id_tiempo_ultimo_trabajo'] ?? 0);

        return Validator::make($data, [
            'id_tiempo_ultimo_trabajo' => ['required', 'integer', 'min:1'],
            'nombre_tiempo_ultimo_trabajo' => $this->reglasNombre($idExcluido),
            'descripcion' => ['nullable', 'string'],
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_tiempo_ultimo_trabajo' => ['required', 'integer', 'min:1'],
            
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

            $existe = TiempoUltimoTrabajoModel::query()
                ->whereRaw('LOWER(TRIM(nombre_tiempo_ultimo_trabajo)) = ?', [$nombre])
                ->when($idExcluido, fn ($query) => $query->where('id_tiempo_ultimo_trabajo', '!=', $idExcluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un tiempo del último trabajo con este nombre.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombre_tiempo_ultimo_trabajo.required' => 'El nombre del tiempo del último trabajo es obligatorio.',
            'nombre_tiempo_ultimo_trabajo.string' => 'El nombre del tiempo del último trabajo debe ser texto.',
            'nombre_tiempo_ultimo_trabajo.max' => 'El nombre no puede exceder 150 caracteres.',
        ];
    }
}
