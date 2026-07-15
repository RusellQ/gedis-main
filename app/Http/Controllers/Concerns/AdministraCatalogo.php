<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait AdministraCatalogo
{
    protected function denyUnlessRoot(): ?JsonResponse
    {
        $user = auth()->user();

        if (!$user || !$user->hasRole('root')) {
            return response()->json([
                'message' => 'Acceso restringido a usuarios con rol root.',
            ], 403);
        }

        return null;
    }

    /**
     * @param  object|null  $parent  Entidad padre con id_dependencia / id_departamento
     * @return array{id_dependencia: mixed, id_departamento: mixed}|JsonResponse
     */
    protected function resolveJerarquiaIds(Request $request, $parent = null)
    {
        if ($parent) {
            return [
                'id_dependencia' => $parent->id_dependencia,
                'id_departamento' => $parent->id_departamento,
            ];
        }

        $request->validate([
            'id_dependencia' => 'required|exists:dependencias,id',
            'id_departamento' => 'required|exists:departamentos,id',
        ]);

        return [
            'id_dependencia' => (int) $request->input('id_dependencia'),
            'id_departamento' => (int) $request->input('id_departamento'),
        ];
    }

    protected function conflictResponse(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 422);
    }
}
