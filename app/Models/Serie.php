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
        
        // --- Vigencia Documental ---
        'vigencia_ad',    // Administrativo
        'vigencia_le',    // Legal
        'vigencia_confi', // Confidencial/Contable

        // --- Plazos de Conservación ---
        'plazo_at',       // Archivo de Trámite
        'plazo_ac',       // Archivo de Concentración
        'plazo_ta',       // Total de Años

        // --- Técnicas de Selección ---
        'tecnica_eliminacion',
        'tecnica_conservacion',
        'tecnica_muestreo',

        // --- Clasificación de Información --- para la gestión de seguridad de la información y protección de datos personales sl igual que en el modelo de Subserie, pero se incluyen aquí para fca
        'clasificacion_r', // Reservada
        'clasificacion_c', // Confidencial

        'observaciones',
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

    public function instrumentoControl()
    {
        return $this->hasOne(InstrumentoControl::class, 'id_serie')
            ->where('tipo_nivel', 'serie');
    }

    public function getPlazoConservacionAttribute()
    {
        return $this->plazo_ta;
    }

    public function getValorDocumentalAttribute()
    {
        $valores = [];

        if ($this->vigencia_ad) {
            $valores[] = 'Administrativo';
        }
        if ($this->vigencia_le) {
            $valores[] = 'Legal';
        }
        if ($this->vigencia_confi) {
            $valores[] = 'Contable';
        }

        return $valores ? implode(', ', $valores) : null;
    }

    public function getDestinoFinalAttribute()
    {
        if ($this->tecnica_conservacion) {
            return 'Conservación';
        }
        if ($this->tecnica_eliminacion) {
            return 'Eliminación';
        }
        if ($this->tecnica_muestreo) {
            return 'Muestreo';
        }

        return null;
    }

    public function getDisposicionEspecificaAttribute()
    {
        return $this->observaciones;
    }
}