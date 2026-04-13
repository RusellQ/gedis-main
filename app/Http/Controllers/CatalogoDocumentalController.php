<?php

namespace App\Http\Controllers;
use App\Models\Fondo;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class CatalogoDocumentalController extends Controller
{
   public function catalogoDisposicion()
{
    $fondos = Fondo::with([
        'subfondos.secciones.series.subseries',
        'secciones.series.subseries'
    ])->get();

    return view('pdf.catalogodisposicion', compact('fondos'));
}

public function generarPDFDisposicion()
{
    $fondos = Fondo::with([
        'subfondos.secciones.series.subseries',
        'secciones.series.subseries'
    ])->get();

    $pdf = Pdf::loadView('pdf.catalogodisposicion', compact('fondos'))
              ->setPaper('a4', 'landscape');

    return $pdf->stream('catalogo-disposicion-documental.pdf');
}

}
