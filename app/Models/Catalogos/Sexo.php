<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.sexo.
 */
class Sexo extends Model
{
    protected $table = 'catalogos.sexo';
    protected $primaryKey = 'id_sexo';
    public $timestamps = false;

    protected $fillable = ['nombre_sexo', 'descripcion', 'fecha_actualizacion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];
}
