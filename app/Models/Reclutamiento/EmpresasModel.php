<?php

namespace App\Models\Reclutamiento;

use Illuminate\Database\Eloquent\Model;
/**
 * MODELO DE LA ENTIDAD reclutamiento.empresas
 */
class EmpresasModel extends Model
{
    protected $table = 'reclutamiento.empresas';
    protected $primaryKey = 'id_empresa';
    
    CONST CREATED_AT = 'fecha_registro';
    CONST UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre_empresa',
        'img_logo_url',
        'fecha_actualizacion',
        'activo'
    ];

    protected $cast = [
        'activo' => 'boolean',
        'fecha_registro' => 'timestamps',
        'fecha_actualizacion' => 'timestamps',
    ];
}
 