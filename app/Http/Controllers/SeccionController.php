<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seccion;
use App\Models\CatalogoArchivistico;
use App\Models\Fondo;
use App\Models\Subfondo;


class SeccionController extends Controller
{
    public function store(Request $request, $fondoId)
    {
        $user = auth()->user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'id_subfondo' => 'nullable|exists:subfondos,id',
        ]);

        // Verificar si el fondo pertenece a la jerarquía del usuario
        $fondo = Fondo::where('id', $fondoId)
            ->where('id_dependencia', $user->id_dependencia)
            ->when($user->id_departamento, function ($query) use ($user) {
                return $query->where('id_departamento', $user->id_departamento);
            })
            ->first();

        if (!$fondo) {
            return response()->json([
                'message' => 'No tienes permisos sobre este fondo.'
            ], 403);
        }

        // Si se proporciona un subfondo, verificar que también pertenezca a la jerarquía del usuario y esté vinculado al fondo
        if ($request->filled('id_subfondo')) {
            $subfondo = Subfondo::where('id', $request->id_subfondo)
                ->where('id_fondo', $fondoId)
                ->where('id_dependencia', $user->id_dependencia)
                ->when($user->id_departamento, function ($query) use ($user) {
                    return $query->where('id_departamento', $user->id_departamento);
                })
                ->first();

            if (!$subfondo) {
                return response()->json([
                    'message' => 'No tienes permisos sobre este subfondo.'
                ], 403);
            }
        }

        // Crear la sección
        $seccion = new Seccion();
        $seccion->nombre = $request->nombre;
        $seccion->codigo = $request->codigo;
        $seccion->descripcion = $request->descripcion;
        $seccion->id_fondo = $fondoId;
        $seccion->id_subfondo = $request->id_subfondo;
        $seccion->id_dependencia = $user->id_dependencia;
        $seccion->id_departamento = $user->id_departamento;

        $seccion->save();

        return response()->json([
            'message' => 'Sección creada correctamente',
            'seccion' => $seccion
        ], 201);
    }


    public function index(Request $request, $fondoId)
    {
        $query = Seccion::where('id_fondo', $fondoId);

        if ($request->has('id_subfondo')) {
            $query->where('id_subfondo', $request->id_subfondo);
        }

        $secciones = $query->get();

        return response()->json($secciones);
    }
}
