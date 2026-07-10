<?php

namespace App\Models;

use App\Services\InstrumentoControlService;
use App\Services\ReglasDocumentalesService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caratula extends Model
{
    use HasFactory;

    protected $table = 'caratulas';

    protected $fillable = [
        'id_dependencia',
        'id_departamento',
        'id_instrumento_control',
        'nombre_sujeto_obligado',
        'area_administrativa',
        'no_consecutivo',
        'area_generadora',
        'codigo_clasificacion_archivistica',
        'anio_apertura',
        'anio_cierre',
        'soporte_documental',
        'titulo_expediente',
        'descripcion_asunto_expediente',
        'ubicacion_topografica',
        'no_legajo',
        'no_fojas',
        'fecha_clasificacion',
        'aplicacion_periodo_reserva',
        'rubrica_titular',
        'fecha_desclasificacion',
        'observaciones',
        'capturador',
    ];

    protected $appends = [
        'valor_documental',
        'valor_documental_administrativo',
        'valor_documental_legal',
        'valor_documental_contable',
        'vigencia_documental',
        'anios_archivo_tramite',
        'anios_archivo_concentracion',
        'caracter_informacion',
        'fundamento_legal',
        'periodo_reserva',
    ];

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function instrumentoControl()
    {
        return $this->belongsTo(InstrumentoControl::class, 'id_instrumento_control');
    }

    public function resolverInstrumento(): ?InstrumentoControl
    {
        if ($this->relationLoaded('instrumentoControl') && $this->instrumentoControl) {
            return $this->instrumentoControl;
        }

        if ($this->id_instrumento_control) {
            return InstrumentoControl::with('serie')->find($this->id_instrumento_control);
        }

        return InstrumentoControlService::resolverPorExpedienteClave($this->codigo_clasificacion_archivistica);
    }

    public function getValorDocumentalAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['valor_documental'];
    }

    public function getValorDocumentalAdministrativoAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['valor_documental_administrativo'];
    }

    public function getValorDocumentalLegalAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['valor_documental_legal'];
    }

    public function getValorDocumentalContableAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['valor_documental_contable'];
    }

    public function getVigenciaDocumentalAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['vigencia_documental'];
    }

    public function getAniosArchivoTramiteAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['anios_archivo_tramite'];
    }

    public function getAniosArchivoConcentracionAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['anios_archivo_concentracion'];
    }

    public function getFundamentoLegalAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['fundamento_legal'];
    }

    public function getPeriodoReservaAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['periodo_reserva'];
    }

    public function getCaracterInformacionAttribute()
    {
        return ReglasDocumentalesService::reglasCaratulaParaApi($this)['caracter_informacion'];
    }
}
