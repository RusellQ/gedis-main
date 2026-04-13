<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    protected $table = 'dependencias';

    protected $fillable = ['nombre', 'clave', 'descripcion'];

    // Relación con fondos, subfondos, etc.
    public function fondos()
    {
        return $this->hasMany(Fondo::class, 'id_dependencia');
    }

    public function subfondos()
    {
        return $this->hasMany(Subfondo::class, 'id_dependencia');
    }

    public function secciones()
    {
        return $this->hasMany(Seccion::class, 'id_dependencia');
    }

    public function series()
    {
        return $this->hasMany(Serie::class, 'id_dependencia');
    }

    public function subseries()
    {
        return $this->hasMany(Subserie::class, 'id_dependencia');
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'id_dependencia');
    }

    public function departamentos()
{
    return $this->hasMany(Departamento::class, 'id_dependencia');
}
}
