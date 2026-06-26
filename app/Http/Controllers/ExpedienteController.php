<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use App\Models\Serie;
use App\Models\Subserie;
use App\Models\User;
use App\Models\ExpedienteBaja;   // Modelo para la tabla expedientes_baja
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;


class ExpedienteController extends Controller
{

    public function darDeBaja(Request $request, $id)
    {
        // 1. Validar motivo (opcional) ─ cambia reglas si lo necesitas
        $validated = $request->validate([
            'motivo_baja' => 'nullable|string|max:500',
        ]);

        // 2. Obtener expediente (o 404)
        $expediente = Expediente::findOrFail($id);

        // 3. Transacción: copiar y luego eliminar
        DB::transaction(function () use ($expediente, $validated) {
            // Copiamos todo a expedientes_baja
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
                'estado'               => $expediente->estado,      // Valor final antes de baja
                'fecha_creacion'       => $expediente->fecha_creacion,
                'fecha_apertura'       => $expediente->fecha_apertura,
                'fecha_cierre'         => $expediente->fecha_cierre,
                'created_at'           => $expediente->created_at,
                'updated_at'           => $expediente->updated_at,
                'preservacion'         => $expediente->preservacion,
                'clave'                => $expediente->clave,

                // Campos adicionales
                'motivo_baja'          => $validated['motivo_baja'] ?? null,
                'fecha_baja'           => now(),
                'user_baja_id'         => Auth::id(),
            ]);

            // Eliminamos el original
            $expediente->delete();
        });

        return response()->json(['message' => 'Expediente dado de baja con exito '], 201);
    }


    public function getExpedienteById($id)
    {
        $expediente = Expediente::find($id);

        if (!$expediente) {
            return response()->json(['message' => 'Expediente no encontrado'], 404);
        }

        return response()->json($expediente);
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
            'fecha_cierre'           => 'nullable|date',//|after_or_equal:fecha_apertura',
            'fecha_creacion'         => 'nullable|date',
            'tiempo_conservacion'    => 'nullable|numeric',
            'vigencia'               => 'nullable|numeric',
            'no_legajos'             => 'nullable|integer|min:0',
            'no_hojas'               => 'nullable|integer|min:0',
            'no_caja'                => 'nullable|integer|min:0',
            'ubicacion_topografica'  => 'nullable|string|max:255',
            'clasificacion'          => 'required|in:Pública,Reservada,Confidencial',
            'caracter'               => 'required|in:Administrativo,Legal,Contable',
            'preservacion'           => 'nullable|string|max:255',
            'observacion'            => 'nullable|string|max:1000',
            'informacion_reservada_y_o_confidencial' => 'nullable|string|max:100',
            'caracter_de_la_informacion' => 'nullable|string|max:255',
            'fecha_clasificacion'    => 'nullable|date',
            'fundamento_legal'       => 'nullable|string',
            'periodo_reserva'        => 'nullable|integer|min:0',
            'ampliacion_periodo_reserva' => 'nullable|integer|min:0',
            'rubrica_titular_de_la_unidad' => 'nullable|string|max:255',
            'fecha_desclasificacion' => 'nullable|date',
            //'descripcion_expediente' => 'nullable|string',
            //'tradicion_documental'   => 'nullable|in:Original,Copia',
            //'soporte_documental'     => 'nullable|in:Original,Copia',
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

        $expediente = Expediente::create([
            'nombre'                 => $request->nombre,
            'codigo'                 => $request->codigo,
            'clave'                  => $clave,
            'id_serie'               => $request->id_serie,
            'id_subserie'            => $request->id_subserie,
            'estado'                 => $request->estado,
            'fecha_apertura'         => $request->fecha_apertura,
            'fecha_cierre'           => $request->fecha_cierre,
            'fecha_creacion'         => $request->fecha_creacion,
            'tiempo_conservacion'    => $request->tiempo_conservacion,
            'vigencia'               => $request->vigencia,
            'no_legajos'             => $request->no_legajos,
            'no_hojas'               => $request->no_hojas,
            'no_caja'                => $request->no_caja,
            'ubicacion_topografica'  => $request->ubicacion_topografica,
            'clasificacion'          => $request->clasificacion,
            'caracter'               => $request->caracter,
            'preservacion'           => $request->preservacion,
            'observacion'            => $request->observacion,
            'informacion_reservada_y_o_confidencial' => $request->informacion_reservada_y_o_confidencial,
            'caracter_de_la_informacion' => $request->caracter_de_la_informacion,
            'fecha_clasificacion'    => $request->fecha_clasificacion,
            'fundamento_legal'       => $request->fundamento_legal,
            'periodo_reserva'        => $request->periodo_reserva,
            'ampliacion_periodo_reserva' => $request->ampliacion_periodo_reserva,
            'rubrica_titular_de_la_unidad' => $request->rubrica_titular_de_la_unidad,
            'fecha_desclasificacion' => $request->fecha_desclasificacion,
            //'descripcion_expediente' => $request->descripcion_expediente,
            //'tradicion_documental'   => $request->tradicion_documental,
            //'soporte_documental'     => $request->soporte_documental,
            'id_dependencia'         => $user->id_dependencia,
            'id_departamento'        => $user->id_departamento,
        ]);

        return response()->json([
            'message'    => 'Expediente creado correctamente.',
            'expediente' => $expediente,
        ], 201);
    }

    
   public function index()
{
    try {
        $user = Auth::user();

        // Relaciones necesarias para cargar con los expedientes
        $with = [
            'serie.seccion.fondo',
            'serie.seccion.subfondo',
            'subserie',
            'unidadesDocumentales',
        ];

        // Mostrar todos los expedientes si el usuario es admin o root
        if (/*$user->hasRole('admin') ||*/ $user->hasRole('root')) {
            $expedientes = Expediente::with($with)->get();
        } else {
            // Validar sólo si NO es admin ni root
            if (!$user->id_dependencia || !$user->id_departamento) {
                return response()->json(['error' => 'El usuario no tiene asignada una dependencia o departamento.'], 403);
            }

            $expedientes = Expediente::where('id_dependencia', $user->id_dependencia)
                ->where('id_departamento', $user->id_departamento)
                ->with($with)
                ->get();
        }

        // Formatear los datos
        $datos = $expedientes->map(function ($exp) {
            return [
                'id'                     => $exp->id,
                'nombre'                 => $exp->nombre,
                'codigo'                 => $exp->codigo,
                'estado'                 => $exp->estado,
                'fecha_creacion'         => $exp->fecha_creacion,
                'fecha_apertura'         => $exp->fecha_apertura,
                'fecha_cierre'           => $exp->fecha_cierre,
                'clave'                  => $exp->clave,
                'tiempo_conservacion'    => $exp->tiempo_conservacion,
                'no_legajos'             => $exp->no_legajos,
                'no_hojas'               => $exp->no_hojas,
                'no_caja'                => $exp->no_caja,
                'ubicacion_topografica'  => $exp->ubicacion_topografica,
                'clasificacion'          => $exp->clasificacion,
                'caracter'               => $exp->caracter,
                'preservacion'           => $exp->preservacion,
                'observacion'            => $exp->observacion,
                'informacion_reservada_y_o_confidencial' => $exp->informacion_reservada_y_o_confidencial,
                'caracter_de_la_informacion' => $exp->caracter_de_la_informacion,
                'fecha_clasificacion'    => $exp->fecha_clasificacion,
                'fundamento_legal'       => $exp->fundamento_legal,
                'periodo_reserva'        => $exp->periodo_reserva,
                'ampliacion_periodo_reserva' => $exp->ampliacion_periodo_reserva,
                'rubrica_titular_de_la_unidad' => $exp->rubrica_titular_de_la_unidad,
                'fecha_desclasificacion' => $exp->fecha_desclasificacion,

                // Jerarquía archivística
                'id_serie'              => $exp->id_serie,
                'id_subserie'           => $exp->id_subserie,
                'fondo'                 => optional(optional($exp->serie)->seccion->fondo)->nombre,
                'subfondo'              => optional(optional($exp->serie)->seccion->subfondo)->nombre,
                'seccion'               => optional(optional($exp->serie)->seccion)->nombre,
                'serie'                 => optional($exp->serie)->nombre,
                'subserie'              => optional($exp->subserie)->nombre,
                'serie_detalle'         => $exp->serie ? [
                    'id'             => $exp->serie->id,
                    'nombre'         => $exp->serie->nombre,
                    'codigo'         => $exp->serie->codigo,
                    'vigencia_ad'    => (bool) $exp->serie->vigencia_ad,
                    'vigencia_le'    => (bool) $exp->serie->vigencia_le,
                    'vigencia_confi' => (bool) $exp->serie->vigencia_confi,
                    'plazo_at'       => $exp->serie->plazo_at,
                    'plazo_ac'       => $exp->serie->plazo_ac,
                ] : null,
                'subserie_detalle'      => $exp->subserie ? [
                    'id'     => $exp->subserie->id,
                    'nombre' => $exp->subserie->nombre,
                    'codigo' => $exp->subserie->codigo,
                ] : null,

                // Unidades documentales
                'unidades_documentales'  => $exp->unidadesDocumentales->map(function ($u) {
                    return [
                        'id'          => $u->id,
                        'tipo'        => $u->tipo,
                        'nombre'      => $u->nombre,
                        'descripcion' => $u->descripcion,
                        'archivo'     => $u->archivo,
                    ];
                }),
            ];
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
        // Solo expedientes de la serie que NO tienen subserie
        $expedientes = Expediente::where('id_serie', $id)
            ->whereNull('id_subserie')      // 👈 filtro clave
            ->get();

        return response()->json($expedientes);
    }


    public function porSubserie($id)
    {
        $expedientes = Expediente::where('id_subserie', $id)->get();
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
            'periodo_reserva' => 'nullable|integer|min:0',
            'ampliacion_periodo_reserva' => 'nullable|integer|min:0',
            'rubrica_titular_de_la_unidad' => 'nullable|string|max:255',
            'fecha_desclasificacion' => 'nullable|date',
            'clave' => 'nullable|string|max:255',
        ]);

        $expediente = Expediente::findOrFail($id);
        $expediente->update($validated);

        return response()->json(['message' => 'Expediente actualizado correctamente.']);
    }

    public function cambiarEstado(Request $request, $id)
    {
        // Validar que venga el campo 'estado' y que sea uno de los permitidos
        $validated = $request->validate([
            'estado' => 'required|string|in:en_tramite,en_concentracion,en_historico',
        ]);

        // Buscar el expediente
        $expediente = Expediente::find($id);
        if (!$expediente) {
            return response()->json(['error' => 'Expediente no encontrado'], 404);
        }

        // Cambiar el estado
        $expediente->estado = $validated['estado'];
        $expediente->save();

        return response()->json(['message' => 'Estado actualizado correctamente']);
    }


    public function indexBaja()
    {

        $expedientes = ExpedienteBaja::orderByDesc('fecha_baja')->get();

        return response()->json($expedientes);
    }

    /**
     * Devuelve varios expedientes por IDs (batch) con relaciones útiles
     */
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
            'unidadesDocumentales',
        ];

        $expedientes = Expediente::with($with)->whereIn('id', $ids)->get();

        // Devolver tal cual para que el frontend pueda decidir cómo agregar/usar
        return response()->json($expedientes);
    }
}
