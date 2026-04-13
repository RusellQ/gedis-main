<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\Dependencia;

class DepartamentoController extends Controller
{
  public function index(Request $request)
{
    $query = Departamento::with('dependencia')->orderBy('created_at', 'desc');

    if ($request->has('dependencia_id')) {
        $query->where('id_dependencia', $request->input('dependencia_id'));
    }

    $departamentos = $query->get();

    return response()->json($departamentos);
}

    public function store(Request $request)
    {
        $request->validate([
            'id_dependencia' => 'required|exists:dependencias,id',
            'nombre' => 'required|string|max:255',
            'clave' => 'required|string|max:100|unique:departamentos,clave',
            'descripcion' => 'nullable|string',
        ]);

        $departamento = Departamento::create($request->all());

        return response()->json([
            'message' => 'Departamento creado con éxito',
            'departamento' => $departamento
        ]);
    }
}
