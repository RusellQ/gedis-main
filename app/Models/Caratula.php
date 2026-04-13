<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caratula extends Model
{
    use HasFactory;

    protected $table = 'caratulas';

    protected $fillable = [
        'id_dependencia',               
        'id_departamento',              
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
        'valor_documental_administrativo',
        'valor_documental_legal',
        'valor_documental_contable',
        'vigencia_documental',
        'anios_archivo_tramite',
        'anios_archivo_concentracion',
        'informacion_reservada',
        'caracter_informacion',
        'fecha_clasificacion',
        'fundamento_legal',
        'periodo_reserva',
        'aplicacion_periodo_reserva',
        'rubrica_titular',
        'fecha_desclasificacion',
        'observaciones',
        'capturador',
    ];

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }
}
