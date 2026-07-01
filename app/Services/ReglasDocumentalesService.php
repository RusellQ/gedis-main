<?php

namespace App\Services;

use App\Models\Caratula;
use App\Models\Expediente;
use App\Models\InstrumentoControl;
use App\Models\Serie;

class ReglasDocumentalesService
{
    public static function clasificacionDesdeSerie(?Serie $serie): string
    {
        if (!$serie) {
            return 'Pública';
        }

        if ($serie->clasificacion_c) {
            return 'Confidencial';
        }

        if ($serie->clasificacion_r) {
            return 'Reservada';
        }

        return 'Pública';
    }

    public static function caracterDesdeSerie(?Serie $serie): ?string
    {
        if (!$serie) {
            return null;
        }

        if ($serie->vigencia_ad) {
            return 'Administrativo';
        }

        if ($serie->vigencia_le) {
            return 'Legal';
        }

        if ($serie->vigencia_confi) {
            return 'Contable';
        }

        return null;
    }

    public static function reglasExpedienteParaApi(Expediente $expediente): array
    {
        $serie = $expediente->relationLoaded('serie')
            ? $expediente->serie
            : $expediente->serie()->first();

        $instrumento = $expediente->relationLoaded('instrumentoControl')
            ? $expediente->instrumentoControl
            : ($expediente->id_instrumento_control
                ? InstrumentoControl::find($expediente->id_instrumento_control)
                : InstrumentoControlService::resolverPorSerie($expediente->id_serie));

        return [
            'tiempo_conservacion' => $serie?->plazo_ta,
            'vigencia' => $serie?->plazo_at,
            'clasificacion' => self::clasificacionDesdeSerie($serie),
            'caracter' => self::caracterDesdeSerie($serie),
            'fundamento_legal' => $instrumento?->fundamento_legal,
            'periodo_reserva' => $instrumento?->periodo_reserva,
            'ampliacion_periodo_reserva' => null,
        ];
    }

    public static function aplicarReglasExpediente(Expediente $expediente, array $data): void
    {
        $serie = $expediente->serie()->first();
        $instrumento = $expediente->id_instrumento_control
            ? InstrumentoControl::find($expediente->id_instrumento_control)
            : InstrumentoControlService::resolverPorSerie($expediente->id_serie);

        if (!$instrumento && $serie) {
            $instrumento = InstrumentoControlService::crearParaSerie($serie);
            $expediente->update(['id_instrumento_control' => $instrumento->id]);
        }

        if ($instrumento) {
            $cambiosInstrumento = [];

            if (array_key_exists('fundamento_legal', $data)) {
                $cambiosInstrumento['fundamento_legal'] = $data['fundamento_legal'];
            }

            if (array_key_exists('periodo_reserva', $data)) {
                $cambiosInstrumento['periodo_reserva'] = $data['periodo_reserva'];
            }

            if ($cambiosInstrumento) {
                $instrumento->update($cambiosInstrumento);
            }
        }

        if (!$serie) {
            return;
        }

        $cambiosSerie = [];

        if (array_key_exists('tiempo_conservacion', $data)) {
            $cambiosSerie['plazo_ta'] = $data['tiempo_conservacion'];
        }

        if (array_key_exists('vigencia', $data)) {
            $cambiosSerie['plazo_at'] = $data['vigencia'];
        }

        if (array_key_exists('clasificacion', $data)) {
            $cambiosSerie['clasificacion_r'] = $data['clasificacion'] === 'Reservada' ? 1 : 0;
            $cambiosSerie['clasificacion_c'] = $data['clasificacion'] === 'Confidencial' ? 1 : 0;
        }

        if (array_key_exists('caracter', $data)) {
            $cambiosSerie['vigencia_ad'] = $data['caracter'] === 'Administrativo' ? 1 : 0;
            $cambiosSerie['vigencia_le'] = $data['caracter'] === 'Legal' ? 1 : 0;
            $cambiosSerie['vigencia_confi'] = $data['caracter'] === 'Contable' ? 1 : 0;
        }

        if ($cambiosSerie) {
            $serie->update($cambiosSerie);
        }
    }

