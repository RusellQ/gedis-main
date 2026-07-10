<?php

namespace App\Services;

use App\Models\Fondo;
use App\Models\InstrumentoControl;
use App\Models\Seccion;
use App\Models\Serie;
use App\Models\Subserie;

class InstrumentoControlService
{
    public static function resolverPorSerie(int $serieId): ?InstrumentoControl
    {
        return InstrumentoControl::where('id_serie', $serieId)
            ->where('tipo_nivel', 'serie')
            ->first();
    }

    public static function resolverPorExpedienteClave(?string $clave): ?InstrumentoControl
    {
        if (!$clave) {
            return null;
        }

        $expediente = \App\Models\Expediente::where('clave', $clave)->first();

        if ($expediente?->id_instrumento_control) {
            return InstrumentoControl::with('serie')->find($expediente->id_instrumento_control);
        }

        if ($expediente?->id_serie) {
            return self::resolverPorSerie($expediente->id_serie);
        }

        return null;
    }

    public static function crearParaFondo(Fondo $fondo): InstrumentoControl
    {
        $instrumento = InstrumentoControl::create([
            'id_padre' => null,
            'id_dependencia' => $fondo->id_dependencia,
            'id_departamento' => $fondo->id_departamento,
            'id_fondo' => $fondo->id,
            'codigo_clasificacion' => $fondo->codigo,
            'tipo_nivel' => 'fondo',
            'nombre_elemento' => $fondo->nombre,
            'descripcion' => $fondo->descripcion,
            'activo' => 1,
        ]);

        $fondo->update(['id_instrumento_control' => $instrumento->id]);

        return $instrumento;
    }

    public static function crearParaSeccion(Seccion $seccion): InstrumentoControl
    {
        $padre = InstrumentoControl::where('id_fondo', $seccion->id_fondo)
            ->where('tipo_nivel', 'fondo')
            ->first();

        $instrumento = InstrumentoControl::create([
            'id_padre' => $padre?->id,
            'id_dependencia' => $seccion->id_dependencia,
            'id_departamento' => $seccion->id_departamento,
            'id_fondo' => $seccion->id_fondo,
            'id_seccion' => $seccion->id,
            'codigo_clasificacion' => $seccion->codigo,
            'tipo_nivel' => 'seccion',
            'nombre_elemento' => $seccion->nombre,
            'descripcion' => $seccion->descripcion,
            'activo' => 1,
        ]);

        $seccion->update(['id_instrumento_control' => $instrumento->id]);

        return $instrumento;
    }

    public static function crearParaSerie(Serie $serie): InstrumentoControl
    {
        $padre = InstrumentoControl::where('id_seccion', $serie->id_seccion)
            ->where('tipo_nivel', 'seccion')
            ->first();

        $instrumento = InstrumentoControl::create([
            'id_padre' => $padre?->id,
            'id_dependencia' => $serie->id_dependencia,
            'id_departamento' => $serie->id_departamento,
            'id_seccion' => $serie->id_seccion,
            'id_serie' => $serie->id,
            'codigo_clasificacion' => $serie->codigo,
            'tipo_nivel' => 'serie',
            'nombre_elemento' => $serie->nombre,
            'descripcion' => $serie->descripcion,
            'activo' => 1,
        ]);

        return $instrumento;
    }

    public static function crearParaSubserie(Subserie $subserie): InstrumentoControl
    {
        $padre = self::resolverPorSerie($subserie->id_serie);

        $instrumento = InstrumentoControl::create([
            'id_padre' => $padre?->id,
            'id_dependencia' => $subserie->id_dependencia,
            'id_departamento' => $subserie->id_departamento,
            'id_serie' => $subserie->id_serie,
            'id_subserie' => $subserie->id,
            'codigo_clasificacion' => $subserie->codigo,
            'tipo_nivel' => 'subserie',
            'nombre_elemento' => $subserie->nombre,
            'descripcion' => $subserie->descripcion,
            'activo' => 1,
        ]);

        $subserie->update(['id_instrumento_control' => $instrumento->id]);

        return $instrumento;
    }
}
