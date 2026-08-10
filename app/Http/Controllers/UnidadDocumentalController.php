<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\UnidadDocumental;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UnidadDocumentalController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_expediente' => 'required|exists:expedientes,id',
            'tipo' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'archivo' => 'nullable|file|mimes:pdf|mimetypes:application/pdf|max:20480',
        ], [
            'id_expediente.required' => 'El expediente es obligatorio.',
            'id_expediente.exists' => 'El expediente no existe.',
            'tipo.required' => 'El tipo es obligatorio.',
            'nombre.required' => 'El nombre es obligatorio.',
            'archivo.mimes' => 'El archivo debe ser un PDF.',
            'archivo.mimetypes' => 'El archivo debe ser un PDF.',
            'archivo.max' => 'El archivo no debe superar 20 MB.',
        ]);

        $expediente = Expediente::findOrFail($validated['id_expediente']);

        if ($expediente->estado !== 'en_tramite') {
            throw ValidationException::withMessages([
                'id_expediente' => 'Solo se pueden añadir unidades documentales a expedientes en trámite.',
            ]);
        }

        $unidad = UnidadDocumental::create([
            'id_expediente' => $expediente->id,
            'tipo' => $validated['tipo'],
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'archivo' => $request->hasFile('archivo')
                ? $request->file('archivo')->store('documentos', 'public')
                : null,
        ]);

        return response()->json($unidad, 201);
    }
}
