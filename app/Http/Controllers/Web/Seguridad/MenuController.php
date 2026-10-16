<?php
namespace App\Http\Controllers\Web\Seguridad;

//Imports obligatorios
use Illuminate\View\View;
use Override;
use Illuminate\Support\Facades\Auth;
use App\Services\Seguridad\MenuService;
use App\Models\Seguridad\UsuarioModel;
use App\Http\Controllers\Web\ControllerWebBase;

final class MenuController extends ControllerWebBase
{
    public function __construct(
        protected readonly MenuService $menu_service
    ){}
    /**
     * Renderiza únicamente la vista de la entidad
     * Los datos dinamicos se obtienen posteriormente la API REST.
     * Los datos estaticos se obtienen en esta funcion y se mandan por params
     */
    public function renderView(): View
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
        ];
        return view('modulos.seguridad.menu.index', compact('data'));
    }
}
?>