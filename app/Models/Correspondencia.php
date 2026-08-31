<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Correspondencia extends Model
{
    use SoftDeletes;

    protected $table = 'correspondencias';

    protected $fillable = [
        'id_expediente',
        'numero_consecutivo',
        'id_usuario_autor',
        'remitente',
        'destinatario',
        'asunto',
        'contenido',
        'clasificacion',
        'estado',
        'fecha_creacion',
        'fecha_modificacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_modificacion' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const ESTADO_BORRADOR = 'borrador';
    const ESTADO_FIRMADO = 'firmado';
    const ESTADO_CANCELADO = 'cancelado';

    const CLASIFICACION_PUBLICA = 'publica';
    const CLASIFICACION_RESERVADA = 'reservada';
    const CLASIFICACION_CONFIDENCIAL = 'confidencial';

    // =========================================================================
    // Relaciones
    // =========================================================================

    /**
     * Expediente al que pertenece esta correspondencia
     */
    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente');
    }

    /**
     * Usuario que creó/redactó el documento
     */
    public function usuarioAutor()
    {
        return $this->belongsTo(User::class, 'id_usuario_autor');
    }

    /**
     * Firma electrónica SAT asociada
     */
    public function firma()
    {
        return $this->hasOne(CorrespondenciaFirma::class, 'id_correspondencia');
    }

    /**
     * Todas las firmas (histórico de intentos)
     */
    public function firmas()
    {
        return $this->hasMany(CorrespondenciaFirma::class, 'id_correspondencia');
    }

    /**
     * Anexos PDF (unidades documentales)
     */
    public function anexos()
    {
        return $this->hasMany(CorrespondenciaAnexo::class, 'id_correspondencia');
    }

    /**
     * Auditoría de acciones
     */
    public function auditorias()
    {
        return $this->hasMany(CorrespondenciaAuditoria::class, 'id_correspondencia');
    }

    // =========================================================================
    // Métodos de negocio
    // =========================================================================

    /**
     * Verificar si el documento está en estado borrador (editable)
     */
    public function esEditable(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR;
    }

    /**
     * Verificar si el documento está firmado
     */
    public function estaFirmado(): bool
    {
        return $this->estado === self::ESTADO_FIRMADO;
    }

    /**
     * Verificar si tiene firma válida
     */
    public function tieneFirmaValida(): bool
    {
        $firma = $this->firma;
        return $firma && $firma->estado_firma === CorrespondenciaFirma::ESTADO_EXITOSA;
    }

    /**
     * Cambiar estado a firmado
     */
    public function marcarComoFirmado(): bool
    {
        $this->estado = self::ESTADO_FIRMADO;
        return $this->save();
    }

    /**
     * Cambiar estado a cancelado
     */
    public function cancelar(): bool
    {
        $this->estado = self::ESTADO_CANCELADO;
        return $this->save();
    }

    /**
     * Obtener siguiente número consecutivo para el expediente
     */
    public static function obtenerProximoConsecutivo(int $idExpediente): int
    {
        $ultimoConsecutivo = self::where('id_expediente', $idExpediente)
            ->max('numero_consecutivo');
        return ($ultimoConsecutivo ?? 0) + 1;
    }
}
