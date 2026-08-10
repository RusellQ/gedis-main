<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\Seccion;
use App\Models\Serie;
use App\Models\Subfondo;
use App\Models\Subserie;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DashboardStatsController extends Controller
{
    private const ESTADOS = ['en_tramite', 'en_concentracion', 'en_historico'];

    private const TIPOS_GRAFICA = ['pie', 'bar', 'polarArea'];

    private const AGRUPACIONES = ['fondo', 'subfondo', 'seccion', 'serie', 'subserie', 'estado'];

    private const MAX_SEGMENTOS = 12;

    private const LABELS_ESTADO = [
        'en_tramite' => 'En trámite',
        'en_concentracion' => 'En concentración',
        'en_historico' => 'En histórico',
    ];

    private const LABELS_TIPO = [
        'pie' => 'Gráfica de pastel',
        'bar' => 'Gráfica de barras',
        'polarArea' => 'Gráfica de área polar',
    ];

    private const LABELS_AGRUPA = [
        'fondo' => 'por fondo',
        'subfondo' => 'por subfondo',
        'seccion' => 'por sección',
        'serie' => 'por serie',
        'subserie' => 'por subserie',
        'estado' => 'por tipo de expediente',
    ];

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $validated = $this->validarFiltros($request);
        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        $alcance = $this->resolverAlcanceUsuario($user);
        if ($alcance instanceof \Illuminate\Http\JsonResponse) {
            return $alcance;
        }

        return response()->json($this->construirEstadisticas($validated, $user, $alcance));
    }

    public function pdf(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $validated = $this->validarFiltros($request, true);
        if ($validated instanceof \Illuminate\Http\JsonResponse) {
            return $validated;
        }

        if (!preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $validated['chart_image'])) {
            return response()->json(['message' => 'La imagen de la gráfica no es válida.'], 422);
        }

        $alcance = $this->resolverAlcanceUsuario($user);
        if ($alcance instanceof \Illuminate\Http\JsonResponse) {
            return $alcance;
        }

        $stats = $this->construirEstadisticas($validated, $user, $alcance);

        if ($stats['total'] < 1) {
            return response()->json([
                'message' => 'No hay datos para generar el PDF con el filtro seleccionado.',
            ], 422);
        }

        $titulo = 'ESTADÍSTICAS DEL SISTEMA — ' . mb_strtoupper($stats['dimension_label']);
        $alcanceLabel = $alcance === 'global'
            ? 'Alcance: global (todas las dependencias)'
            : 'Alcance: su dependencia / departamento';

        $pdf = Pdf::loadView('pdf.dashboard-estadisticas', [
            'titulo' => $titulo,
            'subtituloTipo' => self::LABELS_TIPO[$validated['tipo_grafica']],
            'dimensionLabel' => $stats['filtro_resumen'],
            'alcanceLabel' => $alcanceLabel,
            'chartImage' => $validated['chart_image'],
            'labels' => $stats['labels'],
            'data' => $stats['data'],
            'total' => $stats['total'],
        ])->setPaper('letter', 'portrait');

        return $pdf->download('estadisticas_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * @return array|\Illuminate\Http\JsonResponse
     */
    private function validarFiltros(Request $request, bool $paraPdf = false)
    {
        // Normaliza query/body: axios a veces manda escalar en lugar de array.
        $request->merge([
            'estados' => $this->aLista($request->input('estados')),
            'id_fondos' => $this->aLista($request->input('id_fondos')),
            'id_subfondos' => $this->aLista($request->input('id_subfondos')),
            'id_secciones' => $this->aLista($request->input('id_secciones')),
            'id_series' => $this->aLista($request->input('id_series')),
            'id_subseries' => $this->aLista($request->input('id_subseries')),
        ]);

        $rules = [
            'estados' => ['required', 'array', 'min:1'],
            'estados.*' => [Rule::in(self::ESTADOS)],
            'id_fondos' => ['nullable', 'array'],
            'id_fondos.*' => ['integer'],
            'id_subfondos' => ['nullable', 'array'],
            'id_subfondos.*' => ['integer'],
            'id_secciones' => ['nullable', 'array'],
            'id_secciones.*' => ['integer'],
            'id_series' => ['nullable', 'array'],
            'id_series.*' => ['integer'],
            'id_subseries' => ['nullable', 'array'],
            'id_subseries.*' => ['integer'],
        ];

        if ($paraPdf) {
            $rules['tipo_grafica'] = ['required', Rule::in(self::TIPOS_GRAFICA)];
            $rules['chart_image'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        $fondos = array_values(array_unique(array_map('intval', $validated['id_fondos'] ?? [])));
        $subfondos = array_values(array_unique(array_map('intval', $validated['id_subfondos'] ?? [])));
        $secciones = array_values(array_unique(array_map('intval', $validated['id_secciones'] ?? [])));
        $series = array_values(array_unique(array_map('intval', $validated['id_series'] ?? [])));
        $subseries = array_values(array_unique(array_map('intval', $validated['id_subseries'] ?? [])));
        $estados = array_values(array_unique($validated['estados']));

        // Cascada: niveles opcionales (subfondo/subserie) y obligatorios relativos.
        if ($subfondos !== [] && $fondos === []) {
            return response()->json([
                'message' => 'Debe seleccionar al menos un fondo antes de filtrar por subfondo.',
            ], 422);
        }

        if ($secciones !== [] && $fondos === []) {
            return response()->json([
                'message' => 'Debe seleccionar al menos un fondo antes de filtrar por sección.',
            ], 422);
        }

        if ($series !== [] && $secciones === []) {
            return response()->json([
                'message' => 'Debe seleccionar al menos una sección antes de filtrar por serie.',
            ], 422);
        }

        if ($subseries !== [] && $series === []) {
            return response()->json([
                'message' => 'Debe seleccionar al menos una serie antes de filtrar por subserie.',
            ], 422);
        }

        if ($subfondos !== [] && $fondos !== []) {
            $invalidos = Subfondo::query()
                ->whereIn('id', $subfondos)
                ->whereNotIn('id_fondo', $fondos)
                ->pluck('id');
            if ($invalidos->isNotEmpty()) {
                return response()->json([
                    'message' => 'Hay subfondos que no pertenecen a los fondos seleccionados. Respete Fondo → (Subfondo).',
                ], 422);
            }
        }

        if ($secciones !== [] && $fondos !== []) {
            if ($subfondos !== []) {
                // Con subfondos elegidos: la sección debe colgar de esos subfondos.
                $invalidas = Seccion::query()
                    ->whereIn('id', $secciones)
                    ->where(function ($q) use ($subfondos) {
                        $q->whereNull('id_subfondo')
                            ->orWhereNotIn('id_subfondo', $subfondos);
                    })
                    ->pluck('id');
                if ($invalidas->isNotEmpty()) {
                    return response()->json([
                        'message' => 'Hay secciones que no pertenecen a los subfondos seleccionados. Respete (Subfondo) → Sección.',
                    ], 422);
                }
            } else {
                // Sin subfondos: basta pertenecer a los fondos (vía id_fondo; incluye secciones con o sin subfondo).
                $invalidas = Seccion::query()
                    ->whereIn('id', $secciones)
                    ->whereNotIn('id_fondo', $fondos)
                    ->pluck('id');
                if ($invalidas->isNotEmpty()) {
                    return response()->json([
                        'message' => 'Hay secciones que no pertenecen a los fondos seleccionados. Respete Fondo → Sección.',
                    ], 422);
                }
            }
        }

        if ($series !== [] && $secciones !== []) {
            $invalidas = Serie::query()
                ->whereIn('id', $series)
                ->whereNotIn('id_seccion', $secciones)
                ->pluck('id');
            if ($invalidas->isNotEmpty()) {
                return response()->json([
                    'message' => 'Hay series que no pertenecen a las secciones seleccionadas. Respete Sección → Serie.',
                ], 422);
            }
        }

        if ($subseries !== [] && $series !== []) {
            $invalidas = Subserie::query()
                ->whereIn('id', $subseries)
                ->whereNotIn('id_serie', $series)
                ->pluck('id');
            if ($invalidas->isNotEmpty()) {
                return response()->json([
                    'message' => 'Hay subseries que no pertenecen a las series seleccionadas. Respete Serie → (Subserie).',
                ], 422);
            }
        }

        $validated['id_fondos'] = $fondos;
        $validated['id_subfondos'] = $subfondos;
        $validated['id_secciones'] = $secciones;
        $validated['id_series'] = $series;
        $validated['id_subseries'] = $subseries;
        $validated['estados'] = $estados;
        $validated['agrupar_por'] = $this->resolverAgrupacionAutomatica($validated);

        return $validated;
    }

    /**
     * Convierte escalar o lista en array limpio (soporta GET mal serializado).
     */
    private function aLista(mixed $valor): array
    {
        if ($valor === null || $valor === '' || $valor === []) {
            return [];
        }

        if (!is_array($valor)) {
            return [$valor];
        }

        return array_values(array_filter($valor, static fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * Sin selector "agrupar por": los tipos de expediente y el nivel de jerarquía deciden.
     * - Varios tipos → segmentos por tipo (trámite / concentración / histórico).
     * - Subfondo y subserie solo agrupan si el usuario los eligió explícitamente.
     * - Con series (aunque sea una) → por serie, hasta que elija subserie.
     */
    private function resolverAgrupacionAutomatica(array $filtros): string
    {
        $estados = $filtros['estados'] ?? [];
        $fondos = $filtros['id_fondos'] ?? [];
        $subfondos = $filtros['id_subfondos'] ?? [];
        $secciones = $filtros['id_secciones'] ?? [];
        $series = $filtros['id_series'] ?? [];
        $subseries = $filtros['id_subseries'] ?? [];

        if (count($estados) > 1) {
            return 'estado';
        }

        // Opcional: solo si el usuario eligió subseries.
        if ($subseries !== []) {
            return 'subserie';
        }

        // Con series elegidas (1 o más) → por serie; no saltar a subserie sola.
        if ($series !== []) {
            return 'serie';
        }

        if ($secciones !== []) {
            return 'serie';
        }

        // Opcional: solo si el usuario eligió subfondos.
        if ($subfondos !== []) {
            return 'subfondo';
        }

        if ($fondos !== []) {
            return 'seccion';
        }

        return 'fondo';
    }

    /**
     * @return string|\Illuminate\Http\JsonResponse
     */
    private function resolverAlcanceUsuario(User $user)
    {
        if ($user->hasRole('root')) {
            return 'global';
        }

        if (!$user->id_dependencia || !$user->id_departamento) {
            return response()->json([
                'message' => 'El usuario no tiene asignada una dependencia o departamento.',
            ], 403);
        }

        return 'dependencia';
    }

    private function construirEstadisticas(array $filtros, User $user, string $alcance): array
    {
        $agrupaPor = $filtros['agrupar_por'];
        $pairs = $this->agregarExpedientes($filtros, $user, $alcance, $agrupaPor);
        $pairs = $this->limitarSegmentos($pairs);

        $labels = array_column($pairs, 'label');
        $data = array_map('intval', array_column($pairs, 'total'));
        $total = array_sum($data);

        return [
            'agrupa_por' => $agrupaPor,
            'estados' => $filtros['estados'],
            'dimension_label' => $this->armarTitulo($filtros, $agrupaPor),
            'filtro_resumen' => $this->armarResumenFiltro($filtros, $agrupaPor),
            'labels' => $labels,
            'data' => $data,
            'total' => $total,
            'alcance' => $alcance,
        ];
    }

    private function agregarExpedientes(array $filtros, User $user, string $alcance, string $agrupaPor): array
    {
        $query = Expediente::query()->from('expedientes');

        if ($alcance === 'dependencia') {
            $query->where('expedientes.id_dependencia', $user->id_dependencia)
                ->where('expedientes.id_departamento', $user->id_departamento);
        }

        $query->whereIn('expedientes.estado', $filtros['estados']);

        $query->leftJoin('series', 'expedientes.id_serie', '=', 'series.id')
            ->leftJoin('secciones', 'series.id_seccion', '=', 'secciones.id')
            ->leftJoin('fondos', 'secciones.id_fondo', '=', 'fondos.id')
            ->leftJoin('subfondos', 'secciones.id_subfondo', '=', 'subfondos.id')
            ->leftJoin('subseries', 'expedientes.id_subserie', '=', 'subseries.id');

        // Jerarquía Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente
        // Subfondo/subserie vacíos = no excluir expedientes sin ese nivel opcional.
        if ($filtros['id_fondos'] !== []) {
            $query->whereIn('secciones.id_fondo', $filtros['id_fondos']);
        }
        if ($filtros['id_subfondos'] !== []) {
            $query->whereIn('secciones.id_subfondo', $filtros['id_subfondos']);
        }
        if ($filtros['id_secciones'] !== []) {
            $query->whereIn('series.id_seccion', $filtros['id_secciones']);
        }
        if ($filtros['id_series'] !== []) {
            $query->whereIn('expedientes.id_serie', $filtros['id_series']);
        }
        if ($filtros['id_subseries'] !== []) {
            $query->whereIn('expedientes.id_subserie', $filtros['id_subseries']);
        }

        return $this->agruparYContar($query, $agrupaPor);
    }

    private function agruparYContar(Builder $query, string $agrupaPor): array
    {
        switch ($agrupaPor) {
            case 'fondo':
                $query->select(
                    DB::raw("COALESCE(NULLIF(fondos.nombre, ''), 'Sin clasificar') as label"),
                    DB::raw('COUNT(DISTINCT expedientes.id) as total')
                )->groupBy('fondos.id', 'fondos.nombre');
                break;
            case 'subfondo':
                $query->select(
                    DB::raw("COALESCE(NULLIF(subfondos.nombre, ''), 'Sin subfondo') as label"),
                    DB::raw('COUNT(DISTINCT expedientes.id) as total')
                )->groupBy('subfondos.id', 'subfondos.nombre');
                break;
            case 'seccion':
                $query->select(
                    DB::raw("COALESCE(NULLIF(secciones.nombre, ''), 'Sin clasificar') as label"),
                    DB::raw('COUNT(DISTINCT expedientes.id) as total')
                )->groupBy('secciones.id', 'secciones.nombre');
                break;
            case 'serie':
                $query->select(
                    DB::raw("COALESCE(NULLIF(series.nombre, ''), 'Sin clasificar') as label"),
                    DB::raw('COUNT(DISTINCT expedientes.id) as total')
                )->groupBy('series.id', 'series.nombre');
                break;
            case 'subserie':
                $query->select(
                    DB::raw("COALESCE(NULLIF(subseries.nombre, ''), 'Sin subserie') as label"),
                    DB::raw('COUNT(DISTINCT expedientes.id) as total')
                )->groupBy('subseries.id', 'subseries.nombre');
                break;
            case 'estado':
                $query->select(
                    'expedientes.estado as estado_key',
                    DB::raw('COUNT(DISTINCT expedientes.id) as total')
                )->groupBy('expedientes.estado');
                break;
            default:
                return [];
        }

        $rows = $query->orderByDesc('total')->get();

        if ($agrupaPor === 'estado') {
            return $rows->map(fn ($row) => [
                'label' => self::LABELS_ESTADO[$row->estado_key] ?? ($row->estado_key ?: 'Sin estado'),
                'total' => (int) $row->total,
            ])->all();
        }

        return $rows->map(fn ($row) => [
            'label' => $row->label ?: 'Sin clasificar',
            'total' => (int) $row->total,
        ])->all();
    }

    private function armarTitulo(array $filtros, string $agrupaPor): string
    {
        $tipos = collect($filtros['estados'])
            ->map(fn ($e) => self::LABELS_ESTADO[$e] ?? $e)
            ->implode(', ');

        return "Expedientes ({$tipos}) " . (self::LABELS_AGRUPA[$agrupaPor] ?? '');
    }

    private function armarResumenFiltro(array $filtros, string $agrupaPor): string
    {
        $partes = [];
        $tipos = collect($filtros['estados'])
            ->map(fn ($e) => self::LABELS_ESTADO[$e] ?? $e)
            ->implode(', ');
        $partes[] = 'Tipos: ' . $tipos;
        $partes[] = 'Gráfica ' . (self::LABELS_AGRUPA[$agrupaPor] ?? $agrupaPor);

        if ($filtros['id_fondos'] !== []) {
            $nombres = DB::table('fondos')->whereIn('id', $filtros['id_fondos'])->pluck('nombre')->all();
            $partes[] = 'Fondos: ' . implode(', ', $nombres);
        }
        if ($filtros['id_subfondos'] !== []) {
            $nombres = DB::table('subfondos')->whereIn('id', $filtros['id_subfondos'])->pluck('nombre')->all();
            $partes[] = 'Subfondos: ' . implode(', ', $nombres);
        }
        if ($filtros['id_secciones'] !== []) {
            $nombres = DB::table('secciones')->whereIn('id', $filtros['id_secciones'])->pluck('nombre')->all();
            $partes[] = 'Secciones: ' . implode(', ', $nombres);
        }
        if ($filtros['id_series'] !== []) {
            $nombres = DB::table('series')->whereIn('id', $filtros['id_series'])->pluck('nombre')->all();
            $partes[] = 'Series: ' . implode(', ', $nombres);
        }
        if ($filtros['id_subseries'] !== []) {
            $nombres = DB::table('subseries')->whereIn('id', $filtros['id_subseries'])->pluck('nombre')->all();
            $partes[] = 'Subseries: ' . implode(', ', $nombres);
        }

        return implode(' · ', $partes);
    }

    private function limitarSegmentos(array $pairs): array
    {
        if (count($pairs) <= self::MAX_SEGMENTOS) {
            return $pairs;
        }

        $top = array_slice($pairs, 0, self::MAX_SEGMENTOS - 1);
        $resto = array_slice($pairs, self::MAX_SEGMENTOS - 1);
        $otros = array_sum(array_column($resto, 'total'));
        $top[] = ['label' => 'Otros', 'total' => $otros];

        return $top;
    }
}
