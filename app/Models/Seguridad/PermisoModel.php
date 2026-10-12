<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODELO DE LA ENTIDAD seguridad.permisos
 */
class PermisoModel extends Model
{
    protected $table = 'seguridad.permisos';
    protected $primaryKey = 'id_permiso';

    public $timestamps = true;
    CONST CREATED_AT = 'fecha_registro';
    CONST UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre_permiso',
        'descripcion',
        'fecha_actualizacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function permisosRoles(): HasMany
    {
        return $this->hasMany(PermisoRolModel::class, 'id_permiso', 'id_permiso');
    }
}
