<?php

namespace App\Http\Controllers;

use App\Models\Fondo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CuadroGeneralController extends Controller
{
    public function generarPDF(Request $request)
    {
        $fondos = Fondo::with([
            'subfondos.secciones.series.subseries',
            'secciones.series.subseries',
        ])->get();

        $pdf = Pdf::loadView('pdf.cuadrogeneral', compact('fondos'))->setPaper('a4', 'landscape');

        return $pdf->stream('cuadro-general-clasificacion.pdf');
    }

 public function vistaPrevia()
{
    $fondos = Fondo::with([
        'subfondos.secciones.series.subseries',
        'secciones.series.subseries'
    ])->get();

    return view('pdf.cuadrogeneral', compact('fondos'));
}

}

