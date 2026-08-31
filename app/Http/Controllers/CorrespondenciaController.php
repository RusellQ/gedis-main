<?php

namespace App\Http\Controllers;

use App\Models\Correspondencia;
use App\Models\Expediente;
use App\Services\SATSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CorrespondenciaController extends Controller
{
    protected SATSigningService $satService;

    public function __construct(SATSigningService $satService)
    {
        $this->satService = $satService;
        $this->middleware('auth');
    }

    /**
     * Listar correspondencias de un expediente
     * GET /correspondencia?id_expediente=X
     */
    public function index(Request $request)
    {
        $idExpediente = $request->query('id_expediente');

        if (!$idExpediente) {
            return response()->json(['error' => 'id_expediente requerido'], 400);
        }

        $expediente = Expediente::findOrFail($idExpediente);

        // Validar permisos: usuario debe tener acceso a este expediente
        $this->verificarAccesoExpediente($expediente);

        $correspondencias = Correspondencia::where('id_expediente', $idExpediente)
            ->whereNull('deleted_at')
            ->with(['usuarioAutor', 'firma'])
            ->orderBy('fecha_creacion', 'desc')
            ->get()
            ->map(function ($corr) {
                return [
                    'id' => $corr->id,
                    'numero_consecutivo' => $corr->numero_consecutivo,
                    'asunto' => $corr->asunto,
                    'remitente' => $corr->remitente,
                    'destinatario' => $corr->destinatario,
                    'clasificacion' => $corr->clasificacion,
                    'estado' => $corr->estado,
                    'usuario_autor' => $corr->usuarioAutor ? $corr->usuarioAutor->nombre . ' ' . $corr->usuarioAutor->apellido_paterno : 'Desconocido',
                    'fecha_creacion' => $corr->fecha_creacion->format('Y-m-d H:i:s'),
                    'fecha_firma' => $corr->firma?->timestamp_firma?->format('Y-m-d H:i:s'),
                    'tiene_firma_valida' => $corr->tieneFirmaValida(),
                ];
            });

        return response()->json($correspondencias);
    }

    /**
     * Obtener detalle de una correspondencia
     * GET /correspondencia/{id}
     */
    public function show($id)
    {
        $correspondencia = Correspondencia::with(['expediente', 'usuarioAutor', 'firma'])
            ->findOrFail($id);

        // Validar permisos
        $this->verificarAccesoExpediente($correspondencia->expediente);

        // Registrar acceso (auditoría)
        $this->registrarAcceso($correspondencia);

        return response()->json([
            'id' => $correspondencia->id,
            'id_expediente' => $correspondencia->id_expediente,
            'numero_consecutivo' => $correspondencia->numero_consecutivo,
            'asunto' => $correspondencia->asunto,
            'remitente' => $correspondencia->remitente,
            'destinatario' => $correspondencia->destinatario,
            'contenido' => $correspondencia->contenido,
            'clasificacion' => $correspondencia->clasificacion,
            'estado' => $correspondencia->estado,
            'usuario_autor' => [
                'id' => $correspondencia->usuarioAutor->id,
                'nombre' => $correspondencia->usuarioAutor->nombre . ' ' . $correspondencia->usuarioAutor->apellido_paterno,
            ],
            'fecha_creacion' => $correspondencia->fecha_creacion->toIso8601String(),
            'fecha_modificacion' => $correspondencia->fecha_modificacion->toIso8601String(),
            'firma' => $correspondencia->firma ? [
                'id' => $correspondencia->firma->id,
                'id_usuario_firmante' => $correspondencia->firma->id_usuario_firmante,
                'timestamp_firma' => $correspondencia->firma->timestamp_firma?->toIso8601String(),
                'estado_firma' => $correspondencia->firma->estado_firma,
            ] : null,
        ]);
    }

    /**
     * Crear nueva correspondencia
     * POST /correspondencia
     */
    public function store(Request $request)
    {
        // Validar entrada
        $validado = $request->validate([
            'id_expediente' => 'required|exists:expedientes,id',
            'remitente' => 'nullable|string|max:255',
            'destinatario' => 'nullable|string|max:255',
            'asunto' => 'required|string|min:5',
            'contenido' => 'required|string',
            'clasificacion' => 'required|in:publica,reservada,confidencial',
        ]);

        // Obtener expediente y validar permisos
        $expediente = Expediente::findOrFail($validado['id_expediente']);
        $this->verificarAccesoExpediente($expediente);

        // Validar que usuario tiene permisos de creación
        if (!Auth::user()->hasRole(['root', 'administrador', 'editor'])) {
            return response()->json(['error' => 'No tiene permisos para crear correspondencia'], 403);
        }

        // Obtener próximo número consecutivo
        $proximoConsecutivo = Correspondencia::obtenerProximoConsecutivo($expediente->id);

        // Crear correspondencia
        $correspondencia = Correspondencia::create([
            'id_expediente' => $expediente->id,
            'numero_consecutivo' => $proximoConsecutivo,
            'id_usuario_autor' => Auth::id(),
            'remitente' => $validado['remitente'],
            'destinatario' => $validado['destinatario'],
            'asunto' => $validado['asunto'],
            'contenido' => $validado['contenido'],
            'clasificacion' => $validado['clasificacion'],
            'estado' => Correspondencia::ESTADO_BORRADOR,
        ]);

        // Registrar auditoría
        $this->registrarAcceso($correspondencia, 'crear');

        return response()->json([
            'id' => $correspondencia->id,
            'numero_consecutivo' => $correspondencia->numero_consecutivo,
            'estado' => $correspondencia->estado,
            'mensaje' => 'Correspondencia creada como borrador.',
        ], 201);
    }

    /**
     * Actualizar correspondencia (solo si está en borrador)
     * PUT /correspondencia/{id}
     */
    public function update(Request $request, $id)
    {
        $correspondencia = Correspondencia::findOrFail($id);

        // Validar permisos
        $this->verificarAccesoExpediente($correspondencia->expediente);

        // Validar que no está firmado
        if (!$correspondencia->esEditable()) {
            return response()->json([
                'error' => 'No se puede editar una correspondencia firmada.',
            ], 422);
        }

        // Validar entrada
        $validado = $request->validate([
            'remitente' => 'nullable|string|max:255',
            'destinatario' => 'nullable|string|max:255',
            'asunto' => 'required|string|min:5',
            'contenido' => 'required|string',
            'clasificacion' => 'required|in:publica,reservada,confidencial',
        ]);

        // Actualizar
        $correspondencia->update($validado);

        // Registrar auditoría
        $this->registrarAcceso($correspondencia, 'editar', 'Documento modificado');

        return response()->json([
            'id' => $correspondencia->id,
            'mensaje' => 'Correspondencia actualizada.',
        ]);
    }

    /**
     * Eliminar correspondencia (soft delete, solo en borrador)
     * DELETE /correspondencia/{id}
     */
    public function destroy($id)
    {
        $correspondencia = Correspondencia::findOrFail($id);

        // Validar permisos
        $this->verificarAccesoExpediente($correspondencia->expediente);

        // Validar que no está firmado
        if (!$correspondencia->esEditable()) {
            return response()->json([
                'error' => 'No se puede eliminar una correspondencia firmada.',
            ], 422);
        }

        // Soft delete
        $correspondencia->delete();

        // Registrar auditoría
        $this->registrarAcceso($correspondencia, 'eliminar', 'Documento eliminado');

        return response()->json(['mensaje' => 'Correspondencia eliminada.']);
    }

    /**
     * Firmar correspondencia con SAT
     * POST /correspondencia/{id}/firmar
     */
    public function firmar($id)
    {
        $correspondencia = Correspondencia::findOrFail($id);

        // Validar permisos
        $this->verificarAccesoExpediente($correspondencia->expediente);

        // Validar que el usuario puede firmar
        if (!Auth::user()->hasRole(['root', 'administrador', 'editor'])) {
            return response()->json(['error' => 'No tiene permisos para firmar'], 403);
        }

        // Invocar servicio de firma SAT
        $resultado = $this->satService->firmar($correspondencia);

        if (!$resultado['exito']) {
            return response()->json([
                'error' => $resultado['mensaje'],
                'detalles' => $resultado['error_detail'] ?? null,
            ], 422);
        }

        return response()->json([
            'mensaje' => $resultado['mensaje'],
            'id_firma' => $resultado['id_firma'],
            'timestamp' => $resultado['timestamp'],
        ]);
    }

    /**
     * Descargar correspondencia como PDF
     * GET /correspondencia/{id}/pdf
     */
    public function descargarPDF($id)
    {
        $correspondencia = Correspondencia::with(['expediente', 'usuarioAutor', 'firma'])
            ->findOrFail($id);

        // Validar permisos
        $this->verificarAccesoExpediente($correspondencia->expediente);

        // Registrar acceso (auditoría)
        $this->registrarAcceso($correspondencia, 'descargar');

        // Generar PDF usando view Blade
        $pdf = \PDF::loadView('pdf.correspondencia', [
            'correspondencia' => $correspondencia,
        ]);

        return $pdf->download('correspondencia-' . $correspondencia->id . '.pdf');
    }

    /**
     * Verificar que el usuario tiene acceso a un expediente
     */
    protected function verificarAccesoExpediente(Expediente $expediente): void
    {
        $usuario = Auth::user();

        // root o usuario sin autenticación estricta tiene acceso global
        if (!$usuario || $usuario->hasRole('root')) {
            return;
        }

        // Verificar coincidencia de dependencia si ambas están definidas
        if ($expediente->id_dependencia && $usuario->id_dependencia && $expediente->id_dependencia != $usuario->id_dependencia) {
            abort(403, 'No tiene acceso a este expediente.');
        }
    }

    /**
     * Registrar acceso en auditoría
     */
    protected function registrarAcceso(Correspondencia $correspondencia, string $accion = 'acceder', string $detalles = ''): void
    {
        $correspondencia->auditorias()->create([
            'id_usuario' => Auth::id(),
            'accion' => $accion,
            'detalles' => $detalles ?: 'Acceso al documento',
            'ip_address' => request()->ip(),
        ]);
    }
}
