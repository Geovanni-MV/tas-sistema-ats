<?php
namespace App\Http\Controllers\Web;

use App\Services\DashboardService;

//Imports obligatorios
use Illuminate\View\View;
use Override;
use Illuminate\Support\Facades\Auth;
use App\Services\Seguridad\MenuService;
use App\Models\Seguridad\UsuarioModel;
final class DashboardController extends ControllerWebBase
{
    
    public function __construct(
        protected readonly DashboardService $service,
        protected readonly MenuService $menu_service

        )
    {
    }

    public function renderView(): view
    {
        //Pasara por un middleware de Autentificacion, esta variable obligatoriamente  debera contener al usuario
        /** @var UsuarioModel $usuario */
        $usuario = Auth::user();

        if ($usuario) {
            $usuario->loadMissing(
                'rol:id_rol,nombre_rol',
                'empresa:id_empresa,nombre_empresa'
            );
        }
        //Validacion de Permisos
        /*if($this->puede('ver', $usuario)){

        }*/
        $data = [
            'layout_data'=>[
                'usuario'=>$usuario ?? [],
                'menu'=>$this->menu_service->obtenerMenusUsuario($usuario) ?? [],
            ],
            
            'data_modulo'=>$this->service->dataModulo()
        ];
        
        return view(
            'modulos.dashboard.index',
            compact('data')
        );
    }
}
?>