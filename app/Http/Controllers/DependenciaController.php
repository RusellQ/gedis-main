<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dependencia;

class DependenciaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'clave' => 'required|string|max:100|unique:dependencias,clave',
            'descripcion' => 'nullable|string',
        ]);

        Dependencia::create([
            'nombre' => $request->nombre,
            'clave' => $request->clave,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->back()->with('success', 'Dependencia creada correctamente');
    }

    public function index(Request $request)
    {
        $query = Dependencia::orderBy('created_at', 'desc');

        if (!$request->input('incluir_root')) {
            $query->where('clave', '!=', 'SIS-ROOT');
        }

        $dependencias = $query->get();

        return response()->json($dependencias);
    }
}
