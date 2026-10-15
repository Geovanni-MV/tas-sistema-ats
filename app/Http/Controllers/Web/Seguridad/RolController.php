<?php
namespace App\Http\Controllers\Web\Seguridad;
use Illuminate\View\View;
use App\Http\Controllers\Web\ControllerWebBase;

final class RolController extends ControllerWebBase
{
    
    /**
     * Renderiza únicamente la vista de la entidad
     * Los datos dinamicos se obtienen posteriormente la API REST.
     * Los datos estaticos se obtienen en esta funcion y se mandan por params
     */
    public function renderView(): View
    {
        $data = $this->generarDatView();
        return view('modulos.seguridad.roles.index', compact('data'));
    }
}
?>