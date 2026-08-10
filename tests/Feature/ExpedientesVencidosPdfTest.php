<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExpedientesVencidosPdfTest extends TestCase
{
    public function test_pdf_view_renders_expected_content(): void
    {
        $view = view('pdf.expedientes_vencidos', [
            'expedientes' => [],
            'serieSeleccionada' => null,
            'fechaDesde' => null,
            'fechaHasta' => null,
            'valorDocumental' => null,
            'busqueda' => null,
        ]);

        $html = $view->render();

        $this->assertStringContainsString('Reporte de expedientes vencidos', $html);
        $this->assertStringContainsString('No se encontraron expedientes', $html);
        $this->assertStringContainsString(public_path('assets/QRletras.png'), $html);
        $this->assertStringContainsString(public_path('assets/logo.png'), $html);
        $this->assertStringNotContainsString('../assets/QRletras.png', $html);
    }
}
