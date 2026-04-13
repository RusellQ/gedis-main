<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventarioExport implements FromCollection, WithHeadings
{
    protected $inventario;

    public function __construct(Collection $inventario)
    {
        // Eliminar los campos "_id" y "archivos"
        $this->inventario = $inventario->map(function ($item) {
            unset($item['_id'], $item['archivos']); // Eliminar "_id" y "archivos"
            return $item;
        });
    }

    public function collection()
    {
        return $this->inventario;
    }

    public function headings(): array
    {
        return [
            'Inventario', 'Formato', 'Foto Única', 'Medidas', 'Cantidad',
            'Color', 'Descripción Imagen', 'Clasificación Contenido', 'Fondo',
            'Serie', 'Sub Serie', 'Sección', 'Descripción', 'Capturador',
            'Daño', 'Creado', 'Actualizado'
        ];
    }
}
