<?php

// app/Http/Controllers/FondoController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fondo;
use App\Models\Subfondo;
use Illuminate\Support\Facades\Auth;
use App\Models\Seccion;
use Illuminate\Support\Facades\DB;

class FondoController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user(); // Obtenemos al usuario logueado


        // Validar los datos recibidos
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:fondos,codigo',
            'descripcion' => 'nullable|string',
        ]);

        // Crear el nuevo fondo
        $fondo = new Fondo();
        $fondo->nombre = $request->input('nombre');
        $fondo->codigo = $request->input('codigo');
        $fondo->descripcion = $request->input('descripcion');
        $fondo->id_dependencia = $user->id_dependencia;
        $fondo->id_departamento = $user->id_departamento;
        $fondo->save();

        // Devolver respuesta
        return response()->json([
            'message' => 'Fondo creado correctamente.',
            'fondo' => $fondo
        ], 201);
    }


  public function index()
{
    $user = Auth::user();

    $query = Fondo::select('id', 'nombre', 'codigo', 'descripcion', 'created_at');

    if (!$user->hasRole('root')) {
        if (!$user->id_dependencia || !$user->id_departamento) {
            return response()->json(['error' => 'El usuario no tiene asignada una dependencia o departamento.'], 403);
        }

        $query->where('id_dependencia', $user->id_dependencia)
              ->where('id_departamento', $user->id_departamento);
    }

    $fondos = $query->orderBy('created_at', 'desc')->get();

    return response()->json($fondos);
}



    public function allFondos()
    {
        try {
            $user = Auth::user();

            $fondosQuery = Fondo::query();

            // Si NO es root, filtra por dependencia y departamento del usuario
            if (!$user->hasRole('root')) {
                if (!$user->id_dependencia || !$user->id_departamento) {
                    return response()->json(['error' => 'El usuario no tiene asignada una dependencia o departamento.'], 403);
                }

                $fondosQuery->where('id_dependencia', $user->id_dependencia)
                    ->where('id_departamento', $user->id_departamento);
            }

          
            $fondos = $fondosQuery->with([
                'dependencia',   
                'departamento',
                'subfondos.secciones.series.subseries', 
                'subfondos.secciones.series.subseries.expedientes',
                'subfondos.secciones.series.expedientes',
            ])->get();

            return response()->json($fondos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los fondos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fondosPorDependencia(Request $request)
    {
        $idDependencia = $request->input('id_dependencia');

        if (!$idDependencia) {
            return response()->json([], 200);
        }

        $fondos = Fondo::with([
            'dependencia',
            'departamento',
            'subfondos.secciones.series.subseries.expedientes',
            'subfondos.secciones.series.expedientes',
            'secciones.series.subseries.expedientes',
            'secciones.series.expedientes',
        ])
            ->where('id_dependencia', $idDependencia)
            ->get();

        return response()->json($fondos);
    }



    public function secciones($id)
    {
        $secciones = Seccion::where('id_fondo', $id)
            ->whereNull('id_subfondo') // <- este filtro es clave
            ->get();

        return response()->json($secciones);
    }

    public function subfondos($id)
    {
        $subfondos = Subfondo::where('id_fondo', $id)->get();
        return response()->json($subfondos);
    }
}
