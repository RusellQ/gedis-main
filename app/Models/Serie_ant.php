<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    use HasFactory;

    protected $table = 'series';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'id_seccion',
        'id_dependencia',
        'id_departamento',
    ];

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'id_seccion');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function subseries()
    {
        return $this->hasMany(Subserie::class, 'id_serie');
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'id_serie');
    }
}
