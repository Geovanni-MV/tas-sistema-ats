<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.modalidades.
 */
class ModalidadModel extends Model
{
    protected $table = 'catalogos.modalidades';
    protected $primaryKey = 'id_modalidad';
    public $timestamps = false;

    protected $fillable = [
        'nombre_modalidad',
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
