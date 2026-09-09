<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Fondo;
use App\Models\Seccion;
use App\Models\Serie;
use App\Models\Expediente;

echo "=== FONDOS ===\n";
foreach (Fondo::all() as $f) {
    echo "Fondo ID: {$f->id} | Código: {$f->codigo} | Nombre: {$f->nombre}\n";
}

echo "\n=== SECCIONES ===\n";
foreach (Seccion::all() as $s) {
    echo "Sección ID: {$s->id} | Código: {$s->codigo} | Nombre: {$s->nombre} | Fondo ID: {$s->id_fondo} | Subfondo ID: {$s->id_subfondo}\n";
}

echo "\n=== SERIES ===\n";
foreach (Serie::all() as $sr) {
    echo "Serie ID: {$sr->id} | Código: {$sr->codigo} | Nombre: {$sr->nombre} | Sección ID: {$sr->id_seccion}\n";
}

echo "\n=== EXPEDIENTES ===\n";
$exps = Expediente::all();
echo "Total expedientes: " . $exps->count() . "\n";
foreach ($exps as $e) {
    echo "Expediente ID: {$e->id} | Código: {$e->codigo} | Nombre: {$e->nombre} | Serie ID: {$e->id_serie} | Subserie ID: {$e->id_subserie}\n";
}
