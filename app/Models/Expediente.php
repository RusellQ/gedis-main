<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    protected $table = 'expedientes';

    protected $fillable = [
        'nombre',
        'codigo',
        'clave',
        //'descripcion_expediente',
        'id_serie',
        'id_subserie',
        'estado',
        'fecha_apertura',
        'fecha_cierre',
        'fecha_creacion',
        'tiempo_conservacion',
        'vigencia',
        'no_legajos',
        'no_hojas',
        'no_caja',
        'ubicacion_topografica',
        'clasificacion',
        'caracter',
        'preservacion',
        'observacion',
        'id_dependencia',
        'id_departamento',
        //'tradicion_documental',
        'soporte_documental',
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

    public function unidadesDocumentales()
    {
        return $this->hasMany(UnidadDocumental::class, 'id_expediente');
    }
}
