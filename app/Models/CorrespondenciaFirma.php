<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenciaFirma extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia_firmas';

    protected $fillable = [
        'id_correspondencia',
        'id_usuario_firmante',
        'certificado_sat',
        'timestamp_firma',
        'estado_firma',
        'respuesta_sat',
        'fecha_intento',
    ];

    protected $casts = [
        'respuesta_sat' => 'array',
        'timestamp_firma' => 'datetime',
        'fecha_intento' => 'datetime',
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EXITOSA = 'exitosa';
    const ESTADO_RECHAZADA = 'rechazada';

    // =========================================================================
    // Relaciones
    // =========================================================================

    /**
     * Correspondencia que se firmo
     */
    public function correspondencia()
    {
        return $this->belongsTo(Correspondencia::class, 'id_correspondencia');
    }

    /**
     * Usuario que realizó la firma
     */
    public function usuarioFirmante()
    {
        return $this->belongsTo(User::class, 'id_usuario_firmante');
    }

    // =========================================================================
    // Métodos de negocio
    // =========================================================================

    /**
     * Verificar si la firma fue exitosa
     */
    public function esExitosa(): bool
    {
        return $this->estado_firma === self::ESTADO_EXITOSA;
    }

    /**
     * Verificar si está pendiente
     */
    public function estaPendiente(): bool
    {
        return $this->estado_firma === self::ESTADO_PENDIENTE;
    }
}
