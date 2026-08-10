<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Expediente;
use Carbon\Carbon;

$id = 46;
$exp = Expediente::find($id);
if (!$exp) {
    echo "Expediente $id no encontrado\n";
    exit(1);
}

$antes = [
    'fecha_cierre' => $exp->fecha_cierre,
    'id_serie' => $exp->id_serie,
    'nombre' => $exp->nombre,
    'codigo' => $exp->codigo,
];

$exp->fecha_cierre = Carbon::parse('2010-01-01')->toDateString();
$exp->save();

$serie = $exp->serie;
$plazo = $serie?->plazo_ta ?: ($serie?->plazo_at ?: $serie?->plazo_ac);
$fechaV = $exp->fecha_cierre ? Carbon::parse($exp->fecha_cierre)->addYears($plazo) : null;
$vencido = $fechaV && $fechaV->lte(Carbon::today()) ? 'SI' : 'NO';

echo "Expediente actualizado:\n";
echo "id={$exp->id}\n";
echo "nombre={$exp->nombre}\n";
echo "codigo={$exp->codigo}\n";
echo "id_serie={$exp->id_serie}\n";
echo "fecha_cierre_antes={$antes['fecha_cierre']}\n";
echo "fecha_cierre_despues={$exp->fecha_cierre}\n";
echo "plazo={$plazo}\n";
echo "fecha_vencimiento={$fechaV?->toDateString()}\n";
echo "vencido={$vencido}\n";
