<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'secciones';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'id_fondo',
        'id_subfondo',
        'id_dependencia',
        'id_departamento',
    ];

    public function fondo()
    {
        return $this->belongsTo(Fondo::class, 'id_fondo');
    }

    public function subfondo()
    {
        return $this->belongsTo(Subfondo::class, 'id_subfondo');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }
    
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }


    public function series()
    {
        return $this->hasMany(Serie::class, 'id_seccion');
    }
}
