<?php

namespace App\Models\Seguridad;

use App\Models\Reclutamiento\EmpresasModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODELO DE LA ENTIDAD seguridad.usuarios
 */
class UsuarioModel extends Model
{
    protected $table = 'seguridad.usuarios';
    protected $primaryKey = 'id_usuario';

    /**
     * fecha_registro la asigna la base de datos (useCurrent) y
     * fecha_actualizacion solo se llena cuando el registro se modifica,
     * por eso se gestionan manualmente desde el Service.
     */
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
        'fecha_actualizacion',
        'activo',
    ];

    /** Nunca se expone el hash de la clave en las respuestas JSON. */
    protected $hidden = ['clave_hash'];

    protected $casts = [
        'id_empresa' => 'integer',
        'id_rol' => 'integer',
        'activo' => 'boolean',
        'fecha_registro' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolModel::class, 'id_rol', 'id_rol');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(EmpresasModel::class, 'id_empresa', 'id_empresa');
    }
}
