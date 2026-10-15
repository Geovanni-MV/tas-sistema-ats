<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Seguridad\MenuService;

abstract class ControllerWebBase extends Controller
{
    public function __construct(
        protected readonly MenuService $menu_service
    ){}
    /**
     * Genera las variables comunes
     * para la vista blade
     */
    protected function generarDatView(array $datos_adicionales = []): array
    {
        $usuario = auth()->user();
        if($usuario){
            $usuario->loadMissing(
                'rol:id_rol,nombre_rol',
                'empresa:id_empresa,nombre_empresa'
            );
        }

        $data_base = [
            'usuario'=>$usuario,
            'menu'=>$usuario ? $this->menu_service->obtenerMenusUsuario($usuario) : [],
        ];

        return array_merge(
            $data_base,
            $datos_adicionales
        );
    }
}
?>