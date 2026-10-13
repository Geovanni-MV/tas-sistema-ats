<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.estados_civiles.
 */
class EstadoCivilModel extends Model
{
    protected $table = 'catalogos.estados_civiles';
    protected $primaryKey = 'id_estado_civil';
    public $timestamps = false;

    protected $fillable = [
        'nombre_estado_civil',
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
