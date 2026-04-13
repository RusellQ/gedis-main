<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subfondo extends Model
{
    use HasFactory;

    protected $table = 'subfondos';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'id_fondo',
        'id_dependencia', 
        'id_departamento', 
    ];

    public function fondo()
    {
        return $this->belongsTo(Fondo::class, 'id_fondo');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function departamento()
{
    return $this->belongsTo(Departamento::class, 'id_departamento');
}

    public function secciones()
    {
        return $this->hasMany(Seccion::class, 'id_subfondo');
    }
}
