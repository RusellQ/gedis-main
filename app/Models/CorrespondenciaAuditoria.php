<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenciaAuditoria extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia_auditorias';

    protected $fillable = [
        'id_correspondencia',
        'id_usuario',
        'accion',
        'detalles',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Acciones posibles
    const ACCION_CREAR = 'crear';
    const ACCION_EDITAR = 'editar';
    const ACCION_FIRMAR = 'firmar';
    const ACCION_DESCARGAR = 'descargar';
    const ACCION_ELIMINAR = 'eliminar';
    const ACCION_ACCEDER = 'acceder';

    // =========================================================================
    // Relaciones
    // =========================================================================

    /**
     * Correspondencia auditada
     */
    public function correspondencia()
    {
        return $this->belongsTo(Correspondencia::class, 'id_correspondencia');
    }

    /**
     * Usuario que realizó la acción
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
