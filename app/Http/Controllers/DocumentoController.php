<?php

namespace App\Http\Controllers;

use App\Models\Caratula;
use App\Models\Expediente;
use App\Services\InstrumentoControlService;
use App\Services\ReglasDocumentalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use MongoDB\Client;

class DocumentoController extends Controller
{
    public function getDocumentById($documentId)
    {
        $document = Caratula::with('instrumentoControl.serie')->find($documentId);

        if ($document) {
            return response()->json($document);
        }

        return response()->json(['error' => 'Documento no encontrado'], 404);
    }

    public function getAllDocuments()
    {
        $user = Auth::user();

        $documents = Caratula::with('instrumentoControl.serie')
            ->where('id_dependencia', $user->id_dependencia)
            ->where('id_departamento', $user->id_departamento)
            ->get();

        return response()->json($documents);
    }

    public function storeDocument(Request $request)
    {
        $validatedData = $request->validate([
            'id_dependencia',
            'id_departamento',
            'nombre_sujeto_obligado' => 'required|string',
            'area_administrativa' => 'required|string',
            'no_consecutivo' => 'required|numeric',
            'area_generadora' => 'nullable|string',
            'codigo_clasificacion_archivistica' => 'nullable|string',
            'anio_apertura' => 'nullable|numeric',
            'anio_cierre' => 'nullable|numeric',
            'soporte_documental' => 'nullable|string',
            'titulo_expediente' => 'required|string',
            'descripcion_asunto_expediente' => 'nullable|string',
            'ubicacion_topografica' => 'nullable|string',
            'no_legajo' => 'nullable|numeric',
            'no_fojas' => 'nullable|numeric',
            'valor_documental' => 'nullable|array',
            'valor_documental.administrativo' => 'nullable|boolean',
            'valor_documental.legal' => 'nullable|boolean',
            'valor_documental.contable' => 'nullable|boolean',
            'vigencia_documental' => 'nullable|string',
            'anios_archivo_tramite' => 'nullable|numeric',
            'anios_archivo_concentracion' => 'nullable|numeric',
            'caracter_informacion' => 'nullable|string',
            'fecha_clasificacion' => 'nullable|date',
            'fundamento_legal' => 'nullable|string',
            'periodo_reserva' => 'nullable|string',
            'aplicacion_periodo_reserva' => 'nullable|string',
            'rubrica_titular' => 'nullable|string',
            'fecha_desclasificacion' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'capturador' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $valorDocumental = $validatedData['valor_documental'] ?? [];

            $validatedData['valor_documental_administrativo'] = !empty($valorDocumental['administrativo']) ? 1 : 0;
            $validatedData['valor_documental_legal'] = !empty($valorDocumental['legal']) ? 1 : 0;
            $validatedData['valor_documental_contable'] = !empty($valorDocumental['contable']) ? 1 : 0;
            unset($validatedData['valor_documental'], $validatedData['caracter_informacion']);

            $validatedData['id_dependencia'] = $user->id_dependencia;
            $validatedData['id_departamento'] = $user->id_departamento;

            $instrumento = InstrumentoControlService::resolverPorExpedienteClave(
                $validatedData['codigo_clasificacion_archivistica'] ?? null
            );
            $validatedData['id_instrumento_control'] = $instrumento?->id;

            $camposCaratula = collect($validatedData)->only((new Caratula())->getFillable())->toArray();
            $caratula = Caratula::create($camposCaratula);

            ReglasDocumentalesService::aplicarReglasCaratula($caratula, $validatedData);
            $caratula->load('instrumentoControl.serie');

            return response()->json(['message' => 'Documento creado con éxito', 'data' => $caratula], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el documento: ' . $e->getMessage()], 500);
        }
    }

    public function deleteDocument($documentId)
    {
        $document = Caratula::find($documentId);

        if (!$document) {
            return response()->json(['error' => 'Documento no encontrado'], 404);
        }

        $document->delete();

        return response()->json(['message' => 'Documento eliminado con éxito'], 200);
    }

    public function updateDocument(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre_sujeto_obligado' => 'required|string',
            'area_administrativa' => 'required|string',
            'no_consecutivo' => 'required|numeric',
            'area_generadora' => 'nullable|string',
            'codigo_clasificacion_archivistica' => 'nullable|string',
            'anio_apertura' => 'nullable|numeric',
            'anio_cierre' => 'nullable|numeric',
            'soporte_documental' => 'nullable|string',
            'titulo_expediente' => 'required|string',
            'descripcion_asunto_expediente' => 'nullable|string',
            'ubicacion_topografica' => 'nullable|string',
            'no_legajo' => 'nullable|numeric',
            'no_fojas' => 'nullable|numeric',
            'valor_documental' => 'nullable|array',
            'valor_documental.administrativo' => 'nullable|boolean',
            'valor_documental.legal' => 'nullable|boolean',
            'valor_documental.contable' => 'nullable|boolean',
            'vigencia_documental' => 'nullable|string',
            'anios_archivo_tramite' => 'nullable|numeric',
            'anios_archivo_concentracion' => 'nullable|numeric',
            'caracter_informacion' => 'nullable|string',
            'fecha_clasificacion' => 'nullable|date',
            'fundamento_legal' => 'nullable|string',
            'periodo_reserva' => 'nullable|string',
            'aplicacion_periodo_reserva' => 'nullable|string',
            'rubrica_titular' => 'nullable|string',
            'fecha_desclasificacion' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'capturador' => 'nullable|string',
        ]);

        try {
            $document = Caratula::find($id);

            if (!$document) {
                return response()->json(['error' => 'Documento no encontrado'], 404);
            }

            if (isset($validatedData['valor_documental'])) {
                $validatedData['valor_documental_administrativo'] = $validatedData['valor_documental']['administrativo'] ?? 0;
                $validatedData['valor_documental_legal'] = $validatedData['valor_documental']['legal'] ?? 0;
                $validatedData['valor_documental_contable'] = $validatedData['valor_documental']['contable'] ?? 0;
                unset($validatedData['valor_documental']);
            }

            unset($validatedData['caracter_informacion']);

            if (!empty($validatedData['codigo_clasificacion_archivistica'])) {
                $instrumento = InstrumentoControlService::resolverPorExpedienteClave(
                    $validatedData['codigo_clasificacion_archivistica']
                );
                if ($instrumento) {
                    $validatedData['id_instrumento_control'] = $instrumento->id;
                }
            }

            $camposCaratula = collect($validatedData)->only($document->getFillable())->toArray();
            $document->fill($camposCaratula);
            $document->save();

            ReglasDocumentalesService::aplicarReglasCaratula($document, $validatedData);
            $document->load('instrumentoControl.serie');

            return response()->json(['message' => 'Documento actualizado con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el documento: ' . $e->getMessage()], 500);
        }
    }

