<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnidadDocumental;

class UnidadDocumentalController extends Controller
{
    public function store(Request $request)
    {
        $unidad = UnidadDocumental::create([
            'id_expediente' => $request->id_expediente,
            'tipo' => $request->tipo,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'archivo' => $request->hasFile('archivo')
                ? $request->file('archivo')->store('documentos', 'public')
                : null
        ]);

        return response()->json($unidad);
    }
}
