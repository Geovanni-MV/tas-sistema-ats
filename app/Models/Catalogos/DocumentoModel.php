<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.documentos.
 */
class DocumentoModel extends Model
{
    protected $table = 'catalogos.documentos';
    protected $primaryKey = 'id_documento';
    public $timestamps = true;
    CONST CREATED_AT = 'fecha_registro';
    CONST UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre_documento',
        'requiere_ambos_lados',
        'maximo_archivos',
        'descripcion',
        'fecha_actualizacion',
        'activo',
    ];

    protected $casts = [
        'requiere_ambos_lados' => 'boolean',
        'maximo_archivos' => 'integer',
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];
}
