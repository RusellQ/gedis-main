<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use App\Exports\InventarioExport;
use Maatwebsite\Excel\Facades\Excel;

class EstadisticasController extends Controller
{

    /*        public function obtenerEstadisticas(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            // Convertir las fechas a cadenas de texto en formato 'Y-m-d H:i:s'
            $start = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay()->format('Y-m-d H:i:s') : null;
            $end = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay()->format('Y-m-d H:i:s') : null;

            // Construir el filtro de consulta
            $filter = [];
            if ($start && $end) {
                $filter['created_at'] = [
                    '$gte' => $start,
                    '$lte' => $end
                ];
            }

            // Ejecutar consulta de agregación con filtro
            $query = DB::connection('mongodb')->collection('recursos_collection')
                ->raw(function ($collection) use ($filter) {
                    return $collection->aggregate([
                        ['$match' => $filter],
                        [
                            '$group' => [
                                '_id' => '$capturador',
                                'count' => ['$sum' => 1]
                            ]
                        ]
                    ]);
                });

            // Convertir el cursor en un array
            $estadisticasArray = iterator_to_array($query);

            return response()->json($estadisticasArray);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    } */


    public function obtenerEstadisticas(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            // Convertir las fechas a cadenas de texto en formato 'Y-m-d H:i:s'
            $start = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay()->format('Y-m-d H:i:s') : null;
            $end = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay()->format('Y-m-d H:i:s') : null;

            // Construir el filtro de consulta
            $filter = [];
            if ($start && $end) {
                $filter['created_at'] = [
                    '$gte' => $start,
                    '$lte' => $end
                ];
            }
            // Ejecutar consulta de agregación para la colección original y la colección desechados
            $query = DB::connection('mongodb')->collection('recursos_collection')
                ->raw(function ($collection) use ($filter) {
                    return $collection->aggregate([
                        ['$match' => $filter],
                        [
                            '$group' => [
                                '_id' => '$capturador',
                                'count' => ['$sum' => 1]
                            ]
                        ]
                    ]);
                });

            $queryDesechados = DB::connection('mongodb')->collection('desechados_collection')
                ->raw(function ($collection) use ($filter) {
                    return $collection->aggregate([
                        ['$match' => $filter],
                        [
                            '$group' => [
                                '_id' => '$capturador',
                                'count' => ['$sum' => 1]
                            ]
                        ]
                    ]);
                });

            // Convertir los cursores en arrays
            $estadisticasArray = iterator_to_array($query);
            $estadisticasDesechadosArray = iterator_to_array($queryDesechados);

            // Combinar las estadísticas de ambas colecciones
            $estadisticasCombinadas = [];
            foreach (array_merge($estadisticasArray, $estadisticasDesechadosArray) as $estadistica) {
                $capturador = $estadistica['_id'];
                if (!isset($estadisticasCombinadas[$capturador])) {
                    $estadisticasCombinadas[$capturador] = [
                        '_id' => $capturador,
                        'count' => 0
                    ];
                }
                $estadisticasCombinadas[$capturador]['count'] += $estadistica['count'];
            }

            // Convertir el array combinada a una lista
            $estadisticasFinales = array_values($estadisticasCombinadas);

            return response()->json($estadisticasFinales);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function mostrarInventario(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $query = DB::connection('mongodb')->collection('recursos_collection');

            // Si se proporcionan fechas, agrega filtros
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $inventario = $query->get();

            return response()->json($inventario);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportarInventario(Request $request)
{
    try {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = DB::connection('mongodb')->collection('recursos_collection');

        // Agrega los filtros si las fechas están presentes
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $inventario = $query->get();

        // Exportar el inventario a Excel
        return Excel::download(new InventarioExport($inventario), 'inventario.xlsx');
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }
}
