<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.estados.
 */
class Estado extends Model
{
    protected $table = 'catalogos.estados';
    protected $primaryKey = 'id_estado';

    public $timestamps = false;

    protected $fillable = [
        'nombre_estado',
        'clave_estado',
        'fecha_actualizacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];
}
