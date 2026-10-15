<?php

namespace App\Validators\Seguridad;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthValidator
{
    /**
     * Valida los datos requeridos para iniciar sesión
     * @throws ValidationException
     */
    public function validarLogin(array $data): array
    {
        return Validator::make($data,
            [
                'correo_electronico'=>[
                    'required',
                    'email'
                ],
                'clave'=>[
                    'required',
                    'string'
                ]
            ],
            [
                'correo_electronico.required' => 'El correo electrónico es obligatorio.',
                'correo_electronico.email' => 'El correo electrónico no tiene un formato válido.',
                'correo_electronico.max' => 'El correo electrónico no puede superar los 150 caracteres.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.string' => 'La contraseña debe ser una cadena de texto.',
            ]
        )->Validate();
    }
}
?>