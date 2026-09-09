<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Fondo;
use App\Models\Seccion;
use App\Models\Serie;
use App\Models\Expediente;
use App\Models\Caratula;

echo "=== FONDOS ===\n";
foreach (Fondo::all() as $f) {
    echo "Fondo [ID: {$f->id}] Cod: {$f->codigo} - {$f->nombre}\n";
}

echo "\n=== SECCIONES ===\n";
foreach (Seccion::all() as $s) {
    echo "Seccion [ID: {$s->id}] Cod: {$s->codigo} - {$s->nombre} (Fondo ID: {$s->id_fondo})\n";
}

echo "\n=== SERIES ===\n";
foreach (Serie::all() as $sr) {
    echo "Serie [ID: {$sr->id}] Cod: {$sr->codigo} - {$sr->nombre} (Seccion ID: {$sr->id_seccion})\n";
}

echo "\n=== EXPEDIENTES ===\n";
$exps = Expediente::with('serie.seccion.fondo')->get();
echo "Total expedientes: " . $exps->count() . "\n";
foreach ($exps as $e) {
    echo "Expediente [ID: {$e->id}] Cod: {$e->codigo} | Clave: {$e->clave} - {$e->nombre}\n";
    echo "  -> Serie ID: " . ($e->id_serie ?? 'NULL') . " (" . ($e->serie->nombre ?? 'Sin serie') . ")\n";
    echo "  -> Seccion ID: " . ($e->serie->seccion->id ?? 'NULL') . " (" . ($e->serie->seccion->nombre ?? 'Sin sección') . ")\n";
    echo "  -> Fondo ID: " . ($e->serie->seccion->fondo->id ?? 'NULL') . " (" . ($e->serie->seccion->fondo->nombre ?? 'Sin fondo') . ")\n";
}

echo "\n=== CARATULAS ===\n";
$cars = Caratula::all();
echo "Total carátulas: " . $cars->count() . "\n";
foreach ($cars as $c) {
    echo "Carátula [ID: {$c->id}] Titulo: {$c->titulo_expediente} | CodClasif: {$c->codigo_clasificacion_archivistica}\n";
}
