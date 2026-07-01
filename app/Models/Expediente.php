<?php

namespace App\Models;

use App\Services\ReglasDocumentalesService;
use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    protected $table = 'expedientes';

    protected $fillable = [
        'nombre',
        'codigo',
        'clave',
        'id_serie',
        'id_subserie',
        'id_instrumento_control',
        'estado',
        'fecha_apertura',
        'fecha_cierre',
        'fecha_creacion',
        'no_legajos',
        'no_hojas',
        'no_caja',
        'ubicacion_topografica',
        'preservacion',
        'observacion',
        'informacion_reservada_y_o_confidencial',
        'fecha_clasificacion',
        'rubrica_titular_de_la_unidad',
        'fecha_desclasificacion',
        'id_dependencia',
        'id_departamento',
    ];

    public function serie()
    {
        return $this->belongsTo(Serie::class, 'id_serie');
    }

    public function subserie()
    {
        return $this->belongsTo(Subserie::class, 'id_subserie');
    }

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

    public function unidadesDocumentales()
    {
        return $this->hasMany(UnidadDocumental::class, 'id_expediente');
    }

    public function getTiempoConservacionAttribute()
    {
        return $this->serie?->plazo_ta;
    }

    public function getVigenciaAttribute()
    {
        return $this->serie?->plazo_at;
    }

    public function getClasificacionAttribute()
    {
        return ReglasDocumentalesService::clasificacionDesdeSerie($this->serie);
    }

    public function getCaracterAttribute()
    {
        return ReglasDocumentalesService::caracterDesdeSerie($this->serie);
    }

    public function getFundamentoLegalAttribute()
    {
        return $this->instrumentoControl?->fundamento_legal;
    }

    public function getPeriodoReservaAttribute()
    {
        return $this->instrumentoControl?->periodo_reserva;
    }

    public function getAmpliacionPeriodoReservaAttribute()
    {
        return null;
    }
}
