<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subserie extends Model
{
    use HasFactory;

    protected $table = 'subseries';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'id_serie',
        'id_dependencia', 
        'id_departamento',  

    ];

    public function serie()
    {
        return $this->belongsTo(Serie::class, 'id_serie');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'id_subserie');
    }
}
