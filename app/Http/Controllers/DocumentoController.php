<?php

namespace App\Http\Controllers;

use App\Models\Caratula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use MongoDB\Client;
use Illuminate\Support\Facades\Auth;


class DocumentoController extends Controller
{
    public function getDocumentById($documentId)
    {
        // Busca la carátula por ID en MySQL
        $document = Caratula::find($documentId);

        if ($document) {
            return response()->json($document);
        } else {
            return response()->json(['error' => 'Documento no encontrado'], 404);
        }
    }

    public function getAllDocuments()
    {
        $user = Auth::user();

        $documents = Caratula::where('id_dependencia', $user->id_dependencia)
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
            'informacion_reservada' => 'nullable|string',
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

            // Extraer y mapear valores de valor_documental
            $valorDocumental = $validatedData['valor_documental'] ?? [];

            $validatedData['valor_documental_administrativo'] = !empty($valorDocumental['administrativo']) ? 1 : 0;
            $validatedData['valor_documental_legal'] = !empty($valorDocumental['legal']) ? 1 : 0;
            $validatedData['valor_documental_contable'] = !empty($valorDocumental['contable']) ? 1 : 0;

            unset($validatedData['valor_documental']); // eliminar el array original

            // Asignar automáticamente la dependencia y el departamento del usuario
            $validatedData['id_dependencia'] = $user->id_dependencia;
            $validatedData['id_departamento'] = $user->id_departamento;

            // Crear la carátula
            $caratula = Caratula::create($validatedData);

            return response()->json(['message' => 'Documento creado con éxito', 'data' => $caratula], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear el documento: ' . $e->getMessage()], 500);
        }
    }


    public function deleteDocument($documentId)
    {
        // Buscar el documento en MySQL por su ID
        $document = Caratula::find($documentId);

        // Verificar si el documento existe
        if (!$document) {
            return response()->json(['error' => 'Documento no encontrado'], 404);
        }

        // Eliminar el documento
        $document->delete();

        // Devolver respuesta exitosa
        return response()->json(['message' => 'Documento eliminado con éxito'], 200);
    }




    public function updateDocument(Request $request, $id)
    {
        // Validación de los datos
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
            'informacion_reservada' => 'nullable|string',
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
            // Buscar el documento por ID
            $document = Caratula::find($id);

            if (!$document) {
                return response()->json(['error' => 'Documento no encontrado'], 404);
            }

            // Si existe 'valor_documental', mapear sus subcampos a campos individuales
            if (isset($validatedData['valor_documental'])) {
                $document->valor_documental_administrativo = $validatedData['valor_documental']['administrativo'] ?? 0;
                $document->valor_documental_legal = $validatedData['valor_documental']['legal'] ?? 0;
                $document->valor_documental_contable = $validatedData['valor_documental']['contable'] ?? 0;
                unset($validatedData['valor_documental']);
            }

            // Actualizar los demás campos
            $document->fill($validatedData);

            // Guardar los cambios
            $document->save();

            return response()->json(['message' => 'Documento actualizado con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar el documento: ' . $e->getMessage()], 500);
        }
    }


    public function buscar(Request $request)
    {
        // Validar la entrada
        $validator = Validator::make($request->all(), [
            'palabra_clave' => 'required|string',
        ]);

        // Verificar si la validación falla
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Obtener la palabra clave validada
        $palabraClave = $validator->validated()['palabra_clave'];
        $nombreBaseDatos = 'anzimat';

        // Conectar a MongoDB
        $client = new Client();

        // Obtener todas las colecciones en la base de datos
        $colecciones = $client->$nombreBaseDatos->listCollections();

        $resultados = [];

        $coleccionesOmitir = ['carrousel_images', 'migrations', 'personal_access_tokens', 'users', 'desechados_collection'];

        $camposOmitir = [''];

        // Iterar sobre cada colección y realizar la búsqueda
        foreach ($colecciones as $coleccion) {
            $nombreColeccion = $coleccion->getName();

            // Verificar si la colección debe ser omitida
            if (in_array($nombreColeccion, $coleccionesOmitir)) {
                continue; // Saltar a la siguiente iteración
            }

            // Realizar la búsqueda en la colección actual en todos los campos
            $resultadosColeccion = $client->$nombreBaseDatos->$nombreColeccion
                ->find([
                    '$or' => $this->construirExpresionesDeBusqueda($palabraClave, $nombreColeccion, $camposOmitir)
                ])
                ->toArray();

            // Agregar el nombre de la colección como un campo adicional a cada documento
            foreach ($resultadosColeccion as &$documento) {
                $documento['tipo_coleccion'] = $nombreColeccion;
            }

            // Verificar si hay resultados antes de agregar al array
            if ($resultadosColeccion) {
                $resultados = array_merge($resultados, $resultadosColeccion);
            }
        }

        return response()->json($resultados);
    }

    private function construirExpresionesDeBusqueda($palabraClave, $nombreColeccion, $camposOmitir)
    {
        $expresiones = [];

        // Obtener todos los documentos en la colección
        $client = new Client();
        $colecciones = $client->selectDatabase('anzimat')->selectCollection($nombreColeccion);
        $documentos = $colecciones->find();

        foreach ($documentos as $documento) {
            foreach ($documento as $campo => $valor) {
                // Verificar si el campo debe ser omitido
                if (in_array($campo, $camposOmitir)) {
                    continue; // Saltar a la siguiente iteración
                }

                $expresiones[] = [$campo => ['$regex' => $palabraClave]];
            }
        }

        // Verificar si hay expresiones antes de devolver el resultado
        if (empty($expresiones)) {
            // Devolver una expresión predeterminada (puedes ajustar según tus necesidades)
            $expresiones = [['_id' => ['$exists' => true]]];
        }

        return $expresiones;
    }
}
