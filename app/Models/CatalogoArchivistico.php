<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoArchivistico extends Model
{
    protected $table = 'catalogo_archivistico';
    public $timestamps = false; // si no usas `updated_at`

    protected $fillable = [
        'tipo',
        'nombre',
        'codigo',
        'descripcion',
        'created_at',
    ];
}