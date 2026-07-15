<?php

namespace App\Http\Controllers;

use App\Models\Fondo;
use App\Models\Subfondo;
use App\Models\Seccion;
use App\Http\Controllers\Concerns\AdministraCatalogo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubfondoController extends Controller
{
    use AdministraCatalogo;

    public function store(Request $request)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:100|unique:subfondos,codigo',
            'descripcion' => 'nullable|string',
            'id_fondo' => 'required|exists:fondos,id',
        ]);

        $fondo = Fondo::find($data['id_fondo']);

        if (!$fondo) {
            return response()->json([
                'message' => 'El fondo indicado no existe.',
            ], 404);
        }

        $jerarquia = $this->resolveJerarquiaIds($request, $fondo);
        $data['id_dependencia'] = $jerarquia['id_dependencia'];
        $data['id_departamento'] = $jerarquia['id_departamento'];

        $subfondo = Subfondo::create($data);

        return response()->json([
            'message' => 'Subfondo creado correctamente.',
            'subfondo' => $subfondo,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $subfondo = Subfondo::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'max:100',
                Rule::unique('subfondos', 'codigo')->ignore($subfondo->id),
            ],
            'descripcion' => 'nullable|string',
        ]);

        $subfondo->update($data);

        return response()->json([
            'message' => 'Subfondo actualizado correctamente.',
            'subfondo' => $subfondo,
        ]);
    }

    public function destroy($id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $subfondo = Subfondo::findOrFail($id);

        if ($subfondo->secciones()->exists()) {
            return $this->conflictResponse('No se puede eliminar el subfondo porque tiene secciones asociadas.');
        }

        $subfondo->delete();

        return response()->json([
            'message' => 'Subfondo eliminado correctamente.',
        ]);
    }

    public function secciones($id)
    {
        $secciones = Seccion::where('id_subfondo', $id)->get();

        return response()->json($secciones);
    }
}
