<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.tipos_jornada.
 */
class TipoJornadaModel extends Model
{
    protected $table = 'catalogos.tipos_jornada';
    protected $primaryKey = 'id_tipo_jornada';
    public $timestamps = false;

    protected $fillable = [
        'nombre_tipo_jornada',
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
