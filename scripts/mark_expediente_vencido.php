<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Expediente;
use Carbon\Carbon;

$rows = Expediente::with('serie')->whereNotNull('fecha_cierre')->orderBy('fecha_cierre')->take(50)->get();
foreach ($rows as $exp) {
    $serie = $exp->serie;
    $plazo = null;
    if ($serie) {
        $plazo = $serie->plazo_ta ?: ($serie->plazo_at ?: $serie->plazo_ac);
    }
    $fechaV = $exp->fecha_cierre ? Carbon::parse($exp->fecha_cierre)->addYears($plazo) : null;
    $v = $fechaV && $fechaV->lte(Carbon::today()) ? 'SI' : 'NO';
    $serieNombre = $serie ? $serie->nombre : 'N/A';
    echo sprintf("%d | %s | serie_id=%s | fecha_cierre=%s | plazo=%s | vencido=%s | serie=%s\n", $exp->id, $exp->nombre, $exp->id_serie, $exp->fecha_cierre, $plazo ?? 'NULL', $v, $serieNombre);
}
