<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Fondo;
use App\Models\Subfondo;
use App\Models\Seccion;
use App\Services\InstrumentoControlService;
use App\Http\Controllers\Concerns\AdministraCatalogo;

class FondoController extends Controller
{
    use AdministraCatalogo;

    public function store(Request $request)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:fondos,codigo',
            'descripcion' => 'nullable|string',
            'id_dependencia' => 'required|exists:dependencias,id',
            'id_departamento' => 'required|exists:departamentos,id',
        ]);

        $jerarquia = $this->resolveJerarquiaIds($request);

        $fondo = new Fondo();
        $fondo->nombre = $request->input('nombre');
        $fondo->codigo = $request->input('codigo');
        $fondo->descripcion = $request->input('descripcion');
        $fondo->id_dependencia = $jerarquia['id_dependencia'];
        $fondo->id_departamento = $jerarquia['id_departamento'];
        $fondo->save();

        InstrumentoControlService::crearParaFondo($fondo);

        return response()->json([
            'message' => 'Fondo creado correctamente.',
            'fondo' => $fondo,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $fondo = Fondo::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('fondos', 'codigo')->ignore($fondo->id),
            ],
            'descripcion' => 'nullable|string',
            'id_dependencia' => 'sometimes|exists:dependencias,id',
            'id_departamento' => 'sometimes|exists:departamentos,id',
        ]);

        $fondo->nombre = $request->input('nombre');
        $fondo->codigo = $request->input('codigo');
        $fondo->descripcion = $request->input('descripcion');

        if ($request->filled('id_dependencia')) {
            $fondo->id_dependencia = $request->input('id_dependencia');
        }
        if ($request->filled('id_departamento')) {
            $fondo->id_departamento = $request->input('id_departamento');
        }

        $fondo->save();
        InstrumentoControlService::sincronizarDesdeFondo($fondo);

        return response()->json([
            'message' => 'Fondo actualizado correctamente.',
            'fondo' => $fondo,
        ]);
    }

    public function destroy($id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $fondo = Fondo::findOrFail($id);

        if ($fondo->subfondos()->exists()) {
            return $this->conflictResponse('No se puede eliminar el fondo porque tiene subfondos asociados.');
        }

        if ($fondo->secciones()->exists()) {
            return $this->conflictResponse('No se puede eliminar el fondo porque tiene secciones asociadas.');
        }

        DB::transaction(function () use ($fondo) {
            InstrumentoControlService::eliminarParaFondo($fondo);
            $fondo->delete();
        });

        return response()->json([
            'message' => 'Fondo eliminado correctamente.',
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        $query = Fondo::select(
            'id',
            'nombre',
            'codigo',
            'descripcion',
            'id_dependencia',
            'id_departamento',
            'created_at'
        );

        if (!$user->hasRole('root')) {
            if (!$user->id_dependencia || !$user->id_departamento) {
                return response()->json([
                    'error' => 'El usuario no tiene asignada una dependencia o departamento.',
                ], 403);
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

            if (!$user->hasRole('root')) {
                if (!$user->id_dependencia || !$user->id_departamento) {
                    return response()->json([
                        'error' => 'El usuario no tiene asignada una dependencia o departamento.',
                    ], 403);
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
                'error' => 'Error al obtener los fondos: ' . $e->getMessage(),
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
            ->whereNull('id_subfondo')
            ->get();

        return response()->json($secciones);
    }

    public function subfondos($id)
    {
        $subfondos = Subfondo::where('id_fondo', $id)->get();

        return response()->json($subfondos);
    }
}
