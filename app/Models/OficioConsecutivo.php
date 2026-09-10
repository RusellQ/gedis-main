<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OficioConsecutivo extends Model
{
    protected $table = 'oficio_consecutivos';

    public $timestamps = false;

    protected $fillable = [
        'prefijo',
        'anio',
        'ultimo_numero',
        'updated_at',
    ];

    protected $casts = [
        'anio' => 'integer',
        'ultimo_numero' => 'integer',
        'updated_at' => 'datetime',
    ];
}
