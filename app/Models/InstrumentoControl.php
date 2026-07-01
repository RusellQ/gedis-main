<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstrumentoControl extends Model
{
    protected $table = 'instrumentos_control';

    protected $fillable = [
        'id_padre',
        'id_dependencia',
        'id_departamento',
        'id_fondo',
        'id_seccion',
        'id_serie',
        'id_subserie',
        'codigo_clasificacion',
        'tipo_nivel',
        'nombre_elemento',
        'descripcion',
        'periodo_reserva',
        'fundamento_legal',
        'activo',
    ];

    public function padre()
    {
        return $this->belongsTo(self::class, 'id_padre');
    }

    public function fondo()
    {
        return $this->belongsTo(Fondo::class, 'id_fondo');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'id_seccion');
    }

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
}
