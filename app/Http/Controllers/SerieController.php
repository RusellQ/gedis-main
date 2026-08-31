<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Serie;
use App\Models\Seccion;
use App\Services\InstrumentoControlService;
use App\Http\Controllers\Concerns\AdministraCatalogo;

class SerieController extends Controller
{
    use AdministraCatalogo;

    public function getSeriesBySeccion($id)
    {
        $series = Serie::where('id_seccion', $id)->get();

        if ($series->isEmpty()) {
            return response()->json([
                'status' => 'empty',
                'message' => 'No hay series registradas para esta sección.',
            ], 200);
        }

        return response()->json($series);
    }

    public function store(Request $request)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'id_seccion' => 'required|exists:secciones,id',
            'vigencia_ad' => 'boolean',
            'vigencia_le' => 'boolean',
            'vigencia_confi' => 'boolean',
            'plazo_at' => 'nullable|integer|min:0',
            'plazo_ac' => 'nullable|integer|min:0',
            'plazo_ta' => 'nullable|integer|min:0',
            'tecnica_eliminacion' => 'boolean',
            'tecnica_conservacion' => 'boolean',
            'tecnica_muestreo' => 'boolean',
            'clasificacion_r' => 'nullable|boolean',
            'clasificacion_c' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ]);

        $seccion = Seccion::find($request->id_seccion);

        if (!$seccion) {
            return response()->json([
                'message' => 'La sección indicada no existe.',
            ], 404);
        }

        $jerarquia = $this->resolveJerarquiaIds($request, $seccion);

        $serie = Serie::create([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'id_seccion' => $request->id_seccion,
            'id_dependencia' => $jerarquia['id_dependencia'],
            'id_departamento' => $jerarquia['id_departamento'],
            'vigencia_ad' => $request->boolean('vigencia_ad'),
            'vigencia_le' => $request->boolean('vigencia_le'),
            'vigencia_confi' => $request->boolean('vigencia_confi'),
            'plazo_at' => $request->plazo_at,
            'plazo_ac' => $request->plazo_ac,
            'plazo_ta' => $request->plazo_ta,
            'tecnica_eliminacion' => $request->boolean('tecnica_eliminacion'),
            'tecnica_conservacion' => $request->boolean('tecnica_conservacion'),
            'tecnica_muestreo' => $request->boolean('tecnica_muestreo'),
            'clasificacion_r' => $request->boolean('clasificacion_r'),
            'clasificacion_c' => $request->boolean('clasificacion_c'),
            'observaciones' => $request->observaciones,
        ]);

        InstrumentoControlService::crearParaSerie($serie);

        return response()->json([
            'message' => 'Serie creada correctamente.',
            'serie' => $serie,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $serie = Serie::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('series', 'codigo')->ignore($serie->id),
            ],
            'descripcion' => 'nullable|string',
            'vigencia_ad' => 'boolean',
            'vigencia_le' => 'boolean',
            'vigencia_confi' => 'boolean',
            'plazo_at' => 'nullable|integer|min:0',
            'plazo_ac' => 'nullable|integer|min:0',
            'plazo_ta' => 'nullable|integer|min:0',
            'tecnica_eliminacion' => 'boolean',
            'tecnica_conservacion' => 'boolean',
            'tecnica_muestreo' => 'boolean',
            'clasificacion_r' => 'nullable|boolean',
            'clasificacion_c' => 'nullable|boolean',
            'observaciones' => 'nullable|string',
        ]);

        $serie->update([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
            'descripcion' => $request->descripcion,
            'vigencia_ad' => $request->boolean('vigencia_ad'),
            'vigencia_le' => $request->boolean('vigencia_le'),
            'vigencia_confi' => $request->boolean('vigencia_confi'),
            'plazo_at' => $request->plazo_at,
            'plazo_ac' => $request->plazo_ac,
            'plazo_ta' => $request->plazo_ta,
            'tecnica_eliminacion' => $request->boolean('tecnica_eliminacion'),
            'tecnica_conservacion' => $request->boolean('tecnica_conservacion'),
            'tecnica_muestreo' => $request->boolean('tecnica_muestreo'),
            'clasificacion_r' => $request->boolean('clasificacion_r'),
            'clasificacion_c' => $request->boolean('clasificacion_c'),
            'observaciones' => $request->observaciones,
        ]);

        InstrumentoControlService::sincronizarDesdeSerie($serie);

        return response()->json([
            'message' => 'Serie actualizada correctamente.',
            'serie' => $serie,
        ]);
    }

    public function destroy($id)
    {
        if ($denied = $this->denyUnlessRoot()) {
            return $denied;
        }

        $serie = Serie::findOrFail($id);

        if ($serie->subseries()->exists()) {
            return $this->conflictResponse('No se puede eliminar la serie porque tiene subseries asociadas.');
        }

        if ($serie->expedientes()->exists()) {
            return $this->conflictResponse('No se puede eliminar la serie porque tiene expedientes asociados.');
        }

        DB::transaction(function () use ($serie) {
            InstrumentoControlService::eliminarParaSerie($serie);
            $serie->delete();
        });

        return response()->json([
            'message' => 'Serie eliminada correctamente.',
        ]);
    }
}
