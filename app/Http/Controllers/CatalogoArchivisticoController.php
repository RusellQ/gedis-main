<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatalogoArchivistico;

class CatalogoArchivisticoController extends Controller
{
    public function obtenerFondos()
    {
        return response()->json(
            CatalogoArchivistico::where('tipo', 'fondo')->get()
        );
    }

    public function obtenerSecciones(Request $request)
    {
        $codigoFondo = $request->query('codigo');
        return response()->json(
            CatalogoArchivistico::where('tipo', 'seccion')
                ->where('codigo', 'like', $codigoFondo . '%')
                ->get()
        );
    }

    public function obtenerSeries(Request $request)
    {
        $codigoSeccion = $request->query('codigo'); 
        return response()->json(
            CatalogoArchivistico::where('tipo', 'serie')
                ->where('codigo', 'like', $codigoSeccion . '%')
                ->get()
        );
    }

    public function obtenerExpedientes(Request $request)
    {
        $codigoSerie = $request->query('codigo'); 
        return response()->json(
            CatalogoArchivistico::where('tipo', 'expediente')
                ->where('codigo', 'like', $codigoSerie . '%')
                ->get()
        );
    }
}