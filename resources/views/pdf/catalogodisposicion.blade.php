<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Disposición Documental</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Catálogo de Disposición Documental</h2>

    <table>
        <thead>
            <tr>
                <th>Nivel</th>
                <th>Código</th>
                <th>Título</th>
                <th>Plazo de Conservación</th>
                <th>Valor Documental</th>
                <th>Destino Final</th>
                <th>Disposición Específica</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fondos as $fondo)
                <tr>
                    <td>Fondo</td>
                    <td>{{ $fondo->codigo }}</td>
                    <td>{{ $fondo->nombre }}</td>
                    <td colspan="4"></td>
                </tr>

                @foreach($fondo->subfondos as $subfondo)
                    <tr>
                        <td>Subfondo</td>
                        <td>{{ $subfondo->codigo }}</td>
                        <td>{{ $subfondo->nombre }}</td>
                        <td colspan="4"></td>
                    </tr>

                    @foreach($subfondo->secciones as $seccion)
                        <tr>
                            <td>Sección</td>
                            <td>{{ $seccion->codigo }}</td>
                            <td>{{ $seccion->nombre }}</td>
                            <td colspan="4"></td>
                        </tr>

                        @foreach($seccion->series as $serie)
                            <tr>
                                <td>Serie</td>
                                <td>{{ $serie->codigo }}</td>
                                <td>{{ $serie->nombre }}</td>
                                <td>{{ $serie->plazo_conservacion ?? '-' }}</td>
                                <td>{{ $serie->valor_documental ?? '-' }}</td>
                                <td>{{ $serie->destino_final ?? '-' }}</td>
                                <td>{{ $serie->disposicion_especifica ?? '-' }}</td>
                            </tr>

                            @foreach($serie->subseries as $subserie)
                                <tr>
                                    <td>Subserie</td>
                                    <td>{{ $subserie->codigo }}</td>
                                    <td>{{ $subserie->nombre }}</td>
                                    <td>{{ $subserie->plazo_conservacion ?? '-' }}</td>
                                    <td>{{ $subserie->valor_documental ?? '-' }}</td>
                                    <td>{{ $subserie->destino_final ?? '-' }}</td>
                                    <td>{{ $subserie->disposicion_especifica ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
