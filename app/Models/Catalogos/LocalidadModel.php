<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent del esquema PostgreSQL catalogos.localidades.
 */
class LocalidadModel extends Model
{
    protected $table = 'catalogos.localidades';
    protected $primaryKey = 'id_localidad';
    public $timestamps = false;

    protected $fillable = [
        'id_estado',
        'nombre_localidad',
        'codigo_postal',
        'fecha_actualizacion',
        'activo',
    ];

    protected $casts = [
        'id_estado' => 'integer',
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_estado', 'id_estado');
    }
}
