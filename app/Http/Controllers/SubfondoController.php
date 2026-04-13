<?php

namespace App\Http\Controllers;

use App\Models\Fondo;
use App\Models\Subfondo;
use App\Models\Seccion;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubfondoController extends Controller
{
  
 public function store(Request $request)
{
    $user = auth()->user();

    // Validar los datos recibidos
    $data = $request->validate([
        'nombre'      => 'required|string|max:255',
        'codigo'      => 'required|string|max:100|unique:subfondos,codigo',
        'descripcion' => 'nullable|string',
        'id_fondo'    => 'required|exists:fondos,id',
    ]);

    // Verificar que el fondo pertenezca a la jerarquía del usuario
    $fondo = Fondo::where('id', $data['id_fondo'])
        ->where('id_dependencia', $user->id_dependencia)
        ->when($user->id_departamento, function ($query) use ($user) {
            return $query->where('id_departamento', $user->id_departamento);
        })
        ->first();

    if (!$fondo) {
        return response()->json([
            'message' => 'No tienes permisos para agregar un subfondo a este fondo.'
        ], 403);
    }

    // Asignar jerarquía del usuario al subfondo
    $data['id_dependencia'] = $user->id_dependencia;
    $data['id_departamento'] = $user->id_departamento;

    // Crear el subfondo
    $subfondo = Subfondo::create($data);

    return response()->json([
        'message' => 'Subfondo creado correctamente.',
        'subfondo' => $subfondo
    ], 201);
}

public function secciones($id)
{
    $secciones = Seccion::where('id_subfondo', $id)->get();

    return response()->json($secciones);
}


}
