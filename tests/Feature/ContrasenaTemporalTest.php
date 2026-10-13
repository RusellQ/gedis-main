<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContrasenaTemporalTest extends TestCase
{
    private array $userIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        // APP_URL en .env es "localhost" (sin esquema) y el cliente de prueba
        // arma http://localhost/localhost/... La app real usa el host de la petición.
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
    }

    protected function tearDown(): void
    {
        if ($this->userIds !== []) {
            User::whereIn('id', $this->userIds)->delete();
        }

        parent::tearDown();
    }

    public function test_alta_y_primer_inicio_obligan_a_cambiar_la_contrasena(): void
    {
        $email = 'prueba-temporal-'.uniqid().'@example.com';
        $temporal = 'Temporal1';

        $this->postJson('/users', $this->datosAlta($email, $temporal))
            ->assertOk()
            ->assertJsonFragment(['message' => '¡Usuario creado exitosamente! La contraseña es temporal y deberá cambiarla en su primer inicio de sesión.']);

        $user = User::where('email', $email)->firstOrFail();
        $this->userIds[] = $user->id;
        $this->assertTrue($user->debe_cambiar_password);

        $this->post('/login', [
            'email' => $email,
            'password' => $temporal,
        ])->assertRedirect(route('password.cambiar'));

        $this->get('/dashboard')->assertRedirect(route('password.cambiar'));
        $this->get('/cambiar-contrasena')
            ->assertOk()
            ->assertSee('Cambia tu contraseña')
            ->assertSee('Cerrar sesión');

        $this->from('/cambiar-contrasena')->post('/cambiar-contrasena', [
            'password' => 'corta',
            'password_confirmation' => 'corta',
        ])->assertRedirect('/cambiar-contrasena')
            ->assertSessionHasErrors([
                'password' => 'La contraseña debe tener al menos 8 caracteres.',
            ]);

        $this->from('/cambiar-contrasena')->post('/cambiar-contrasena', [
            'password' => 'Definitiva1',
            'password_confirmation' => 'OtraClave1',
        ])->assertRedirect('/cambiar-contrasena')
            ->assertSessionHasErrors([
                'password' => 'La confirmación no coincide con la contraseña.',
            ]);

        $this->from('/cambiar-contrasena')->post('/cambiar-contrasena', [
            'password' => $temporal,
            'password_confirmation' => $temporal,
        ])->assertRedirect('/cambiar-contrasena')
            ->assertSessionHasErrors([
                'password' => 'La contraseña nueva debe ser distinta de la temporal.',
            ]);

        $this->assertTrue($user->fresh()->debe_cambiar_password);

        $this->from('/cambiar-contrasena')->post('/cambiar-contrasena', [
            'password' => 'Definitiva1',
            'password_confirmation' => 'Definitiva1',
        ])->assertRedirect('/dashboard');

        $this->assertFalse($user->fresh()->debe_cambiar_password);
        $this->assertTrue(Hash::check('Definitiva1', $user->fresh()->password));

        $this->post('/logout')->assertRedirect('/');

        $this->post('/login', [
            'email' => $email,
            'password' => 'Definitiva1',
        ])->assertRedirect('/dashboard');

        $this->get('/cambiar-contrasena')->assertRedirect('/dashboard');
    }

    public function test_usuario_sin_marca_entra_al_tablero(): void
    {
        $email = 'prueba-existente-'.uniqid().'@example.com';
        $user = $this->crearUsuario($email, 'Definitiva1', false);

        $this->post('/login', [
            'email' => $email,
            'password' => 'Definitiva1',
        ])->assertRedirect('/dashboard');

        $this->assertFalse($user->fresh()->debe_cambiar_password);
    }

    public function test_listado_solo_marca_temporal_si_cambia_la_contrasena(): void
    {
        $email = 'prueba-listado-'.uniqid().'@example.com';
        $user = $this->crearUsuario($email, 'Definitiva1', false);

        $this->putJson('/users/'.$user->id, $this->datosEdicion($user, 'Temporal99'))
            ->assertOk();

        $user->refresh();
        $this->assertTrue($user->debe_cambiar_password);
        $this->assertTrue(Hash::check('Temporal99', $user->password));

        $this->putJson('/users/'.$user->id, array_merge($this->datosEdicion($user, 'corta'), [
            'password' => 'corta',
        ]))->assertStatus(422)
            ->assertJsonPath('errors.password.0', 'La contraseña temporal debe tener al menos 8 caracteres.');

        $this->putJson('/users/'.$user->id, $this->datosEdicion($user, null))
            ->assertOk();

        $user->refresh();
        $this->assertTrue($user->debe_cambiar_password);
        $this->assertTrue(Hash::check('Temporal99', $user->password));

        $this->post('/login', [
            'email' => $email,
            'password' => 'Temporal99',
        ])->assertRedirect(route('password.cambiar'));
    }

    private function crearUsuario(string $email, string $password, bool $temporal): User
    {
        $user = User::create([
            'nombre' => 'Prueba',
            'apellido_paterno' => 'Temporal',
            'apellido_materno' => 'Gedis',
            'email' => $email,
            'password' => Hash::make($password),
            'debe_cambiar_password' => $temporal,
            'roles' => ['lector'],
            'id_dependencia' => 2,
            'id_departamento' => 1,
        ]);
        $this->userIds[] = $user->id;

        return $user;
    }

    private function datosAlta(string $email, string $password): array
    {
        return [
            'nombre' => 'Prueba',
            'apellido_paterno' => 'Temporal',
            'apellido_materno' => 'Gedis',
            'email' => $email,
            'password' => $password,
            'roles' => ['lector'],
            'id_dependencia' => 2,
            'id_departamento' => 1,
        ];
    }

    private function datosEdicion(User $user, ?string $password): array
    {
        $datos = [
            'nombre' => 'Prueba',
            'apellido_paterno' => 'Temporal',
            'apellido_materno' => 'Gedis',
            'email' => $user->email,
            'roles' => ['lector'],
        ];

        if ($password !== null) {
            $datos['password'] = $password;
        }

        return $datos;
    }
}
