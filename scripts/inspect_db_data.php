<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Fondo;
use App\Models\Seccion;
use App\Models\Serie;
use App\Models\Expediente;
use App\Models\Caratula;

$log = [];
$log[] = "=== FONDOS ===";
foreach (Fondo::all() as $f) {
    $log[] = "Fondo [ID: {$f->id}] Cod: {$f->codigo} - Name: {$f->nombre}";
}

$log[] = "\n=== SECCIONES ===";
foreach (Seccion::all() as $s) {
    $log[] = "Seccion [ID: {$s->id}] Cod: {$s->codigo} - Name: {$s->nombre} (Fondo ID: {$s->id_fondo})";
}

$log[] = "\n=== SERIES ===";
foreach (Serie::all() as $sr) {
    $log[] = "Serie [ID: {$sr->id}] Cod: {$sr->codigo} - Name: {$sr->nombre} (Seccion ID: {$sr->id_seccion})";
}

$log[] = "\n=== EXPEDIENTES (table expedientes) ===";
$exps = Expediente::all();
$log[] = "Total expedientes: " . $exps->count();
foreach ($exps as $e) {
    $log[] = "Expediente [ID: {$e->id}] Cod: {$e->codigo} | Clave: {$e->clave} | Name: {$e->nombre} | Serie ID: " . ($e->id_serie ?? 'NULL') . " | Dep: " . ($e->id_dependencia ?? 'NULL') . " | Dept: " . ($e->id_departamento ?? 'NULL');
}

$log[] = "\n=== CARATULAS (table caratulas) ===";
$cars = Caratula::all();
$log[] = "Total carátulas: " . $cars->count();
foreach ($cars as $c) {
    $log[] = "Caratula [ID: {$c->id}] Titulo: {$c->titulo_expediente} | CodClasif: {$c->codigo_clasificacion_archivistica} | InstrCtrl ID: " . ($c->id_instrumento_control ?? 'NULL') . " | Dep: " . ($c->id_dependencia ?? 'NULL');
}

file_put_contents(__DIR__ . '/inspect_result.txt', implode("\n", $log));
echo "DONE_INSPECT";
