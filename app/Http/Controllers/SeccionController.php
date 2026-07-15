<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Seccion;
use App\Models\Fondo;
use App\Models\Subfondo;
use App\Services\InstrumentoControlService;
use App\Http\Controllers\Concerns\AdministraCatalogo;

class SeccionController extends Controller
{
    use AdministraCatalogo;

    public function store(Request $request, $fondoId = null)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $fondoId = $fondoId ?? $request->input('id_fondo');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'id_fondo' => 'nullable|exists:fondos,id',
            'id_subfondo' => 'nullable|exists:subfondos,id',
        ]);

        if (!$fondoId) {
            return response()->json([
                'message' => 'Debes indicar el fondo de la sección.',
            ], 422);
        }

        $fondo = Fondo::find($fondoId);

        if (!$fondo) {
            return response()->json([
                'message' => 'El fondo indicado no existe.',
            ], 404);
        }

        if ($request->filled('id_subfondo')) {
            $subfondo = Subfondo::where('id', $request->id_subfondo)
                ->where('id_fondo', $fondoId)
                ->first();

            if (!$subfondo) {
                return response()->json([
                    'message' => 'El subfondo no pertenece al fondo indicado.',
                ], 422);
            }
        }

        $jerarquia = $this->resolveJerarquiaIds($request, $fondo);

        $seccion = new Seccion();
        $seccion->nombre = $request->nombre;
        $seccion->codigo = $request->codigo;
        $seccion->descripcion = $request->descripcion;
        $seccion->id_fondo = $fondoId;
        $seccion->id_subfondo = $request->id_subfondo;
        $seccion->id_dependencia = $jerarquia['id_dependencia'];
        $seccion->id_departamento = $jerarquia['id_departamento'];
        $seccion->save();

        InstrumentoControlService::crearParaSeccion($seccion);

        return response()->json([
            'message' => 'Sección creada correctamente.',
            'seccion' => $seccion,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $seccion = Seccion::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'max:100',
                Rule::unique('secciones', 'codigo')->ignore($seccion->id),
            ],
            'descripcion' => 'nullable|string',
        ]);

        $seccion->nombre = $request->nombre;
        $seccion->codigo = $request->codigo;
        $seccion->descripcion = $request->descripcion;
        $seccion->save();

        InstrumentoControlService::sincronizarDesdeSeccion($seccion);

        return response()->json([
            'message' => 'Sección actualizada correctamente.',
            'seccion' => $seccion,
        ]);
    }

    public function destroy($id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $seccion = Seccion::findOrFail($id);

        if ($seccion->series()->exists()) {
            return $this->conflictResponse('No se puede eliminar la sección porque tiene series asociadas.');
        }

        DB::transaction(function () use ($seccion) {
            InstrumentoControlService::eliminarParaSeccion($seccion);
            $seccion->delete();
        });

        return response()->json([
            'message' => 'Sección eliminada correctamente.',
        ]);
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
