<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadDocumental extends Model
{
    use HasFactory;

      protected $table = 'unidades_documentales';

    protected $fillable = [
        'id_expediente', 'tipo', 'nombre', 'descripcion', 'archivo'
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id_expediente');
    }
    
}
