<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODELO DE LA ENTIDAD seguridad.permisos_roles
 * Asigna un permiso a un rol sobre una opción del menú.
 */
class PermisoRolModel extends Model
{
    protected $table = 'seguridad.permisos_roles';
    protected $primaryKey = 'id_permiso_rol';
   
    public $timestamps = true;
    CONST CREATED_AT = 'fecha_registro';
    CONST UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_permiso',
        'id_rol',
        'id_menu',
        'fecha_actualizacion',
        'activo',
    ];

    protected $casts = [
        'id_permiso' => 'integer',
        'id_rol' => 'integer',
        'id_menu' => 'integer',
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function permiso(): BelongsTo
    {
        return $this->belongsTo(PermisoModel::class, 'id_permiso', 'id_permiso');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolModel::class, 'id_rol', 'id_rol');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(MenuModel::class, 'id_menu', 'id_menu');
    }
}