    public function buscar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'palabra_clave' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $palabraClave = $validator->validated()['palabra_clave'];
        $nombreBaseDatos = 'anzimat';
        $client = new Client();
        $colecciones = $client->$nombreBaseDatos->listCollections();
        $resultados = [];
        $coleccionesOmitir = ['carrousel_images', 'migrations', 'personal_access_tokens', 'users', 'desechados_collection'];
        $camposOmitir = [''];

        foreach ($colecciones as $coleccion) {
            $nombreColeccion = $coleccion->getName();

            if (in_array($nombreColeccion, $coleccionesOmitir)) {
                continue;
            }

            $resultadosColeccion = $client->$nombreBaseDatos->$nombreColeccion
                ->find([
                    '$or' => $this->construirExpresionesDeBusqueda($palabraClave, $nombreColeccion, $camposOmitir)
                ])
                ->toArray();

            foreach ($resultadosColeccion as &$documento) {
                $documento['tipo_coleccion'] = $nombreColeccion;
            }

            if ($resultadosColeccion) {
                $resultados = array_merge($resultados, $resultadosColeccion);
            }
        }

        return response()->json($resultados);
    }

    private function construirExpresionesDeBusqueda($palabraClave, $nombreColeccion, $camposOmitir)
    {
        $expresiones = [];
        $client = new Client();
        $colecciones = $client->selectDatabase('anzimat')->selectCollection($nombreColeccion);
        $documentos = $colecciones->find();

        foreach ($documentos as $documento) {
            foreach ($documento as $campo => $valor) {
                if (in_array($campo, $camposOmitir)) {
                    continue;
                }

                $expresiones[] = [$campo => ['$regex' => $palabraClave]];
            }
        }

        if (empty($expresiones)) {
            $expresiones = [['_id' => ['$exists' => true]]];
        }

        return $expresiones;
    }
}
