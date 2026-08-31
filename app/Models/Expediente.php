<?php

namespace App\Models;

use App\Services\ReglasDocumentalesService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class Expediente extends Model
{
    protected $table = 'expedientes';

    protected $fillable = [
        'nombre',
        'codigo',
        'clave',
        'id_serie',
        'id_subserie',
        'id_instrumento_control',
        'estado',
        'fecha_apertura',
        'fecha_cierre',
        'fecha_creacion',
        'no_legajos',
        'no_hojas',
        'no_caja',
        'ubicacion_topografica',
        'preservacion',
        'observacion',
        'informacion_reservada_y_o_confidencial',
        'fecha_clasificacion',
        'rubrica_titular_de_la_unidad',
        'fecha_desclasificacion',
        'id_dependencia',
        'id_departamento',
    ];

    public function serie()
    {
        return $this->belongsTo(Serie::class, 'id_serie');
    }

    public function subserie()
    {
        return $this->belongsTo(Subserie::class, 'id_subserie');
    }

    public function dependencia()
    {
        return $this->belongsTo(Dependencia::class, 'id_dependencia');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function instrumentoControl()
    {
        return $this->belongsTo(InstrumentoControl::class, 'id_instrumento_control');
    }

    public function unidadesDocumentales()
    {
        return $this->hasMany(UnidadDocumental::class, 'id_expediente');
    }

    public function getTiempoConservacionAttribute()
    {
        return $this->serie?->plazo_ta;
    }

    public function getVigenciaAttribute()
    {
        return $this->serie?->plazo_at;
    }

    public function getClasificacionAttribute()
    {
        return ReglasDocumentalesService::clasificacionDesdeSerie($this->serie);
    }

    public function getCaracterAttribute()
    {
        return ReglasDocumentalesService::caracterDesdeSerie($this->serie);
    }

    public function getFundamentoLegalAttribute()
    {
        return $this->instrumentoControl?->fundamento_legal;
    }

    public function getPeriodoReservaAttribute()
    {
        return $this->instrumentoControl?->periodo_reserva;
    }

    public function getAmpliacionPeriodoReservaAttribute()
    {
        return null;
    }

    /**
     * Siguiente código numérico del cajón (serie sin subserie, o una subserie).
     * No reutiliza huecos ni números de expedientes dados de baja.
     */
    public static function siguienteCodigoPara(int $serieId, ?int $subserieId = null): string
    {
        $serie = Serie::with(['seccion.fondo', 'seccion.subfondo'])->findOrFail($serieId);
        $subserie = null;

        if ($subserieId) {
            $subserie = Subserie::findOrFail($subserieId);
            if ((int) $subserie->id_serie !== $serieId) {
                throw new InvalidArgumentException('La subserie no pertenece a la serie indicada.');
            }
        }

        $vivos = static::query()
            ->where('id_serie', $serieId)
            ->when(
                $subserieId,
                fn ($q) => $q->where('id_subserie', $subserieId),
                fn ($q) => $q->whereNull('id_subserie')
            )
            ->pluck('codigo');

        $max = static::maxEnteroDeCodigos($vivos);

        if (Schema::hasTable('expedientes_baja')) {
            $prefix = implode('/', array_filter([
                $serie->seccion->fondo->codigo ?? '',
                optional($serie->seccion->subfondo)->codigo,
                $serie->seccion->codigo ?? '',
                $serie->codigo ?? '',
                $subserie?->codigo,
            ]));

            $bajas = ExpedienteBaja::query()
                ->where('id_serie', $serieId)
                ->get(['codigo', 'clave']);

            foreach ($bajas as $baja) {
                if (!static::bajaPerteneceAlCajon($baja->clave, $prefix)) {
                    continue;
                }

                $n = static::codigoAEntero($baja->codigo);
                if ($n === 0 && $baja->clave) {
                    $n = static::codigoAEntero((string) last(explode('/', $baja->clave)));
                }

                if ($n > $max) {
                    $max = $n;
                }
            }
        }

        return (string) ($max + 1);
    }

    private static function codigoAEntero(?string $codigo): int
    {
        if ($codigo === null) {
            return 0;
        }

        $codigo = trim($codigo);
        if ($codigo === '' || !preg_match('/^\d+$/', $codigo)) {
            return 0;
        }

        return (int) $codigo;
    }

    private static function maxEnteroDeCodigos($codigos): int
    {
        $max = 0;
        foreach ($codigos as $codigo) {
            $n = static::codigoAEntero($codigo);
            if ($n > $max) {
                $max = $n;
            }
        }

        return $max;
    }

    private static function bajaPerteneceAlCajon(?string $clave, string $prefix): bool
    {
        if ($prefix === '' || !$clave) {
            return false;
        }

        $needle = $prefix . '/';
        if (!str_starts_with($clave, $needle)) {
            return false;
        }

        $resto = substr($clave, strlen($needle));
        if ($resto === '') {
            return false;
        }

        $segmentos = explode('/', $resto);
        if (count($segmentos) === 1) {
            return true;
        }

        return count($segmentos) === 2 && preg_match('/^\d{4}$/', $segmentos[1]);
    }
}
