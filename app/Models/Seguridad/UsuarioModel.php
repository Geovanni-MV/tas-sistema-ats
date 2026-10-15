<?php

namespace App\Models\Seguridad;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Seguridad\RolModel;
use App\Models\Reclutamiento\EmpresasModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UsuarioModel extends Authenticatable
{
    protected $table = 'seguridad.usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = true;
    CONST CREATED_AT = 'fecha_registro';
    CONST UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'nombres',
        'apellidos',
        'telefono',
        'correo_electronico',
        'id_empresa',
        'id_rol',
        'clave_hash',
    ];

    protected $hidden = [
        'clave_hash',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    /**
     * Indica a Laravel qué columna contiene
     * la contraseña del usuario.
     */
    public function getAuthPassword(): string
    {
        return $this->clave_hash;
    }
    /**
     * 
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolModel::class, 'id_rol', 'id_rol');
    }
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(EmpresasModel::class, 'id_empresa', 'id_empresa');
    }
    /**
     * Obtencion del menu
     */
    public function obtenerMenus()
    {
        return MenuModel::query()
            ->select(
                'seguridad.menu.id_menu',
                'seguridad.menu.id_menu_padre',
                'seguridad.menu.nombre_menu',
                'seguridad.menu.ruta',
                'seguridad.menu.icono_clase',
                'seguridad.menu.orden',
                'seguridad.menu.es_padre',
                'seguridad.menu.es_colapsable'
            )
            ->join(
                'seguridad.permisos_roles',
                'seguridad.permisos_roles.id_menu',
                '=',
                'seguridad.menu.id_menu'
            )
            ->where(
                'seguridad.permisos_roles.id_rol',
                $this->id_rol
            )
            ->where(
                'seguridad.permisos_roles.activo',
                true
            )
            ->where(
                'seguridad.menu.activo',
                true
            )
            ->distinct()
            ->orderBy(
                'seguridad.menu.orden'
            )
            ->get();
    }
}