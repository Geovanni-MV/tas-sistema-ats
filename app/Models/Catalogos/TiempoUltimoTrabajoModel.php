<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.tiempos_ultimo_trabajo.
 */
class TiempoUltimoTrabajoModel extends Model
{
    protected $table = 'catalogos.tiempos_ultimo_trabajo';
    protected $primaryKey = 'id_tiempo_ultimo_trabajo';
    
    public $timestamps = true;
    CONST CREATED_AT = 'fecha_registro';
    CONST UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre_tiempo_ultimo_trabajo',
        'descripcion',
        'fecha_actualizacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];
}
