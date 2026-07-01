<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatalogoArchivistico;
use App\Models\Expediente;

class CatalogoArchivisticoController extends Controller
{
    public function obtenerFondos()
    {
        return response()->json(
            CatalogoArchivistico::where('tipo_nivel', 'fondo')
                ->where('activo', 1)
                ->get()
                ->map->toCatalogoArray()
        );
    }

    public function obtenerSecciones(Request $request)
    {
        $codigoFondo = $request->query('codigo');

        return response()->json(
            CatalogoArchivistico::where('tipo_nivel', 'seccion')
                ->where('activo', 1)
                ->where('codigo_clasificacion', 'like', $codigoFondo . '%')
                ->get()
                ->map->toCatalogoArray()
        );
    }

    public function obtenerSeries(Request $request)
    {
        $codigoSeccion = $request->query('codigo');

        return response()->json(
            CatalogoArchivistico::where('tipo_nivel', 'serie')
                ->where('activo', 1)
                ->where('codigo_clasificacion', 'like', $codigoSeccion . '%')
                ->get()
                ->map->toCatalogoArray()
        );
    }

    public function obtenerExpedientes(Request $request)
    {
        $codigoSerie = $request->query('codigo');

        $expedientes = Expediente::where('codigo', 'like', $codigoSerie . '%')
            ->orWhere('clave', 'like', '%' . $codigoSerie . '%')
            ->get();

        return response()->json(
            $expedientes->map(fn ($exp) => [
                'id' => $exp->id,
                'tipo' => 'expediente',
                'nombre' => $exp->nombre,
                'codigo' => $exp->codigo,
                'descripcion' => $exp->observacion,
                'created_at' => $exp->created_at,
            ])
        );
    }
}
