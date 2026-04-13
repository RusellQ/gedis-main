<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fondo extends Model
{
    protected $table = 'fondos';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'id_dependencia',
        'id_departamento',

    ];

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
        return $this->hasMany(Serie::class, 'id_fondo');
    }

    public function subfondos()
    {
        return $this->hasMany(Subfondo::class, 'id_fondo');
    }

    public function secciones()
    {
        return $this->hasMany(Seccion::class, 'id_fondo');
    }
}
