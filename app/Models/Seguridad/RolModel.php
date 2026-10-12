<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODELO DE LA ENTIDAD seguridad.roles
 */
class RolModel extends Model
{
    protected $table = 'seguridad.roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = true;

    CONST CREATED_AT = 'fecha_registro';
    CONST UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombre_rol',
        'descripcion',
        'fecha_actualizacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(UsuarioModel::class, 'id_rol', 'id_rol');
    }

    public function permisosRoles(): HasMany
    {
        return $this->hasMany(PermisoRolModel::class, 'id_rol', 'id_rol');
    }
}
