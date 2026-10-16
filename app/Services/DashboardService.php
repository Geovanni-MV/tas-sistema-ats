<?php
namespace App\Services;
//Importación de modelos

final class DashboardService
{
    public function dataModulo(): array
    {
        $datos = [
            'resumen'=>[
                'vacantes_activas'=>12,
                'candidatos'=>184,
                'en_proceso'=>46,
                'contrataciones'=>18
            ]
        ];
        return $datos;
    }
}
?>