<?php

namespace Tests\Unit;

use App\Models\Departamento;
use App\Models\Dependencia;
use App\Models\User;
use App\Services\OficioNumeroService;
use Tests\TestCase;

class OficioNumeroServiceTest extends TestCase
{
    public function test_resolver_prefijo_usa_claves_de_dependencia_y_departamento(): void
    {
        $service = new OficioNumeroService();
        $user = new User(['nombre' => 'Test']);
        $user->setRelation('dependencia', new Dependencia(['clave' => 'SEGOB']));
        $user->setRelation('departamento', new Departamento(['clave' => 'AGE']));

        $this->assertSame('SEGOB/AGE', $service->resolverPrefijo($user));
    }

    public function test_resolver_prefijo_fallback_sin_claves(): void
    {
        $service = new OficioNumeroService();
        $user = new User(['nombre' => 'Test']);

        $this->assertSame('OFICIO', $service->resolverPrefijo($user));
    }
}
