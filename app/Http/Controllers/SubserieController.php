<?php

namespace App\Http\Controllers;

use App\Models\Serie;
use App\Models\Subserie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubserieController extends Controller
{
    public function index($id)
{
    $subseries = Subserie::where('id_serie', $id)
        ->with('serie')
        ->paginate(10);

    return response()->json($subseries);
}

    public function store(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'nombre' => 'required|string|max:255',
        'codigo' => 'required|string|max:100|unique:subseries,codigo',
        'descripcion' => 'nullable|string',
        'id_serie' => 'required|exists:series,id',
    ]);

    // Verificar que la serie pertenezca a la jerarquía del usuario
    $serie = Serie::where('id', $request->id_serie)
        ->where('id_dependencia', $user->id_dependencia)
        ->when($user->id_departamento, function ($query) use ($user) {
            return $query->where('id_departamento', $user->id_departamento);
        })
        ->first();

    if (!$serie) {
        return response()->json([
            'message' => 'No tienes permisos sobre esta serie.'
        ], 403);
    }

    // Crear la subserie con la jerarquía del usuario
    $subserie = Subserie::create([
        'nombre' => $request->nombre,
        'codigo' => $request->codigo,
        'descripcion' => $request->descripcion,
        'id_serie' => $request->id_serie,
        'id_dependencia' => $user->id_dependencia,
        'id_departamento' => $user->id_departamento
    ]);

    return response()->json([
        'message' => 'Subserie creada correctamente.',
        'subserie' => $subserie
    ], 201);
}

    public function expedientes($id)
{
    $subserie = Subserie::findOrFail($id);
    $expedientes = $subserie->expedientes; // Asumiendo relación definida

    return response()->json($expedientes);
}


}
