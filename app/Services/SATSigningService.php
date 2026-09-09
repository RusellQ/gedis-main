<?php

namespace App\Services;

use App\Models\Correspondencia;
use App\Models\CorrespondenciaFirma;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SATSigningService
{
    /**
     * Endpoint de API SAT para firma
     */
    protected string $satEndpoint;

    /**
     * Clave API para autenticación con SAT
     */
    protected string $satApiKey;

    /**
     * Ruta del certificado SAT
     */
    protected string $satCertPath;

    public function __construct()
    {
        $this->satEndpoint = config('services.sat.signing_endpoint', env('SAT_SIGNING_ENDPOINT')) ?? '';
        $this->satApiKey = config('services.sat.signing_api_key', env('SAT_SIGNING_API_KEY')) ?? '';
        $this->satCertPath = config('services.sat.cert_path', env('SAT_SIGNING_CERT_PATH')) ?? '';
    }

    /**
     * Firmar un documento de correspondencia
     *
     * @param Correspondencia $correspondencia
     * @return array Resultado con estado y detalles de la firma
     */
    public function firmar(Correspondencia $correspondencia): array
    {
        try {
            // Validar que el documento está en estado borrador
            if (!$correspondencia->esEditable()) {
                return [
                    'exito' => false,
                    'mensaje' => 'El documento ya está firmado. No se puede firmar nuevamente.',
                    'estado' => 'rechazada',
                ];
            }

            // Preparar documento para firma (XDP/XML)
            $documentoPrepado = $this->prepararDocumento($correspondencia);

            // Invocar API SAT
            $respuestaSAT = $this->invocarAPISAT($documentoPrepado, $correspondencia);

            if (!$respuestaSAT['exito']) {
                return $respuestaSAT;
            }

            // Guardar firma en BD
            $firma = $this->guardarFirma(
                $correspondencia,
                $respuestaSAT['certificado'],
                $respuestaSAT['timestamp'],
                $respuestaSAT['respuesta_completa']
            );

            // Cambiar estado a firmado
            $correspondencia->marcarComoFirmado();

            // Registrar auditoría
            $this->registrarAuditoria($correspondencia, 'firmar', 'Documento firmado exitosamente por SAT');

            return [
                'exito' => true,
                'mensaje' => 'Documento firmado exitosamente.',
                'id_firma' => $firma->id,
                'timestamp' => $firma->timestamp_firma,
            ];
        } catch (\Exception $e) {
            Log::error('Error al firmar documento de correspondencia', [
                'id_correspondencia' => $correspondencia->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'exito' => false,
                'mensaje' => 'Error al firmar el documento. Intente nuevamente.',
                'error_detail' => $e->getMessage(),
                'estado' => 'rechazada',
            ];
        }
    }

    /**
     * Preparar documento para firma (convertir a XDP/XML)
     *
     * @param Correspondencia $correspondencia
     * @return string XML preparado
     */
    protected function prepararDocumento(Correspondencia $correspondencia): string
    {
        // Construir XML con estructura XDP compatible con firma SAT
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><documento/>');

        // Metadatos del documento
        $metadatos = $xml->addChild('metadatos');
        $metadatos->addChild('id', $correspondencia->id);
        $metadatos->addChild('asunto', htmlspecialchars($correspondencia->asunto));
        $metadatos->addChild('remitente', htmlspecialchars($correspondencia->remitente ?? ''));
        $metadatos->addChild('destinatario', htmlspecialchars($correspondencia->destinatario ?? ''));
        $metadatos->addChild('clasificacion', $correspondencia->clasificacion);
        $metadatos->addChild('fecha_creacion', $correspondencia->fecha_creacion->toIso8601String());
        $metadatos->addChild('id_usuario_autor', $correspondencia->id_usuario_autor);

        // Contenido del documento
        $contenido = $xml->addChild('contenido');
        $contenido->addChild('html', base64_encode($correspondencia->contenido));

        // Información del expediente
        if ($correspondencia->expediente) {
            $expediente = $xml->addChild('expediente');
            $expediente->addChild('id', $correspondencia->expediente->id);
            $expediente->addChild('nombre', htmlspecialchars($correspondencia->expediente->nombre ?? ''));
            $expediente->addChild('codigo', htmlspecialchars($correspondencia->expediente->codigo ?? ''));
        }

        return $xml->asXML();
    }

    /**
     * Invocar API SAT para firmar
     *
     * @param string $documentoXML
     * @param Correspondencia $correspondencia
     * @return array
     */
    protected function invocarAPISAT(string $documentoXML, Correspondencia $correspondencia): array
    {
        // MODO SANDBOX: Si no hay credenciales SAT, simular firma exitosa
        if (!$this->satEndpoint || !$this->satApiKey) {
            return $this->simularFirmaSAT($correspondencia);
        }

        try {
            // Realizar petición a API SAT
            $respuesta = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->satApiKey,
                    'Content-Type' => 'application/xml',
                ])
                ->post($this->satEndpoint, [
                    'documento' => base64_encode($documentoXML),
                    'id_correspondencia' => $correspondencia->id,
                    'usuario_id' => auth()->id(),
                ])
                ->throw()
                ->json();

            // Validar respuesta
            if ($respuesta['estado'] !== 'exitosa') {
                return [
                    'exito' => false,
                    'mensaje' => $respuesta['mensaje'] ?? 'Error al firmar en SAT.',
                    'estado' => 'rechazada',
                    'respuesta_completa' => $respuesta,
                ];
            }

            return [
                'exito' => true,
                'certificado' => $respuesta['certificado'] ?? null,
                'timestamp' => $respuesta['timestamp'] ?? now(),
                'respuesta_completa' => $respuesta,
            ];
        } catch (\Exception $e) {
            Log::error('Error al contactar API SAT', [
                'error' => $e->getMessage(),
                'id_correspondencia' => $correspondencia->id,
            ]);

            return [
                'exito' => false,
                'mensaje' => 'Error de comunicación con SAT. Intente más tarde.',
                'estado' => 'pendiente',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Simular firma SAT (para desarrollo/sandbox)
     *
     * @param Correspondencia $correspondencia
     * @return array
     */
    protected function simularFirmaSAT(Correspondencia $correspondencia): array
    {
        Log::info('Usando simulación de firma SAT (SANDBOX)', [
            'id_correspondencia' => $correspondencia->id,
        ]);

        return [
            'exito' => true,
            'certificado' => 'CERT_SIM_' . bin2hex(random_bytes(16)),
            'timestamp' => now(),
            'respuesta_completa' => [
                'estado' => 'exitosa',
                'modo' => 'sandbox',
                'mensaje' => 'Firma simulada (SANDBOX)',
                'id_correspondencia' => $correspondencia->id,
            ],
        ];
    }

    /**
     * Guardar firma en la BD
     *
     * @param Correspondencia $correspondencia
     * @param string $certificado
     * @param \DateTime $timestamp
     * @param array $respuestaSAT
     * @return CorrespondenciaFirma
     */
    protected function guardarFirma(
        Correspondencia $correspondencia,
        ?string $certificado,
        $timestamp,
        array $respuestaSAT
    ): CorrespondenciaFirma {
        $firma = new CorrespondenciaFirma([
            'id_correspondencia' => $correspondencia->id,
            'id_usuario_firmante' => auth()->id(),
            'certificado_sat' => $certificado ? $this->cifrarCertificado($certificado) : null,
            'timestamp_firma' => $timestamp,
            'estado_firma' => CorrespondenciaFirma::ESTADO_EXITOSA,
            'respuesta_sat' => $respuestaSAT,
        ]);

        $firma->save();

        return $firma;
    }

    /**
     * Cifrar certificado para almacenamiento seguro
     *
     * @param string $certificado
     * @return string
     */
    protected function cifrarCertificado(string $certificado): string
    {
        // Usar Laravel encryption para cifrar el certificado antes de guardar
        return \Illuminate\Support\Facades\Crypt::encrypt($certificado);
    }

    /**
     * Registrar acción en auditoría
     *
     * @param Correspondencia $correspondencia
     * @param string $accion
     * @param string $detalles
     * @return void
     */
    protected function registrarAuditoria(Correspondencia $correspondencia, string $accion, string $detalles): void
    {
        $correspondencia->auditorias()->create([
            'id_usuario' => auth()->id(),
            'accion' => $accion,
            'detalles' => $detalles,
            'ip_address' => request()->ip(),
        ]);
    }
}
