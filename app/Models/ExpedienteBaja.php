<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpedienteBaja extends Model
{
    protected $table = 'expedientes_baja';
    protected $guarded = []; // o define $fillable con los campos permitidos
    public $timestamps = false; // si no usas created_at/updated_at
}