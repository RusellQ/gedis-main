<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Serie;
use App\Models\Seccion;


class SerieController extends Controller
{
public function getSeriesBySeccion($id)
{
    $series = Serie::where('id_seccion', $id)->get();

    if ($series->isEmpty()) {
        return response()->json([
            'status' => 'empty',
            'message' => 'No hay series registradas para esta sección.'
        ], 200);
    }

    return response()->json($series);
}

public function store(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'nombre'               => 'required|string|max:255',
        'codigo'               => 'required|string|max:50',
        'descripcion'          => 'nullable|string',
        'id_seccion'           => 'required|exists:secciones,id',
        'vigencia_ad'          => 'boolean',
        'vigencia_le'          => 'boolean',
        'vigencia_confi'       => 'boolean',
        'plazo_at'             => 'nullable|integer|min:0',
        'plazo_ac'             => 'nullable|integer|min:0',
        'plazo_ta'             => 'nullable|integer|min:0',
        'tecnica_eliminacion'  => 'boolean',
        'tecnica_conservacion' => 'boolean',
        'tecnica_muestreo'     => 'boolean',
        'clasificacion_r'      => 'nullable|string|max:255',
        'clasificacion_c'      => 'nullable|string|max:255',
        'observaciones'        => 'nullable|string',
    ]);

    // Verificar que la sección sea accesible para el usuario
    $seccion = Seccion::where('id', $request->id_seccion)
        ->where('id_dependencia', $user->id_dependencia)
        ->when($user->id_departamento, function ($query) use ($user) {
            return $query->where('id_departamento', $user->id_departamento);
        })
        ->first();

    if (!$seccion) {
        return response()->json([
            'message' => 'No tienes permisos sobre esta sección.'
        ], 403);
    }

    // Crear la serie
    $serie = Serie::create([
        'nombre'               => $request->nombre,
        'codigo'               => $request->codigo,
        'descripcion'          => $request->descripcion,
        'id_seccion'           => $request->id_seccion,
        'id_dependencia'       => $user->id_dependencia,
        'id_departamento'      => $user->id_departamento,
        'vigencia_ad'          => $request->boolean('vigencia_ad'),
        'vigencia_le'          => $request->boolean('vigencia_le'),
        'vigencia_confi'       => $request->boolean('vigencia_confi'),
        'plazo_at'             => $request->plazo_at,
        'plazo_ac'             => $request->plazo_ac,
        'plazo_ta'             => $request->plazo_ta,
        'tecnica_eliminacion'  => $request->boolean('tecnica_eliminacion'),
        'tecnica_conservacion' => $request->boolean('tecnica_conservacion'),
        'tecnica_muestreo'     => $request->boolean('tecnica_muestreo'),
        'clasificacion_r'      => $request->clasificacion_r,
        'clasificacion_c'      => $request->clasificacion_c,
        'observaciones'        => $request->observaciones,
    ]);

    return response()->json([
        'message' => 'Serie creada correctamente.',
        'serie' => $serie
    ], 201);
}



}
