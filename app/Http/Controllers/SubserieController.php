<?php

namespace App\Http\Controllers;

use App\Models\Serie;
use App\Models\Subserie;
use App\Services\InstrumentoControlService;
use App\Http\Controllers\Concerns\AdministraCatalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubserieController extends Controller
{
    use AdministraCatalogo;

    public function index($id)
    {
        $subseries = Subserie::where('id_serie', $id)
            ->with('serie')
            ->paginate(10);

        return response()->json($subseries);
    }

    public function store(Request $request)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:100|unique:subseries,codigo',
            'descripcion' => 'nullable|string',
            'id_serie' => 'required|exists:series,id',
        ]);

        $serie = Serie::find($request->id_serie);

        if (!$serie) {
            return response()->json([
                'message' => 'La serie indicada no existe.',
            ], 404);
        }

        $jerarquia = $this->resolveJerarquiaIds($request, $serie);

        $subserie = Subserie::create([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'id_serie' => $request->id_serie,
            'id_dependencia' => $jerarquia['id_dependencia'],
            'id_departamento' => $jerarquia['id_departamento'],
        ]);

        InstrumentoControlService::crearParaSubserie($subserie);

        return response()->json([
            'message' => 'Subserie creada correctamente.',
            'subserie' => $subserie,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $subserie = Subserie::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'max:100',
                Rule::unique('subseries', 'codigo')->ignore($subserie->id),
            ],
            'descripcion' => 'nullable|string',
        ]);

        $subserie->update([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
        ]);

        InstrumentoControlService::sincronizarDesdeSubserie($subserie);

        return response()->json([
            'message' => 'Subserie actualizada correctamente.',
            'subserie' => $subserie,
        ]);
    }

    public function destroy($id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $subserie = Subserie::findOrFail($id);

        if ($subserie->expedientes()->exists()) {
            return $this->conflictResponse('No se puede eliminar la subserie porque tiene expedientes asociados.');
        }

        DB::transaction(function () use ($subserie) {
            InstrumentoControlService::eliminarParaSubserie($subserie);
            $subserie->delete();
        });

        return response()->json([
            'message' => 'Subserie eliminada correctamente.',
        ]);
    }

    public function expedientes($id)
    {
        $subserie = Subserie::findOrFail($id);
        $expedientes = $subserie->expedientes;

        return response()->json($expedientes);
    }
}
