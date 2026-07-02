<?php

namespace App\Http\Controllers;

use App\Models\Serie;
use App\Services\InstrumentoControlService;
use App\Services\ReglasDocumentalesService;
use Illuminate\Http\Request;

class ReglasDocumentalesController extends Controller
{
    public function porSerie(int $id)
    {
        $serie = Serie::find($id);

        if (!$serie) {
            return response()->json(['message' => 'Serie no encontrada'], 404);
        }

        $instrumento = InstrumentoControlService::resolverPorSerie($id);

        return response()->json($this->formatearRespuesta($serie, $instrumento));
    }

    public function porClave(Request $request)
    {
        $request->validate([
            'clave' => 'required|string',
        ]);

        $instrumento = InstrumentoControlService::resolverPorExpedienteClave($request->clave);

        if (!$instrumento) {
            return response()->json(['message' => 'No se encontró instrumento de control para la clave indicada'], 404);
        }

        $serie = $instrumento->relationLoaded('serie')
            ? $instrumento->serie
            : $instrumento->serie()->first();

        return response()->json($this->formatearRespuesta($serie, $instrumento));
    }

    private function formatearRespuesta(?Serie $serie, $instrumento): array
    {
        return [
            'serie' => $serie ? [
                'id' => $serie->id,
                'nombre' => $serie->nombre,
                'codigo' => $serie->codigo,
                'plazo_ta' => $serie->plazo_ta,
                'plazo_at' => $serie->plazo_at,
                'plazo_ac' => $serie->plazo_ac,
                'vigencia_ad' => (bool) $serie->vigencia_ad,
                'vigencia_le' => (bool) $serie->vigencia_le,
                'vigencia_confi' => (bool) $serie->vigencia_confi,
                'clasificacion_r' => $serie->clasificacion_r,
                'clasificacion_c' => $serie->clasificacion_c,
            ] : null,
            'instrumento' => $instrumento ? [
                'id' => $instrumento->id,
                'nombre_elemento' => $instrumento->nombre_elemento,
                'codigo_clasificacion' => $instrumento->codigo_clasificacion,
                'fundamento_legal' => $instrumento->fundamento_legal,
                'periodo_reserva' => $instrumento->periodo_reserva,
            ] : null,
            'reglas' => [
                'valor_documental' => [
                    'administrativo' => (bool) ($serie?->vigencia_ad),
                    'legal' => (bool) ($serie?->vigencia_le),
                    'contable' => (bool) ($serie?->vigencia_confi),
                ],
                'vigencia_documental' => $serie?->plazo_ta !== null ? (string) $serie->plazo_ta : null,
                'anios_archivo_tramite' => $serie?->plazo_at,
                'anios_archivo_concentracion' => $serie?->plazo_ac,
                'clasificacion' => ReglasDocumentalesService::clasificacionDesdeSerie($serie),
                'caracter' => ReglasDocumentalesService::caracterDesdeSerie($serie),
                'fundamento_legal' => $instrumento?->fundamento_legal,
                'periodo_reserva' => $instrumento?->periodo_reserva,
                'serie_nombre' => $serie?->nombre,
                'serie_codigo' => $serie?->codigo,
                'instrumento_nombre' => $instrumento?->nombre_elemento,
                'instrumento_codigo' => $instrumento?->codigo_clasificacion,
            ],
        ];
    }
}
