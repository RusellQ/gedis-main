<?php

namespace App\Http\Controllers;

use App\Models\Correspondencia;
use App\Models\Expediente;
use App\Models\UnidadDocumental;
use App\Models\User;
use App\Services\OficioNumeroService;
use App\Services\SATSigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CorrespondenciaController extends Controller
{
    public function __construct(
        protected SATSigningService $satService,
        protected OficioNumeroService $oficioNumeroService
    ) {
        $this->middleware('auth');
    }

    /**
     * Bandeja del emisor (oficios creados en su dependencia).
     * GET /api/correspondencia/bandeja
     */
    public function bandeja(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario->hasRole(['root', 'administrador', 'editor', 'lector'])) {
            return response()->json(['error' => 'No tiene permisos'], 403);
        }

        $query = Correspondencia::query()
            ->with(['usuarioAutor', 'usuarioDestinatario', 'firma'])
            ->orderByDesc('fecha_creacion');

        if (!$usuario->hasRole('root')) {
            $autorIds = User::where('id_dependencia', $usuario->id_dependencia)->pluck('id');
            $query->whereIn('id_usuario_autor', $autorIds);
        }

        return response()->json($query->get()->map(fn ($c) => $this->mapListado($c)));
    }

    /**
     * Oficios remitidos al usuario autenticado.
     * GET /api/correspondencia/recepcion
     */
    public function recepcion()
    {
        $usuario = Auth::user();

        $items = Correspondencia::query()
            ->with(['usuarioAutor', 'firma', 'expediente'])
            ->where('id_usuario_destinatario', $usuario->id)
            ->whereIn('estado', [
                Correspondencia::ESTADO_REMITIDO,
                Correspondencia::ESTADO_ARCHIVADO,
                Correspondencia::ESTADO_FIRMADO,
            ])
            ->orderByDesc('remitido_at')
            ->get()
            ->map(fn ($c) => $this->mapListado($c));

        return response()->json($items);
    }

    /**
     * Usuarios de la misma dependencia para remisión.
     * GET /api/correspondencia/usuarios-remision
     */
    public function usuariosRemision()
    {
        $usuario = Auth::user();
        $query = User::query()->orderBy('nombre');

        if (!$usuario->hasRole('root')) {
            $query->where('id_dependencia', $usuario->id_dependencia);
        }

        $users = $query->get()->map(fn (User $u) => [
            'id' => $u->id,
            'nombre' => trim($u->nombre . ' ' . $u->apellido_paterno . ' ' . ($u->apellido_materno ?? '')),
            'email' => $u->email,
            'id_departamento' => $u->id_departamento,
        ])->filter(fn ($u) => $u['id'] !== $usuario->id)->values();

        return response()->json($users);
    }

    public function show($id)
    {
        $correspondencia = Correspondencia::with([
            'expediente',
            'usuarioAutor',
            'usuarioDestinatario',
            'firma',
        ])->findOrFail($id);

        $this->verificarAccesoOficio($correspondencia);
        $this->registrarAcceso($correspondencia, 'acceder');

        return response()->json($this->mapDetalle($correspondencia));
    }

    /**
     * Crear borrador de oficio (sin expediente).
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole(['root', 'administrador', 'editor'])) {
            return response()->json(['error' => 'No tiene permisos para crear oficios'], 403);
        }

        $validado = $request->validate([
            'asunto' => 'required|string|min:5',
            'contenido' => 'required|string',
            'destinatario' => 'nullable|string|max:2000',
            'remitente' => 'nullable|string|max:255',
            'leyenda_anio' => 'nullable|string|max:500',
            'clasificacion' => 'nullable|in:publica,reservada,confidencial',
            'emitir' => 'nullable|boolean',
        ]);

        $usuario = Auth::user();
        $remitente = $validado['remitente']
            ?? trim($usuario->nombre . ' ' . $usuario->apellido_paterno);

        try {
            $correspondencia = Correspondencia::create([
                'id_expediente' => null,
                'numero_consecutivo' => Correspondencia::obtenerProximoConsecutivo(null),
                'id_usuario_autor' => $usuario->id,
                'remitente' => $remitente,
                'destinatario' => $validado['destinatario'] ?? null,
                'asunto' => $validado['asunto'],
                'leyenda_anio' => $validado['leyenda_anio']
                    ?? config('services.oficio.leyenda_anio_default')
                    ?: ('Año ' . now()->year),
                'fecha_oficio' => now()->toDateString(),
                'contenido' => $validado['contenido'],
                'clasificacion' => $validado['clasificacion'] ?? Correspondencia::CLASIFICACION_PUBLICA,
                'estado' => Correspondencia::ESTADO_BORRADOR,
            ]);

            if (!empty($validado['emitir'])) {
                $this->asignarNumeroYEmitir($correspondencia, $usuario);
            }

            $this->registrarAcceso($correspondencia, 'crear');

            return response()->json([
                'id' => $correspondencia->id,
                'estado' => $correspondencia->estado,
                'numero_oficio' => $correspondencia->numero_oficio,
                'mensaje' => $correspondencia->estado === Correspondencia::ESTADO_EMITIDO
                    ? 'Oficio emitido.'
                    : 'Oficio guardado como borrador.',
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error al crear oficio: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Error al guardar el oficio.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        $this->verificarAccesoOficio($correspondencia);

        if (!$correspondencia->esEditable()) {
            return response()->json(['error' => 'Solo se pueden editar oficios en borrador.'], 422);
        }

        if (!Auth::user()->hasRole(['root', 'administrador', 'editor'])) {
            return response()->json(['error' => 'No tiene permisos'], 403);
        }

        $validado = $request->validate([
            'asunto' => 'required|string|min:5',
            'contenido' => 'required|string',
            'destinatario' => 'nullable|string|max:2000',
            'remitente' => 'nullable|string|max:255',
            'leyenda_anio' => 'nullable|string|max:500',
            'clasificacion' => 'nullable|in:publica,reservada,confidencial',
            'emitir' => 'nullable|boolean',
        ]);

        $correspondencia->update([
            'asunto' => $validado['asunto'],
            'contenido' => $validado['contenido'],
            'destinatario' => $validado['destinatario'] ?? $correspondencia->destinatario,
            'remitente' => $validado['remitente'] ?? $correspondencia->remitente,
            'leyenda_anio' => $validado['leyenda_anio'] ?? $correspondencia->leyenda_anio,
            'clasificacion' => $validado['clasificacion'] ?? $correspondencia->clasificacion,
        ]);

        if (!empty($validado['emitir'])) {
            $this->asignarNumeroYEmitir($correspondencia, Auth::user());
        }

        $this->registrarAcceso($correspondencia, 'editar', 'Oficio modificado');

        return response()->json([
            'id' => $correspondencia->id,
            'estado' => $correspondencia->estado,
            'numero_oficio' => $correspondencia->numero_oficio,
            'mensaje' => 'Oficio actualizado.',
        ]);
    }

    /**
     * Emitir: asigna número de oficio y pasa a emitido.
     * POST /api/correspondencia/{id}/emitir
     */
    public function emitir($id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        $this->verificarAccesoOficio($correspondencia);

        if (!Auth::user()->hasRole(['root', 'administrador', 'editor'])) {
            return response()->json(['error' => 'No tiene permisos'], 403);
        }

        if ($correspondencia->estado !== Correspondencia::ESTADO_BORRADOR) {
            return response()->json(['error' => 'Solo se pueden emitir borradores.'], 422);
        }

        $this->asignarNumeroYEmitir($correspondencia, Auth::user());
        $this->registrarAcceso($correspondencia, 'emitir', 'Oficio emitido');

        return response()->json([
            'mensaje' => 'Oficio emitido.',
            'numero_oficio' => $correspondencia->numero_oficio,
            'estado' => $correspondencia->estado,
        ]);
    }

    /**
     * Remitir a usuario interno.
     * POST /api/correspondencia/{id}/remitir
     */
    public function remitir(Request $request, $id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        $this->verificarAccesoOficio($correspondencia);

        if (!Auth::user()->hasRole(['root', 'administrador', 'editor'])) {
            return response()->json(['error' => 'No tiene permisos'], 403);
        }

        if ($correspondencia->estado === Correspondencia::ESTADO_BORRADOR) {
            $this->asignarNumeroYEmitir($correspondencia, Auth::user());
        }

        if (!in_array($correspondencia->estado, [
            Correspondencia::ESTADO_EMITIDO,
            Correspondencia::ESTADO_FIRMADO,
        ], true)) {
            return response()->json(['error' => 'El oficio no puede remitirse en su estado actual.'], 422);
        }

        $validado = $request->validate([
            'id_usuario_destinatario' => 'required|exists:users,id',
        ]);

        $destinatario = User::findOrFail($validado['id_usuario_destinatario']);
        $emisor = Auth::user();
        if (!$emisor->hasRole('root')
            && $destinatario->id_dependencia
            && $emisor->id_dependencia
            && (int) $destinatario->id_dependencia !== (int) $emisor->id_dependencia) {
            return response()->json(['error' => 'El destinatario debe ser de su misma dependencia.'], 422);
        }

        $correspondencia->id_usuario_destinatario = $destinatario->id;
        $correspondencia->estado = Correspondencia::ESTADO_REMITIDO;
        $correspondencia->remitido_at = now();
        $correspondencia->save();

        $this->registrarAcceso($correspondencia, 'remitir', 'Remitido a usuario ' . $destinatario->id);

        return response()->json([
            'mensaje' => 'Oficio remitido.',
            'estado' => $correspondencia->estado,
        ]);
    }

    /**
     * Archivar: asocia expediente y crea unidad documental PDF.
     * POST /api/correspondencia/{id}/archivar
     */
    public function archivar(Request $request, $id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        $this->verificarAccesoOficio($correspondencia);

        $usuario = Auth::user();
        $esDestinatario = (int) $correspondencia->id_usuario_destinatario === (int) $usuario->id;
        if (!$esDestinatario && !$usuario->hasRole(['root', 'administrador'])) {
            return response()->json(['error' => 'Solo el destinatario puede archivar este oficio.'], 403);
        }

        if ($correspondencia->estado !== Correspondencia::ESTADO_REMITIDO
            && !($usuario->hasRole('root') && $correspondencia->estado === Correspondencia::ESTADO_FIRMADO
                && $correspondencia->id_usuario_destinatario)) {
            if ($correspondencia->estado !== Correspondencia::ESTADO_REMITIDO) {
                return response()->json(['error' => 'El oficio debe estar remitido para archivarse.'], 422);
            }
        }

        $validado = $request->validate([
            'id_expediente' => 'required|exists:expedientes,id',
            'tipo' => 'nullable|string|max:255',
            'nombre' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $expediente = Expediente::findOrFail($validado['id_expediente']);
        $this->verificarAccesoExpediente($expediente);

        if ($expediente->estado !== 'en_tramite') {
            return response()->json([
                'error' => 'Solo se pueden añadir unidades documentales a expedientes en trámite.',
            ], 422);
        }

        try {
            $pdf = \PDF::loadView('pdf.oficio', [
                'correspondencia' => $correspondencia->load(['usuarioAutor', 'firma']),
            ]);
            $nombreArchivo = 'oficios/' . Str::slug($correspondencia->numero_oficio ?: ('oficio-' . $correspondencia->id))
                . '-' . $correspondencia->id . '.pdf';
            Storage::disk('public')->put($nombreArchivo, $pdf->output());

            $unidad = UnidadDocumental::create([
                'id_expediente' => $expediente->id,
                'tipo' => $validado['tipo'] ?? 'Oficio',
                'nombre' => $validado['nombre']
                    ?? ($correspondencia->numero_oficio ?: ('Oficio ' . $correspondencia->id)),
                'descripcion' => $validado['descripcion'] ?? $correspondencia->asunto,
                'archivo' => $nombreArchivo,
            ]);

            $correspondencia->id_expediente = $expediente->id;
            $correspondencia->id_unidad_documental = $unidad->id;
            $correspondencia->estado = Correspondencia::ESTADO_ARCHIVADO;
            $correspondencia->archivado_at = now();
            $correspondencia->save();

            $this->registrarAcceso($correspondencia, 'archivar', 'Archivado en expediente ' . $expediente->id);

            return response()->json([
                'mensaje' => 'Oficio archivado como unidad documental.',
                'id_unidad_documental' => $unidad->id,
                'id_expediente' => $expediente->id,
                'estado' => $correspondencia->estado,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al archivar oficio: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'No se pudo archivar el oficio.'], 500);
        }
    }

    public function destroy($id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        $this->verificarAccesoOficio($correspondencia);

        if (!$correspondencia->esEditable()) {
            return response()->json(['error' => 'Solo se pueden eliminar borradores.'], 422);
        }

        $correspondencia->delete();
        $this->registrarAcceso($correspondencia, 'eliminar', 'Oficio eliminado');

        return response()->json(['mensaje' => 'Oficio eliminado.']);
    }

    /**
     * Firma SAT sandbox (opcional).
     */
    public function firmar($id)
    {
        $correspondencia = Correspondencia::findOrFail($id);
        $this->verificarAccesoOficio($correspondencia);

        if (!Auth::user()->hasRole(['root', 'administrador', 'editor'])) {
            return response()->json(['error' => 'No tiene permisos para firmar'], 403);
        }

        if (in_array($correspondencia->estado, [
            Correspondencia::ESTADO_ARCHIVADO,
            Correspondencia::ESTADO_CANCELADO,
        ], true)) {
            return response()->json(['error' => 'No se puede firmar un oficio archivado o cancelado.'], 422);
        }

        if ($correspondencia->estado === Correspondencia::ESTADO_BORRADOR) {
            $this->asignarNumeroYEmitir($correspondencia, Auth::user());
            $correspondencia->refresh();
        }

        $resultado = $this->satService->firmar($correspondencia);

        if (!$resultado['exito']) {
            return response()->json([
                'error' => $resultado['mensaje'],
                'detalles' => $resultado['error_detail'] ?? null,
            ], 422);
        }

        return response()->json([
            'mensaje' => $resultado['mensaje'] . ' (modo prueba / sandbox)',
            'id_firma' => $resultado['id_firma'],
            'timestamp' => $resultado['timestamp'],
            'estado' => $correspondencia->fresh()->estado,
        ]);
    }

    public function descargarPDF($id)
    {
        $correspondencia = Correspondencia::with(['expediente', 'usuarioAutor', 'firma'])
            ->findOrFail($id);

        $this->verificarAccesoOficio($correspondencia);
        $this->registrarAcceso($correspondencia, 'descargar');

        $pdf = \PDF::loadView('pdf.oficio', [
            'correspondencia' => $correspondencia,
        ]);

        $nombre = 'oficio-' . ($correspondencia->numero_oficio
            ? Str::slug($correspondencia->numero_oficio)
            : $correspondencia->id) . '.pdf';

        return $pdf->download($nombre);
    }

    protected function asignarNumeroYEmitir(Correspondencia $correspondencia, User $emisor): void
    {
        if (!$correspondencia->numero_oficio) {
            $correspondencia->numero_oficio = $this->oficioNumeroService->generar($emisor);
        }
        if (!$correspondencia->fecha_oficio) {
            $correspondencia->fecha_oficio = now()->toDateString();
        }
        $correspondencia->estado = Correspondencia::ESTADO_EMITIDO;
        $correspondencia->save();
    }

    protected function verificarAccesoOficio(Correspondencia $correspondencia): void
    {
        $usuario = Auth::user();
        if (!$usuario || $usuario->hasRole('root')) {
            return;
        }

        if ((int) $correspondencia->id_usuario_autor === (int) $usuario->id
            || (int) $correspondencia->id_usuario_destinatario === (int) $usuario->id) {
            return;
        }

        $autor = $correspondencia->usuarioAutor;
        if ($autor && $autor->id_dependencia && $usuario->id_dependencia
            && (int) $autor->id_dependencia === (int) $usuario->id_dependencia
            && $usuario->hasRole(['administrador', 'editor', 'lector'])) {
            return;
        }

        abort(403, 'No tiene acceso a este oficio.');
    }

    protected function verificarAccesoExpediente(Expediente $expediente): void
    {
        $usuario = Auth::user();
        if (!$usuario || $usuario->hasRole('root')) {
            return;
        }
        if ($expediente->id_dependencia && $usuario->id_dependencia
            && (int) $expediente->id_dependencia !== (int) $usuario->id_dependencia) {
            abort(403, 'No tiene acceso a este expediente.');
        }
    }

    protected function registrarAcceso(
        Correspondencia $correspondencia,
        string $accion = 'acceder',
        string $detalles = ''
    ): void {
        try {
            $correspondencia->auditorias()->create([
                'id_usuario' => Auth::id(),
                'accion' => $accion,
                'detalles' => $detalles ?: 'Acceso al documento',
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar auditoría de correspondencia: ' . $e->getMessage());
        }
    }

    protected function mapListado(Correspondencia $corr): array
    {
        return [
            'id' => $corr->id,
            'numero_oficio' => $corr->numero_oficio,
            'asunto' => $corr->asunto,
            'destinatario' => $corr->destinatario,
            'estado' => $corr->estado,
            'clasificacion' => $corr->clasificacion,
            'fecha_oficio' => $corr->fecha_oficio?->format('Y-m-d'),
            'fecha_creacion' => $corr->fecha_creacion?->format('Y-m-d H:i:s'),
            'remitido_at' => $corr->remitido_at?->format('Y-m-d H:i:s'),
            'usuario_autor' => $corr->usuarioAutor
                ? trim($corr->usuarioAutor->nombre . ' ' . $corr->usuarioAutor->apellido_paterno)
                : 'Desconocido',
            'usuario_destinatario' => $corr->usuarioDestinatario
                ? trim($corr->usuarioDestinatario->nombre . ' ' . $corr->usuarioDestinatario->apellido_paterno)
                : null,
            'id_expediente' => $corr->id_expediente,
            'tiene_firma_valida' => $corr->tieneFirmaValida(),
        ];
    }

    protected function mapDetalle(Correspondencia $c): array
    {
        return [
            'id' => $c->id,
            'id_expediente' => $c->id_expediente,
            'id_unidad_documental' => $c->id_unidad_documental,
            'numero_consecutivo' => $c->numero_consecutivo,
            'numero_oficio' => $c->numero_oficio,
            'asunto' => $c->asunto,
            'leyenda_anio' => $c->leyenda_anio,
            'fecha_oficio' => $c->fecha_oficio?->format('Y-m-d'),
            'remitente' => $c->remitente,
            'destinatario' => $c->destinatario,
            'contenido' => $c->contenido,
            'clasificacion' => $c->clasificacion,
            'estado' => $c->estado,
            'id_usuario_destinatario' => $c->id_usuario_destinatario,
            'usuario_autor' => $c->usuarioAutor ? [
                'id' => $c->usuarioAutor->id,
                'nombre' => trim($c->usuarioAutor->nombre . ' ' . $c->usuarioAutor->apellido_paterno),
            ] : null,
            'usuario_destinatario' => $c->usuarioDestinatario ? [
                'id' => $c->usuarioDestinatario->id,
                'nombre' => trim($c->usuarioDestinatario->nombre . ' ' . $c->usuarioDestinatario->apellido_paterno),
            ] : null,
            'firma' => $c->firma ? [
                'id' => $c->firma->id,
                'timestamp_firma' => $c->firma->timestamp_firma?->toIso8601String(),
                'estado_firma' => $c->firma->estado_firma,
            ] : null,
            'lugar' => config('services.oficio.lugar'),
            'firma_nombre' => config('services.oficio.firma_nombre'),
            'firma_cargo' => config('services.oficio.firma_cargo'),
        ];
    }
}
