<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Serie;
use App\Models\Subserie;
use App\Models\ExpedienteBaja;
use App\Services\InstrumentoControlService;
use App\Services\ReglasDocumentalesService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExpedienteController extends Controller
{
    public function darDeBaja(Request $request, $id)
    {
        $validated = $request->validate([
            'motivo_baja' => 'nullable|string|max:500',
        ]);

        $expediente = Expediente::with(['serie', 'instrumentoControl'])->findOrFail($id);

        DB::transaction(function () use ($expediente, $validated) {
            ExpedienteBaja::create([
                'id_serie'             => $expediente->id_serie,
                'tiempo_conservacion'  => $expediente->tiempo_conservacion,
                'no_legajos'           => $expediente->no_legajos,
                'no_hojas'             => $expediente->no_hojas,
                'ubicacion_topografica' => $expediente->ubicacion_topografica,
                'no_caja'              => $expediente->no_caja,
                'clasificacion'        => $expediente->clasificacion,
                'caracter'             => $expediente->caracter,
                'observacion'          => $expediente->observacion,
                'nombre'               => $expediente->nombre,
                'codigo'               => $expediente->codigo,
                'estado'               => $expediente->estado,
                'fecha_creacion'       => $expediente->fecha_creacion,
                'fecha_apertura'       => $expediente->fecha_apertura,
                'fecha_cierre'         => $expediente->fecha_cierre,
                'created_at'           => $expediente->created_at,
                'updated_at'           => $expediente->updated_at,
                'preservacion'         => $expediente->preservacion,
                'clave'                => $expediente->clave,
                'motivo_baja'          => $validated['motivo_baja'] ?? null,
                'fecha_baja'           => now(),
                'user_baja_id'         => Auth::id(),
            ]);

            $expediente->delete();
        });

        return response()->json(['message' => 'Expediente dado de baja con exito '], 201);
    }

    public function getExpedienteById($id)
    {
        $expediente = Expediente::with(['serie', 'instrumentoControl'])->find($id);

        if (!$expediente) {
            return response()->json(['message' => 'Expediente no encontrado'], 404);
        }

        return response()->json(
            array_merge($expediente->toArray(), ReglasDocumentalesService::reglasExpedienteParaApi($expediente))
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'                 => 'required|string|max:255',
            'codigo'                 => 'required|string|max:50',
            'id_serie'               => 'required|exists:series,id',
            'id_subserie'            => 'nullable|exists:subseries,id',
            'estado'                 => 'required|in:en_tramite,en_historico,en_concentracion',
            'fecha_apertura'         => 'nullable|date',
            'fecha_cierre'           => 'nullable|date',
            'fecha_creacion'         => 'nullable|date',
            'tiempo_conservacion'    => 'nullable|numeric',
            'vigencia'               => 'nullable|numeric',
            'no_legajos'             => 'nullable|integer|min:0',
            'no_hojas'               => 'nullable|integer|min:0',
            'no_caja'                => 'nullable|integer|min:0',
            'ubicacion_topografica'  => 'nullable|string|max:255',
            'clasificacion'          => 'nullable|in:Pública,Reservada,Confidencial',
            'caracter'               => 'nullable|in:Administrativo,Legal,Contable',
            'preservacion'           => 'nullable|string|max:255',
            'observacion'            => 'nullable|string|max:1000',
            'informacion_reservada_y_o_confidencial' => 'nullable|string|max:100',
            'caracter_de_la_informacion' => 'nullable|string|max:255',
            'fecha_clasificacion'    => 'nullable|date',
            'fundamento_legal'       => 'nullable|string',
            'periodo_reserva'        => 'nullable',
            'ampliacion_periodo_reserva' => 'nullable',
            'rubrica_titular_de_la_unidad' => 'nullable|string|max:255',
            'fecha_desclasificacion' => 'nullable|date',
        ]);

        $user = auth()->user();

        $serie = Serie::with('seccion.subfondo', 'seccion.fondo')
            ->findOrFail($request->id_serie);

        $subserie = $request->filled('id_subserie')
            ? Subserie::findOrFail($request->id_subserie)
            : null;

        $partes = array_filter([
            $serie->seccion->fondo->codigo ?? '',
            optional($serie->seccion->subfondo)->codigo,
            $serie->seccion->codigo ?? '',
            $serie->codigo ?? '',
            $subserie?->codigo,
            $request->codigo,
        ]);

        $clave = implode('/', $partes);
        $instrumento = InstrumentoControlService::resolverPorSerie($request->id_serie);

        $expediente = Expediente::create([
            'nombre'                 => $request->nombre,
            'codigo'                 => $request->codigo,
            'clave'                  => $clave,
            'id_serie'               => $request->id_serie,
            'id_subserie'            => $request->id_subserie,
            'id_instrumento_control' => $instrumento?->id,
            'estado'                 => $request->estado,
            'fecha_apertura'         => $request->fecha_apertura,
            'fecha_cierre'           => $request->fecha_cierre,
            'fecha_creacion'         => $request->fecha_creacion,
            'no_legajos'             => $request->no_legajos,
            'no_hojas'               => $request->no_hojas,
            'no_caja'                => $request->no_caja,
            'ubicacion_topografica'  => $request->ubicacion_topografica,
            'preservacion'           => $request->preservacion,
            'observacion'            => $request->observacion,
            'informacion_reservada_y_o_confidencial' => $request->informacion_reservada_y_o_confidencial
                ?? $request->caracter_de_la_informacion,
            'fecha_clasificacion'    => $request->fecha_clasificacion,
            'rubrica_titular_de_la_unidad' => $request->rubrica_titular_de_la_unidad,
            'fecha_desclasificacion' => $request->fecha_desclasificacion,
            'id_dependencia'         => $user->id_dependencia,
            'id_departamento'        => $user->id_departamento,
        ]);

        ReglasDocumentalesService::aplicarReglasExpediente($expediente, $request->all());
        $expediente->load(['serie', 'instrumentoControl']);

        return response()->json([
            'message'    => 'Expediente creado correctamente.',
            'expediente' => array_merge(
                $expediente->toArray(),
                ReglasDocumentalesService::reglasExpedienteParaApi($expediente)
            ),
        ], 201);
    }

    public function index()
    {
        try {
            $user = Auth::user();

            $with = [
                'serie.seccion.fondo',
                'serie.seccion.subfondo',
                'subserie',
                'instrumentoControl',
                'unidadesDocumentales',
            ];

            if ($user->hasRole('root')) {
                $expedientes = Expediente::with($with)->get();
            } else {
                if (!$user->id_dependencia || !$user->id_departamento) {
                    return response()->json(['error' => 'El usuario no tiene asignada una dependencia o departamento.'], 403);
                }

                $expedientes = Expediente::where('id_dependencia', $user->id_dependencia)
                    ->where('id_departamento', $user->id_departamento)
                    ->with($with)
                    ->get();
            }

            $datos = $expedientes->map(function ($exp) {
                $base = ReglasDocumentalesService::formatearExpedienteParaApi($exp);

                return array_merge($base, [
                    'fondo' => optional(optional($exp->serie)->seccion->fondo)->nombre,
                    'subfondo' => optional(optional($exp->serie)->seccion->subfondo)->nombre,
                    'seccion' => optional(optional($exp->serie)->seccion)->nombre,
                    'serie' => optional($exp->serie)->nombre,
                    'subserie' => optional($exp->subserie)->nombre,
                    'serie_detalle' => $exp->serie ? [
                        'id' => $exp->serie->id,
                        'nombre' => $exp->serie->nombre,
                        'codigo' => $exp->serie->codigo,
                        'vigencia_ad' => (bool) $exp->serie->vigencia_ad,
                        'vigencia_le' => (bool) $exp->serie->vigencia_le,
                        'vigencia_confi' => (bool) $exp->serie->vigencia_confi,
                        'plazo_at' => $exp->serie->plazo_at,
                        'plazo_ac' => $exp->serie->plazo_ac,
                    ] : null,
                    'subserie_detalle' => $exp->subserie ? [
                        'id' => $exp->subserie->id,
                        'nombre' => $exp->subserie->nombre,
                        'codigo' => $exp->subserie->codigo,
                    ] : null,
                    'unidades_documentales' => $exp->unidadesDocumentales->map(function ($u) {
                        return [
                            'id' => $u->id,
                            'tipo' => $u->tipo,
                            'nombre' => $u->nombre,
                            'descripcion' => $u->descripcion,
                            'archivo' => $u->archivo,
                        ];
                    }),
                ]);
            });

            return response()->json($datos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener los expedientes. ' . $e->getMessage()
            ], 500);
        }
    }

    public function porSerie($id)
    {
        $expedientes = Expediente::with(['serie', 'instrumentoControl'])
            ->where('id_serie', $id)
            ->whereNull('id_subserie')
            ->get()
            ->map(fn ($exp) => array_merge(
                $exp->toArray(),
                ReglasDocumentalesService::reglasExpedienteParaApi($exp)
            ));

        return response()->json($expedientes);
    }

    public function porSubserie($id)
    {
        $expedientes = Expediente::with(['serie', 'instrumentoControl'])
            ->where('id_subserie', $id)
            ->get()
            ->map(fn ($exp) => array_merge(
                $exp->toArray(),
                ReglasDocumentalesService::reglasExpedienteParaApi($exp)
            ));

        return response()->json($expedientes);
    }

    public function actualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:255',
            'tiempo_conservacion' => 'nullable|integer|min:0',
            'no_legajos' => 'nullable|integer|min:0',
            'no_hojas' => 'nullable|integer|min:0',
            'ubicacion_topografica' => 'nullable|string|max:255',
            'no_caja' => 'nullable|integer|min:0',
            'clasificacion' => 'nullable|string',
            'caracter' => 'nullable|string',
            'observacion' => 'nullable|string',
            'estado' => 'nullable|string',
            'fecha_creacion' => 'nullable|date',
            'fecha_apertura' => 'nullable|date',
            'fecha_cierre' => 'nullable|date',
            'preservacion' => 'nullable|string',
            'informacion_reservada_y_o_confidencial' => 'nullable|string|max:100',
            'caracter_de_la_informacion' => 'nullable|string|max:255',
            'fecha_clasificacion' => 'nullable|date',
            'fundamento_legal' => 'nullable|string',
            'periodo_reserva' => 'nullable',
            'ampliacion_periodo_reserva' => 'nullable',
            'rubrica_titular_de_la_unidad' => 'nullable|string|max:255',
            'fecha_desclasificacion' => 'nullable|date',
            'clave' => 'nullable|string|max:255',
            'vigencia' => 'nullable',
        ]);

        $expediente = Expediente::findOrFail($id);

        $camposOperativos = collect($validated)->only([
            'nombre', 'codigo', 'no_legajos', 'no_hojas', 'ubicacion_topografica', 'no_caja',
            'observacion', 'estado', 'fecha_creacion', 'fecha_apertura', 'fecha_cierre',
            'preservacion', 'fecha_clasificacion', 'rubrica_titular_de_la_unidad',
            'fecha_desclasificacion', 'clave',
        ])->toArray();

        if (isset($validated['informacion_reservada_y_o_confidencial'])) {
            $camposOperativos['informacion_reservada_y_o_confidencial'] = $validated['informacion_reservada_y_o_confidencial'];
        } elseif (isset($validated['caracter_de_la_informacion'])) {
            $camposOperativos['informacion_reservada_y_o_confidencial'] = $validated['caracter_de_la_informacion'];
        }

        $expediente->update($camposOperativos);
        ReglasDocumentalesService::aplicarReglasExpediente($expediente, $validated);

        return response()->json(['message' => 'Expediente actualizado correctamente.']);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|string|in:en_tramite,en_concentracion,en_historico',
        ]);

        $expediente = Expediente::find($id);
        if (!$expediente) {
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        $expediente->estado = $validated['estado'];
        $expediente->save();

        return response()->json(['message' => 'Estado actualizado correctamente']);
    }

    public function indexBaja()
    {
        $expedientes = ExpedienteBaja::orderByDesc('fecha_baja')->get();

        return response()->json($expedientes);
    }

    public function batchByIds(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:expedientes,id',
        ]);

        $ids = $request->input('ids');

        $with = [
            'serie.seccion.fondo',
            'serie.seccion.subfondo',
            'subserie',
            'instrumentoControl',
            'unidadesDocumentales',
        ];

        $expedientes = Expediente::with($with)->whereIn('id', $ids)->get();

        $datos = $expedientes->map(function ($exp) {
            return array_merge(
                $exp->toArray(),
                ReglasDocumentalesService::reglasExpedienteParaApi($exp),
                ['serie' => $exp->serie]
            );
        });

        return response()->json($datos);
    }
}
