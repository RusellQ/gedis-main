<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoArchivistico extends InstrumentoControl
{
    use HasFactory;

    public $timestamps = true;

    public function toCatalogoArray(): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo_nivel,
            'nombre' => $this->nombre_elemento,
            'codigo' => $this->codigo_clasificacion,
            'descripcion' => $this->descripcion,
            'created_at' => $this->created_at,
        ];
    }
}
