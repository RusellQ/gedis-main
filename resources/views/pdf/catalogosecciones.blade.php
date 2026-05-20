<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Secciones, Series y Subseries</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .page { padding: 18px; }
        .header { text-align: center; margin-bottom: 18px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header h2 { margin: 6px 0 0; font-size: 11px; color: #4b5563; }
        .table-wrapper { width: 100%; overflow-x: auto; }
        .tbl { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .tbl th, .tbl td { border: 1px solid #000; padding: 6px; }
        .tbl th { background-color: #f0f0f0; font-size: 9px; }
        .tbl td { font-size: 9px; }
        .section-row td { background: #e2e8f0; font-weight: bold; }
        .fondo-row td { background: #cbd5e1; font-weight: bold; }
        .label-row td { font-weight: bold; }
        .nested { padding-left: 12px; }
        .footer { margin-top: 18px; font-size: 8px; color: #475569; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>Catálogo de Secciones, Series y Subseries</h1>
        <h2>Catálogo de Disposición Documental</h2>
    </div>

    <div class="table-wrapper">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nivel</th>
                    <th>Nombre Serie / Subserie</th>
                    <th>AD</th>
                    <th>LE</th>
                    <th>COR</th>
                    <th>AT</th>
                    <th>AC</th>
                    <th>TA</th>
                    <th>Eliminación</th>
                    <th>Conservación</th>
                    <th>Muestreo</th>
                    <th>Observaciones</th>
                    <th>R</th>
                    <th>C</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fondos as $fondo)
                    <tr class="fondo-row">
                        <td colspan="15">FONDO: {{ $fondo->codigo }} - {{ $fondo->nombre }}</td>
                    </tr>

                    @foreach($fondo->subfondos as $subfondo)
                        <tr class="section-row">
                            <td colspan="15">Subfondo: {{ $subfondo->codigo }} - {{ $subfondo->nombre }}</td>
                        </tr>
                        @foreach($subfondo->secciones as $seccion)
                            <tr class="section-row">
                                <td colspan="15">Sección: {{ $seccion->codigo }} - {{ $seccion->nombre }}</td>
                            </tr>
                            @foreach($seccion->series as $serie)
                                <tr>
                                    <td>{{ $serie->codigo }}</td>
                                    <td>SERIE</td>
                                    <td class="nested">{{ $serie->nombre }}</td>
                                    <td>{{ $serie->vigencia_ad ? 'X' : '' }}</td>
                                    <td>{{ $serie->vigencia_le ? 'X' : '' }}</td>
                                    <td>{{ $serie->vigencia_confi ? 'X' : '' }}</td>
                                    <td>{{ $serie->plazo_at ?? '-' }}</td>
                                    <td>{{ $serie->plazo_ac ?? '-' }}</td>
                                    <td>{{ $serie->plazo_ta ?? '-' }}</td>
                                    <td>{{ $serie->tecnica_eliminacion ? 'X' : '' }}</td>
                                    <td>{{ $serie->tecnica_conservacion ? 'X' : '' }}</td>
                                    <td>{{ $serie->tecnica_muestreo ? 'X' : '' }}</td>
                                    <td>{{ $serie->observaciones ?? '-' }}</td>
                                    <td>{{ $serie->clasificacion_r ? 'X' : '' }}</td>
                                    <td>{{ $serie->clasificacion_c ? 'X' : '' }}</td>
                                </tr>
                                @foreach($serie->subseries as $subserie)
                                    <tr>
                                        <td>{{ $subserie->codigo }}</td>
                                        <td>SUBSERIE</td>
                                        <td class="nested">{{ $subserie->nombre }}</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach

                    @foreach($fondo->secciones as $seccion)
                        @if(!$seccion->id_subfondo)
                            <tr class="section-row">
                                <td colspan="15">Sección: {{ $seccion->codigo }} - {{ $seccion->nombre }}</td>
                            </tr>
                            @foreach($seccion->series as $serie)
                                <tr>
                                    <td>{{ $serie->codigo }}</td>
                                    <td>SERIE</td>
                                    <td class="nested">{{ $serie->nombre }}</td>
                                    <td>{{ $serie->vigencia_ad ? 'X' : '' }}</td>
                                    <td>{{ $serie->vigencia_le ? 'X' : '' }}</td>
                                    <td>{{ $serie->vigencia_confi ? 'X' : '' }}</td>
                                    <td>{{ $serie->plazo_at ?? '-' }}</td>
                                    <td>{{ $serie->plazo_ac ?? '-' }}</td>
                                    <td>{{ $serie->plazo_ta ?? '-' }}</td>
                                    <td>{{ $serie->tecnica_eliminacion ? 'X' : '' }}</td>
                                    <td>{{ $serie->tecnica_conservacion ? 'X' : '' }}</td>
                                    <td>{{ $serie->tecnica_muestreo ? 'X' : '' }}</td>
                                    <td>{{ $serie->observaciones ?? '-' }}</td>
                                    <td>{{ $serie->clasificacion_r ? 'X' : '' }}</td>
                                    <td>{{ $serie->clasificacion_c ? 'X' : '' }}</td>
                                </tr>
                                @foreach($serie->subseries as $subserie)
                                    <tr>
                                        <td>{{ $subserie->codigo }}</td>
                                        <td>SUBSERIE</td>
                                        <td class="nested">{{ $subserie->nombre }}</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Generado el día de la impresión.
    </div>
</div>
</body>
</html>
