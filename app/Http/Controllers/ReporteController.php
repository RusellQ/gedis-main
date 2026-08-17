<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Models\Fondo;
use App\Models\Subfondo;
use App\Models\Seccion;
use App\Models\Serie;
use App\Models\Subserie;

class ReporteController extends Controller
{
 public function filtrar(Request $request)
{
    $user = Auth::user();

    // 🔒 Seguridad: si no hay usuario autenticado
    if (!$user) {
        return response()->json([
            'message' => 'No autenticado'
        ], 401);
    }

    $query = Expediente::query();

    // 🔥 Filtrar por dependencia del usuario
    if (!empty($user->id_dependencia)) {
        $query->where('id_dependencia', $user->id_dependencia);
    }

    // Estado
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }

    // Fechas
    if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
        $query->whereBetween('fecha_apertura', [
            $request->fecha_inicio,
            $request->fecha_fin
        ]);
    } elseif ($request->filled('fecha_inicio')) {
        $query->whereDate('fecha_apertura', '>=', $request->fecha_inicio);
    } elseif ($request->filled('fecha_fin')) {
        $query->whereDate('fecha_apertura', '<=', $request->fecha_fin);
    }

    // Fondo
    if ($request->filled('id_fondo')) {
        $query->whereHas('serie.seccion', function ($q) use ($request) {
            $q->where('id_fondo', $request->id_fondo);
        });
    }

    // Subfondo
    if ($request->filled('id_subfondo')) {
        $query->whereHas('serie.seccion', function ($q) use ($request) {
            $q->where('id_subfondo', $request->id_subfondo);
        });
    }

    // Sección
    if ($request->filled('id_seccion')) {
        $query->whereHas('serie', function ($q) use ($request) {
            $q->where('id_seccion', $request->id_seccion);
        });
    }

    // Serie
    if ($request->filled('id_serie')) {
        $query->where('id_serie', $request->id_serie);
    }

    // Subserie
    if ($request->filled('id_subserie')) {
        $query->where('id_subserie', $request->id_subserie);
    }

    $expedientes = $query->with([
        'serie.seccion.fondo',
        'serie.seccion.subfondo',
        'subserie',
        'instrumentoControl',
        'unidadesDocumentales',
    ])->get();

    $expedientes->each->append(['clasificacion', 'caracter']);

    return response()->json($expedientes, 200);
}




public function generarPDF(Request $request)
{
    $user = Auth::user();
    $query = Expediente::query();

    // 🔥 Filtrar por la dependencia del usuario (no por ubicación_topografica)
    if ($user->id_dependencia) {
        $query->where('id_dependencia', $user->id_dependencia);
    }

    // Filtro por estado
    $estadoFiltro = null;
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
        $estadoFiltro = $request->estado;
    }

    // Fechas (flexible: inicio o fin o ambos)
    $fechaInicio = $request->fecha_inicio;
    $fechaFin = $request->fecha_fin;

    if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
        $query->whereBetween('fecha_apertura', [$request->fecha_inicio, $request->fecha_fin]);
    } elseif ($request->filled('fecha_inicio')) {
        $query->where('fecha_apertura', '>=', $request->fecha_inicio);
    } elseif ($request->filled('fecha_fin')) {
        $query->where('fecha_apertura', '<=', $request->fecha_fin);
    }

    // Filtro por fondo
    if ($request->filled('id_fondo')) {
        $query->whereHas('serie.seccion', function ($q) use ($request) {
            $q->where('id_fondo', $request->id_fondo);
        });
    }

    // Filtro por subfondo
    if ($request->filled('id_subfondo')) {
        $query->whereHas('serie.seccion', function ($q) use ($request) {
            $q->where('id_subfondo', $request->id_subfondo);
        });
    }

    // Filtro por sección
    if ($request->filled('id_seccion')) {
        $query->whereHas('serie', function ($q) use ($request) {
            $q->where('id_seccion', $request->id_seccion);
        });
    }

    // Filtro por serie
    if ($request->filled('id_serie')) {
        $query->where('id_serie', $request->id_serie);
    }

    // Filtro por subserie
    if ($request->filled('id_subserie')) {
        $query->where('id_subserie', $request->id_subserie);
    }

    // Cargar relaciones
    $expedientes = $query->with([
        'serie.seccion.fondo',
        'serie.seccion.subfondo',
        'subserie',
        'instrumentoControl',
        'unidadesDocumentales',
    ])->get();

    // Generar PDF
    $pdf = Pdf::loadView('pdf.expedientes', compact(
        'expedientes',
        'estadoFiltro',
        'fechaInicio',
        'fechaFin'
    ))->setPaper('legal', 'landscape');

    return $pdf->download('reporte_expedientes.pdf');
}

    public function fondos()
    {
        return response()->json($this->catalogoQuery(Fondo::query(), ['id', 'nombre', 'codigo'])->get(), 200);
    }

    public function subfondos()
    {
        return response()->json($this->catalogoQuery(Subfondo::query(), ['id', 'nombre', 'codigo', 'id_fondo'])->get(), 200);
    }

    public function secciones()
    {
        return response()->json($this->catalogoQuery(Seccion::query(), ['id', 'nombre', 'codigo', 'id_fondo', 'id_subfondo'])->get(), 200);
    }

    public function series()
    {
        return response()->json($this->catalogoQuery(Serie::query(), ['id', 'nombre', 'codigo', 'id_seccion'])->get(), 200);
    }

    public function subseries()
    {
        return response()->json($this->catalogoQuery(Subserie::query(), ['id', 'nombre', 'codigo', 'id_serie'])->get(), 200);
    }

    private function catalogoQuery($query, array $columns)
    {
        $user = Auth::user();
        $query->select($columns)->orderBy('nombre');

        if (!empty($user?->id_dependencia)) {
            $query->where('id_dependencia', $user->id_dependencia);
        }

        return $query;
    }
}
