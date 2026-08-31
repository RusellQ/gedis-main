<?php

namespace Tests\Feature;

use App\Models\Correspondencia;
use App\Models\CorrespondenciaFirma;
use App\Models\Expediente;
use App\Models\User;
use App\Services\SATSigningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class CorrespondenciaTest extends TestCase
{
    /**
     * Test de renderizado de la plantilla PDF de correspondencia
     */
    public function test_pdf_view_renders_correspondencia_content(): void
    {
        $usuario = new User([
            'id' => 1,
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'email' => 'juan@example.com',
        ]);

        $expediente = new Expediente([
            'id' => 10,
            'codigo' => 'EXP-2026-001',
            'nombre' => 'Expediente de Prueba',
        ]);

        $correspondencia = new Correspondencia([
            'id' => 1,
            'id_expediente' => 10,
            'numero_consecutivo' => 1,
            'id_usuario_autor' => 1,
            'remitente' => 'Secretaría General',
            'destinatario' => 'Dirección de Archivo',
            'asunto' => 'Oficio de Solicitud de Información',
            'contenido' => '<p>Por medio del presente me dirijo a usted...</p>',
            'clasificacion' => Correspondencia::CLASIFICACION_PUBLICA,
            'estado' => Correspondencia::ESTADO_BORRADOR,
            'fecha_creacion' => now(),
        ]);

        $correspondencia->setRelation('usuarioAutor', $usuario);
        $correspondencia->setRelation('expediente', $expediente);

        $view = view('pdf.correspondencia', [
            'correspondencia' => $correspondencia,
        ]);

        $html = $view->render();

        $this->assertStringContainsString('Oficio de Solicitud de Información', $html);
        $this->assertStringContainsString('Secretaría General', $html);
        $this->assertStringContainsString('Dirección de Archivo', $html);
        $this->assertStringContainsString('Por medio del presente me dirijo a usted...', $html);
        $this->assertStringContainsString('EXP-2026-001', $html);
    }

    /**
     * Test de lógica de negocio del modelo Correspondencia
     */
    public function test_correspondencia_model_logic(): void
    {
        $corr = new Correspondencia([
            'estado' => Correspondencia::ESTADO_BORRADOR,
        ]);

        $this->assertTrue($corr->esEditable());
        $this->assertFalse($corr->estaFirmado());
        $this->assertFalse($corr->tieneFirmaValida());

        $corr->estado = Correspondencia::ESTADO_FIRMADO;
        $this->assertFalse($corr->esEditable());
        $this->assertTrue($corr->estaFirmado());
    }

    /**
     * Test de servicio de firma SAT (Simulación / Sandbox)
     */
    public function test_sat_signing_service_sandbox(): void
    {
        $usuario = new User([
            'id' => 1,
            'nombre' => 'Admin',
            'apellido_paterno' => 'Test',
        ]);

        $expediente = new Expediente([
            'id' => 5,
            'codigo' => 'EXP-005',
            'nombre' => 'Expediente Test',
        ]);

        $corr = new Correspondencia([
            'id' => 99,
            'id_expediente' => 5,
            'numero_consecutivo' => 1,
            'id_usuario_autor' => 1,
            'asunto' => 'Documento para firmar',
            'contenido' => '<p>Contenido oficial</p>',
            'clasificacion' => Correspondencia::CLASIFICACION_PUBLICA,
            'estado' => Correspondencia::ESTADO_BORRADOR,
            'fecha_creacion' => now(),
        ]);

        $corr->setRelation('expediente', $expediente);

        $service = new SATSigningService();
        
        // Autenticar mock user para audit log
        $this->actingAs($usuario);

        // Sin credenciales SAT en .env, usa sandbox
        $resultado = $service->firmar($corr);

        $this->assertTrue($resultado['exito']);
        $this->assertEquals('Documento firmado exitosamente.', $resultado['mensaje']);
    }

    /**
     * Test que valida que un documento firmado no pueda ser editado o firmado nuevamente
     */
    public function test_sat_signing_service_rejects_already_signed_document(): void
    {
        $corr = new Correspondencia([
            'id' => 100,
            'estado' => Correspondencia::ESTADO_FIRMADO,
        ]);

        $service = new SATSigningService();
        $resultado = $service->firmar($corr);

        $this->assertFalse($resultado['exito']);
        $this->assertStringContainsString('El documento ya está firmado', $resultado['mensaje']);
    }
}