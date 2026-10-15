<?php

namespace App\Http\Controllers\Web\Seguridad;

use App\DTO\Seguridad\LoginDTO;
use App\Http\Controllers\Controller;
use App\Services\Seguridad\AuthService;
use App\Validators\Seguridad\AuthValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $service,
        private readonly AuthValidator $validator
    ) {}

    /**
     * Renderiza el formulario
     */
    public function renderView(): View
    {
        return view('auth.login');
    }
    /**
     * Procesa el formulario deinicio de sesión
     */
    public function login(Request $request): RedirectResponse
    {
        try{
            $datos = $this->validator->validarLogin($request->all());
            $dto = new LoginDTO(
                correo_electronico: $datos['correo_electronico'],
                clave: $datos['clave']
            );
            $this->service->login($dto, $request);
            return redirect()
                ->route('sistema-ats-tas.dashboard');
        }catch(ValidationException $e){
            throw $e;
        }catch(RuntimeException $e){
            return back()
                ->withInput($request->only('correo_electronico'))
                ->withErrors([
                    'login'=>$e->getMessage()
                ]);
        }catch(Throwable $e){
            return back()
                ->withInput($request->only('correo_electronico'))
                ->withErrors([
                    'login'=>'Ocurió un error interno al iniciar sesión.',
                ]);
        }
    }
    /**
     * Cierra la sesión y regresa al formulario de login
     */
    public function logout(Request  $request): RedirectResponse
    {
        $this->service->logout($request);
        return redirect()
            ->route('login');
    }

}