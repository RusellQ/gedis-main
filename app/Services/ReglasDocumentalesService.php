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

    public static function caracterInformacionDesdeSerie(?Serie $serie): ?string
    {
        return self::valorDocumentalTextoDesdeSerie($serie);
    }

    public static function valorDocumentalTextoDesdeSerie(?Serie $serie): ?string
    {
        if (!$serie) {
            return null;
        }

        $valores = [];

        if ($serie->vigencia_ad) {
            $valores[] = 'Administrativo';
        }
        if ($serie->vigencia_le) {
            $valores[] = 'Legal';
        }
        if ($serie->vigencia_confi) {
            $valores[] = 'Contable';
        }

        return $valores ? implode(', ', $valores) : null;
    }

    public static function resolverInstrumentoParaCaratula(Caratula $caratula): ?InstrumentoControl
    {
        if ($caratula->relationLoaded('instrumentoControl') && $caratula->instrumentoControl) {
            return $caratula->instrumentoControl;
        }

        if ($caratula->id_instrumento_control) {
            $instrumento = InstrumentoControl::with('serie')->find($caratula->id_instrumento_control);
            if ($instrumento) {
                return $instrumento;
            }
        }

        $instrumento = InstrumentoControlService::resolverPorExpedienteClave($caratula->codigo_clasificacion_archivistica);

        if ($instrumento && !$instrumento->relationLoaded('serie')) {
            $instrumento->load('serie');
        }

        return $instrumento;
    }

    public static function resolverSerieParaCaratula(Caratula $caratula): ?Serie
    {
        $instrumento = self::resolverInstrumentoParaCaratula($caratula);

        if ($instrumento?->relationLoaded('serie') && $instrumento->serie) {
            return $instrumento->serie;
        }

        if ($instrumento?->serie) {
            return $instrumento->serie;
        }

        if ($instrumento?->id_serie) {
            return Serie::find($instrumento->id_serie);
        }

        $expediente = Expediente::where('clave', $caratula->codigo_clasificacion_archivistica)->first();

        if ($expediente?->id_serie) {
            return Serie::find($expediente->id_serie);
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

    public static function filtrarReglasCaratulaDelPayload(array $data): array
    {
        $campos = [
            'valor_documental',
            'valor_documental_administrativo',
            'valor_documental_legal',
            'valor_documental_contable',
            'vigencia_documental',
            'anios_archivo_tramite',
            'anios_archivo_concentracion',
            'fundamento_legal',
            'periodo_reserva',
        ];

        return array_intersect_key($data, array_flip($campos));
    }

    public static function reglasCaratulaParaApi(Caratula $caratula): array
    {
        $instrumento = self::resolverInstrumentoParaCaratula($caratula);
        $serie = self::resolverSerieParaCaratula($caratula);

        return [
            'valor_documental_administrativo' => $serie?->vigencia_ad ? 1 : 0,
            'valor_documental_legal' => $serie?->vigencia_le ? 1 : 0,
            'valor_documental_contable' => $serie?->vigencia_confi ? 1 : 0,
            'valor_documental' => self::valorDocumentalTextoDesdeSerie($serie),
            'vigencia_documental' => $serie?->plazo_ta !== null ? (string) $serie->plazo_ta : null,
            'anios_archivo_tramite' => $serie?->plazo_at,
            'anios_archivo_concentracion' => $serie?->plazo_ac,
            'clasificacion' => self::clasificacionDesdeSerie($serie),
            'caracter_informacion' => self::caracterInformacionDesdeSerie($serie),
            'fundamento_legal' => $instrumento?->fundamento_legal,
            'periodo_reserva' => $instrumento?->periodo_reserva,
            'reglas_fuente' => [
                'codigo_clasificacion_archivistica' => $caratula->codigo_clasificacion_archivistica,
                'id_instrumento_control' => $caratula->id_instrumento_control ?? $instrumento?->id,
                'id_serie' => $serie?->id,
                'serie_codigo' => $serie?->codigo,
                'serie_nombre' => $serie?->nombre,
            ],
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

        if (array_key_exists('valor_documental_administrativo', $data)) {
            $cambiosSerie['vigencia_ad'] = !empty($data['valor_documental_administrativo']) ? 1 : 0;
        } elseif (isset($data['valor_documental']['administrativo'])) {
            $cambiosSerie['vigencia_ad'] = !empty($data['valor_documental']['administrativo']) ? 1 : 0;
        }

        if (array_key_exists('valor_documental_legal', $data)) {
            $cambiosSerie['vigencia_le'] = !empty($data['valor_documental_legal']) ? 1 : 0;
        } elseif (isset($data['valor_documental']['legal'])) {
            $cambiosSerie['vigencia_le'] = !empty($data['valor_documental']['legal']) ? 1 : 0;
        }

        if (array_key_exists('valor_documental_contable', $data)) {
            $cambiosSerie['vigencia_confi'] = !empty($data['valor_documental_contable']) ? 1 : 0;
        } elseif (isset($data['valor_documental']['contable'])) {
            $cambiosSerie['vigencia_confi'] = !empty($data['valor_documental']['contable']) ? 1 : 0;
        }

        if (array_key_exists('vigencia_documental', $data) && $data['vigencia_documental'] !== null && $data['vigencia_documental'] !== '') {
            $cambiosSerie['plazo_ta'] = (int) $data['vigencia_documental'];
        }

        if (array_key_exists('anios_archivo_tramite', $data) && $data['anios_archivo_tramite'] !== null && $data['anios_archivo_tramite'] !== '') {
            $cambiosSerie['plazo_at'] = $data['anios_archivo_tramite'];
        }

        if (array_key_exists('anios_archivo_concentracion', $data) && $data['anios_archivo_concentracion'] !== null && $data['anios_archivo_concentracion'] !== '') {
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
