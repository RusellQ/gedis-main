<?php

namespace App\Services;

use App\Models\OficioConsecutivo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OficioNumeroService
{
    /**
     * Genera el número de oficio con la misma lógica que la clave de expediente:
     * partes de clasificación del emisor + consecutivo + año, unidos con "/".
     *
     * Expediente: fondo[/subfondo]/sección/serie[/subserie]/codigo/año
     * Oficio:     dependencia[/departamento]/consecutivo/año
     *
     * Ej.: SEGOB/AGE/49/2026
     */
    public function generar(User $emisor, $fecha = null): string
    {
        $fecha = $fecha ? Carbon::parse($fecha) : now();
        $anio = (int) $fecha->format('Y');
        $prefijo = $this->resolverPrefijo($emisor);

        return DB::transaction(function () use ($prefijo, $anio) {
            $fila = OficioConsecutivo::query()
                ->where('prefijo', $prefijo)
                ->where('anio', $anio)
                ->lockForUpdate()
                ->first();

            if (!$fila) {
                $created = OficioConsecutivo::create([
                    'prefijo' => $prefijo,
                    'anio' => $anio,
                    'ultimo_numero' => 0,
                    'updated_at' => now(),
                ]);
                $fila = OficioConsecutivo::query()
                    ->where('id', $created->id)
                    ->lockForUpdate()
                    ->first();
            }

            $siguiente = ((int) $fila->ultimo_numero) + 1;
            $fila->ultimo_numero = $siguiente;
            $fila->updated_at = now();
            $fila->save();

            // Misma idea que ExpedienteController::store → implode('/', array_filter([...]))
            $partes = array_filter([
                ...explode('/', $prefijo),
                (string) $siguiente,
                (string) $anio,
            ]);

            return implode('/', $partes);
        });
    }

    /**
     * Prefijo organizativo del emisor (claves de dependencia y departamento),
     * análogo a los códigos de fondo/sección/serie en la clave de expediente.
     */
    public function resolverPrefijo(User $emisor): string
    {
        $emisor->loadMissing(['dependencia', 'departamento']);

        $partes = array_filter([
            $emisor->dependencia->clave ?? '',
            $emisor->departamento->clave ?? '',
        ]);

        if ($partes === []) {
            return 'OFICIO';
        }

        return implode('/', $partes);
    }
}
