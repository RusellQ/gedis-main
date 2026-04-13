<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $fillable = ['id_dependencia', 'nombre', 'clave', 'descripcion'];

    // Relación inversa con Dependencia
    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }
}
