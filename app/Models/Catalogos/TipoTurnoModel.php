<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.tipos_turno.
 */
class TipoTurnoModel extends Model
{
    protected $table = 'catalogos.tipos_turno';
    protected $primaryKey = 'id_tipo_turno';
    public $timestamps = false;

    protected $fillable = [
        'nombre_tipo_turno',
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
