<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODELO DE LA ENTIDAD seguridad.menu
 */
class MenuModel extends Model
{
    protected $table = 'seguridad.menu';
    protected $primaryKey = 'id_menu';
    
    public $timestamps = true;
    CONST CREATED_AT = 'fecha_registro';
    CONST UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_menu_padre',
        'es_padre',
        'es_colapsable',
        'nombre_menu',
        'icono_clase',
        'descripcion',
        'orden',
        'ruta',
        'fecha_actualizacion',
        'activo',
    ];

    protected $casts = [
        'id_menu_padre' => 'integer',
        'es_padre' => 'boolean',
        'es_colapsable' => 'boolean',
        'orden' => 'integer',
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_menu_padre', 'id_menu');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(self::class, 'id_menu_padre', 'id_menu');
    }
}
