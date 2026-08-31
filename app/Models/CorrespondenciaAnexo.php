<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrespondenciaAnexo extends Model
{
    public $timestamps = false;

    protected $table = 'correspondencia_anexos';

    protected $fillable = [
        'id_correspondencia',
        'id_unidad_documental',
        'orden',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // =========================================================================
    // Relaciones
    // =========================================================================

    /**
     * Correspondencia asociada
     */
    public function correspondencia()
    {
        return $this->belongsTo(Correspondencia::class, 'id_correspondencia');
    }

    /**
     * Unidad documental (PDF)
     */
    public function unidadDocumental()
    {
        return $this->belongsTo(UnidadDocumental::class, 'id_unidad_documental');
    }
}