    public static function reglasCaratulaParaApi(Caratula $caratula): array
    {
        $instrumento = $caratula->relationLoaded('instrumentoControl')
            ? $caratula->instrumentoControl
            : ($caratula->id_instrumento_control
                ? InstrumentoControl::with('serie')->find($caratula->id_instrumento_control)
                : InstrumentoControlService::resolverPorExpedienteClave($caratula->codigo_clasificacion_archivistica));

        $serie = $instrumento?->serie;

        return [
            'valor_documental_administrativo' => $serie?->vigencia_ad ? 1 : 0,
            'valor_documental_legal' => $serie?->vigencia_le ? 1 : 0,
            'valor_documental_contable' => $serie?->vigencia_confi ? 1 : 0,
            'vigencia_documental' => $serie?->plazo_ta !== null ? (string) $serie->plazo_ta : null,
            'anios_archivo_tramite' => $serie?->plazo_at,
            'anios_archivo_concentracion' => $serie?->plazo_ac,
            'fundamento_legal' => $instrumento?->fundamento_legal,
            'periodo_reserva' => $instrumento?->periodo_reserva,
        ];
    }

    public static function aplicarReglasCaratula(Caratula $caratula, array $data): void
    {
        $instrumento = $caratula->id_instrumento_control
            ? InstrumentoControl::with('serie')->find($caratula->id_instrumento_control)
            : InstrumentoControlService::resolverPorExpedienteClave($caratula->codigo_clasificacion_archivistica);

        if (!$instrumento) {
            return;
        }

        if (!$caratula->id_instrumento_control) {
            $caratula->update(['id_instrumento_control' => $instrumento->id]);
        }

        $cambiosInstrumento = [];

        if (array_key_exists('fundamento_legal', $data)) {
            $cambiosInstrumento['fundamento_legal'] = $data['fundamento_legal'];
        }

        if (array_key_exists('periodo_reserva', $data)) {
            $cambiosInstrumento['periodo_reserva'] = $data['periodo_reserva'];
        }

        if ($cambiosInstrumento) {
            $instrumento->update($cambiosInstrumento);
        }

        $serie = $instrumento->serie;

        if (!$serie) {
            return;
        }

        $cambiosSerie = [];

        if (isset($data['valor_documental_administrativo']) || isset($data['valor_documental']['administrativo'])) {
            $cambiosSerie['vigencia_ad'] = !empty($data['valor_documental_administrativo'] ?? $data['valor_documental']['administrativo'] ?? false) ? 1 : 0;
        }

        if (isset($data['valor_documental_legal']) || isset($data['valor_documental']['legal'])) {
            $cambiosSerie['vigencia_le'] = !empty($data['valor_documental_legal'] ?? $data['valor_documental']['legal'] ?? false) ? 1 : 0;
        }

        if (isset($data['valor_documental_contable']) || isset($data['valor_documental']['contable'])) {
            $cambiosSerie['vigencia_confi'] = !empty($data['valor_documental_contable'] ?? $data['valor_documental']['contable'] ?? false) ? 1 : 0;
        }

        if (array_key_exists('vigencia_documental', $data) && $data['vigencia_documental'] !== null && $data['vigencia_documental'] !== '') {
            $cambiosSerie['plazo_ta'] = (int) $data['vigencia_documental'];
        }

        if (array_key_exists('anios_archivo_tramite', $data)) {
            $cambiosSerie['plazo_at'] = $data['anios_archivo_tramite'];
        }

        if (array_key_exists('anios_archivo_concentracion', $data)) {
            $cambiosSerie['plazo_ac'] = $data['anios_archivo_concentracion'];
        }

        if ($cambiosSerie) {
            $serie->update($cambiosSerie);
        }
    }

    public static function formatearExpedienteParaApi(Expediente $exp): array
    {
        $reglas = self::reglasExpedienteParaApi($exp);

        return array_merge([
            'id' => $exp->id,
            'nombre' => $exp->nombre,
            'codigo' => $exp->codigo,
            'estado' => $exp->estado,
            'fecha_creacion' => $exp->fecha_creacion,
            'fecha_apertura' => $exp->fecha_apertura,
            'fecha_cierre' => $exp->fecha_cierre,
            'clave' => $exp->clave,
            'no_legajos' => $exp->no_legajos,
            'no_hojas' => $exp->no_hojas,
            'no_caja' => $exp->no_caja,
            'ubicacion_topografica' => $exp->ubicacion_topografica,
            'preservacion' => $exp->preservacion,
            'observacion' => $exp->observacion,
            'informacion_reservada_y_o_confidencial' => $exp->informacion_reservada_y_o_confidencial,
            'caracter_de_la_informacion' => $exp->informacion_reservada_y_o_confidencial,
            'fecha_clasificacion' => $exp->fecha_clasificacion,
            'rubrica_titular_de_la_unidad' => $exp->rubrica_titular_de_la_unidad,
            'fecha_desclasificacion' => $exp->fecha_desclasificacion,
            'id_serie' => $exp->id_serie,
            'id_subserie' => $exp->id_subserie,
            'id_instrumento_control' => $exp->id_instrumento_control,
        ], $reglas);
    }
}
