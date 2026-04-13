<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expediente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{

public function dashboard()
{
    try {
        $usuario = Auth::user();

        if (!$usuario->area_asignada) {
            return response()->json(['error' => 'El usuario no tiene un área asignada.'], 403);
        }

        $area = trim($usuario->area_asignada);
        $hoy = now();
        $seisMesesDespues = now()->addMonths(6);

        $expedientesVencidos = Expediente::where('ubicacion_topografica', $area)
            ->whereNotNull('fecha_cierre')
            ->where('fecha_cierre', '<=', $hoy)
            ->get();

        $expedientesPorVencer = Expediente::where('ubicacion_topografica', $area)
            ->whereNotNull('fecha_cierre')
            ->whereBetween('fecha_cierre', [$hoy->copy()->addDay(), $seisMesesDespues])
            ->get();

        return view('dashboard', compact('expedientesVencidos', 'expedientesPorVencer'));

    } catch (\Throwable $e) {
        Log::error('Error al cargar el dashboard', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Ocurrió un error al cargar el dashboard.'], 500);
    }
}



}
