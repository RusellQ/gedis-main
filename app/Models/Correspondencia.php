<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Correspondencia extends Model
{
    use SoftDeletes;

    protected $table = 'correspondencias';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_modificacion';

    protected $fillable = [
        'id_expediente',
        'id_unidad_documental',
        'numero_consecutivo',
        'numero_oficio',
        'id_usuario_autor',
        'id_usuario_destinatario',
        'remitente',
        'destinatario',
        'asunto',
        'leyenda_anio',
        'fecha_oficio',
        'contenido',
        'clasificacion',
        'estado',
        'remitido_at',
        'archivado_at',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_modificacion' => 'datetime',
        'fecha_oficio' => 'date',
        'remitido_at' => 'datetime',
        'archivado_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const ESTADO_BORRADOR = 'borrador';
    const ESTADO_EMITIDO = 'emitido';
    const ESTADO_REMITIDO = 'remitido';
    const ESTADO_ARCHIVADO = 'archivado';
    const ESTADO_FIRMADO = 'firmado';
    const ESTADO_CANCELADO = 'cancelado';

    const CLASIFICACION_PUBLICA = 'publica';
    const CLASIFICACION_RESERVADA = 'reservada';
    const CLASIFICACION_CONFIDENCIAL = 'confidencial';

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente');
    }

    public function unidadDocumental()
    {
        return $this->belongsTo(UnidadDocumental::class, 'id_unidad_documental');
    }

    public function usuarioAutor()
    {
        return $this->belongsTo(User::class, 'id_usuario_autor');
    }

    public function usuarioDestinatario()
    {
        return $this->belongsTo(User::class, 'id_usuario_destinatario');
    }

    public function firma()
    {
        return $this->hasOne(CorrespondenciaFirma::class, 'id_correspondencia');
    }

    public function firmas()
    {
        return $this->hasMany(CorrespondenciaFirma::class, 'id_correspondencia');
    }

    public function anexos()
    {
        return $this->hasMany(CorrespondenciaAnexo::class, 'id_correspondencia');
    }

    public function auditorias()
    {
        return $this->hasMany(CorrespondenciaAuditoria::class, 'id_correspondencia');
    }

    public function esEditable(): bool
    {
        return $this->estado === self::ESTADO_BORRADOR;
    }

    public function puedeRemitirse(): bool
    {
        return in_array($this->estado, [self::ESTADO_EMITIDO, self::ESTADO_FIRMADO], true)
            && !$this->id_usuario_destinatario;
    }

    public function puedeArchivarse(): bool
    {
        return $this->estado === self::ESTADO_REMITIDO && !$this->id_unidad_documental;
    }

    public function estaFirmado(): bool
    {
        return $this->estado === self::ESTADO_FIRMADO || $this->tieneFirmaValida();
    }

    public function tieneFirmaValida(): bool
    {
        $firma = $this->firma;
        return $firma && $firma->estado_firma === CorrespondenciaFirma::ESTADO_EXITOSA;
    }

    public function marcarComoFirmado(): bool
    {
        $this->estado = self::ESTADO_FIRMADO;
        return $this->save();
    }

    public function cancelar(): bool
    {
        $this->estado = self::ESTADO_CANCELADO;
        return $this->save();
    }

    public static function obtenerProximoConsecutivo(?int $idExpediente): int
    {
        $query = self::query();
        if ($idExpediente) {
            $query->where('id_expediente', $idExpediente);
        } else {
            $query->whereNull('id_expediente');
        }
        $ultimo = $query->max('numero_consecutivo');
        return ((int) $ultimo) + 1;
    }

    public function nombreAutorCompleto(): string
    {
        $u = $this->usuarioAutor;
        if (!$u) {
            return 'Desconocido';
        }
        return trim($u->nombre . ' ' . $u->apellido_paterno . ' ' . ($u->apellido_materno ?? ''));
    }
}
