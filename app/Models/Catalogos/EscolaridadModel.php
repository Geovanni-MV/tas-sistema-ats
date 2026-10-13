<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.escolaridades.
 */
class EscolaridadModel extends Model
{
    protected $table = 'catalogos.escolaridades';
    protected $primaryKey = 'id_escolaridad';
    public $timestamps = false;

    protected $fillable = [
        'nombre_escolaridad',
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
