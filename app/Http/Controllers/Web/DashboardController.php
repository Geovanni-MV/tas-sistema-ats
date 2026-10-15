<?php
namespace App\Http\Controllers\Web;
use Illuminate\View\View;
use App\Services\Seguridad\MenuService;

final class DashboardController extends ControllerWebBase
{

    public function renderView(): view
    {
        $data = $this->generarDatView([
            'resumen'=>[
                'vacantes_activas'=>12,
                'candidatos'=>184,
                'en_proceso'=>46,
                'contrataciones'=>18
            ]
        ]);
        return view(
            'modulos.dashboard.index',
            compact('data')
        );
    }
}
?>