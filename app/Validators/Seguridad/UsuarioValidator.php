<?php

namespace App\Validators\Seguridad;

use App\Models\Seguridad\UsuarioModel;
use App\Models\Seguridad\RolModel;
use App\Models\Reclutamiento\EmpresasModel;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza todas las reglas de validación de la entidad seguridad.usuarios
 */
final class UsuarioValidator
{
    /** @throws ValidationException */
    public function validateCreate(array $data): array
    {
        return Validator::make($data, [
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'telefono' => $this->reglasTelefono(),
            'correo_electronico' => $this->reglasCorreo(),
            'id_empresa' => $this->reglasEmpresa(),
            'id_rol' => $this->reglasRol(),
            'clave' => $this->reglasClave(),
        ], $this->mensajes())->validate();
    }

    /**
     * En la actualización no se modifican "activo" (validateStatus) ni la
     * clave (validateClave).
     *
     * @throws ValidationException
     */
    public function validateUpdate(array $data): array
    {
        return Validator::make($data, [
            'id_usuario' => ['required', 'integer', 'min:1'],
            'nombres' => ['required', 'string', 'max:150'],
            'apellidos' => ['required', 'string', 'max:150'],
            'telefono' => $this->reglasTelefono(),
            'correo_electronico' => $this->reglasCorreo((int) ($data['id_usuario'] ?? 0)),
            'id_empresa' => $this->reglasEmpresa(),
            'id_rol' => $this->reglasRol(),
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateClave(array $data): array
    {
        return Validator::make($data, [
            'id_usuario' => ['required', 'integer', 'min:1'],
            'clave' => $this->reglasClave(),
        ], $this->mensajes())->validate();
    }

    /** @throws ValidationException */
    public function validateStatus(array $data): array
    {
        return Validator::make($data, [
            'id_usuario' => ['required', 'integer', 'min:1'],
        ])->validate();
    }

    private function reglasClave(): array
    {
        return ['required', 'string', 'min:8', 'max:72'];
    }

    private function reglasTelefono(): array
    {
        return ['required', 'string', 'max:15', 'regex:/^\+?[0-9]{7,14}$/'];
    }

    private function reglasCorreo(?int $id_excluido = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:150',
            $this->correoNoDuplicado($id_excluido),
        ];
    }

    private function reglasEmpresa(): array
    {
        return ['nullable', 'integer', 'min:1', Rule::exists(EmpresasModel::class, 'id_empresa')];
    }

    private function reglasRol(): array
    {
        return ['nullable', 'integer', 'min:1', Rule::exists(RolModel::class, 'id_rol')];
    }

    /**
     * Comparación sin distinguir mayúsculas/minúsculas ni espacios en los extremos.
     */
    private function correoNoDuplicado(?int $id_excluido): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fail) use ($id_excluido): void {
            $correo = mb_strtolower(trim((string) $valor));

            $existe = UsuarioModel::query()
                ->whereRaw('LOWER(TRIM(correo_electronico)) = ?', [$correo])
                ->when($id_excluido !== null, fn ($query) => $query->where('id_usuario', '<>', $id_excluido))
                ->exists();

            if ($existe) {
                $fail('Ya existe un usuario con el mismo correo electrónico.');
            }
        };
    }

    private function mensajes(): array
    {
        return [
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden exceder 150 caracteres.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.max' => 'Los apellidos no pueden exceder 150 caracteres.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.max' => 'El teléfono no puede exceder 15 caracteres.',
            'telefono.regex' => 'El teléfono debe contener entre 7 y 14 dígitos y puede iniciar con "+".',
            'correo_electronico.required' => 'El correo electrónico es obligatorio.',
            'correo_electronico.email' => 'El correo electrónico no tiene un formato válido.',
            'correo_electronico.max' => 'El correo electrónico no puede exceder 150 caracteres.',
            'id_empresa.exists' => 'La empresa seleccionada no existe.',
            'id_rol.exists' => 'El rol seleccionado no existe.',
            'clave.required' => 'La clave es obligatoria.',
            'clave.min' => 'La clave debe tener al menos 8 caracteres.',
            'clave.max' => 'La clave no puede exceder 72 caracteres.',
        ];
    }
}
