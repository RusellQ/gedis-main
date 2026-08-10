<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Expediente;

$exps = Expediente::with('serie')->limit(50)->get();
foreach ($exps as $exp) {
    $serie = $exp->serie;
    echo sprintf(
        "%s | %s | %s | cierre=%s | serie=%s | plazo_ta=%s | plazo_at=%s | plazo_ac=%s\n",
        $exp->id,
        $exp->nombre,
        $exp->codigo,
        $exp->fecha_cierre ?? 'NULL',
        $serie?->nombre ?? 'NULL',
        $serie?->plazo_ta ?? 'NULL',
        $serie?->plazo_at ?? 'NULL',
        $serie?->plazo_ac ?? 'NULL'
    );
}
