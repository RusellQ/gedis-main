<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cuadro General de Clasificación Archivística</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .nivel {
            margin-left: 20px;
        }

        .titulo {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px;
        }
    </style>
</head>

<body>
    <h1 style="text-align: center; margin-bottom: 20px;">
        Cuadro General de Clasificación Archivística
    </h1>

    @foreach($fondos as $fondo)
    <div class="titulo">Fondo: {{ $fondo->codigo }} - {{ $fondo->nombre }}</div>

    @foreach($fondo->subfondos as $subfondo)
    <div class="nivel titulo">Subfondo: {{ $subfondo->codigo }} - {{ $subfondo->nombre }}</div>

    @foreach($subfondo->secciones as $seccion)
    <div class="nivel">Sección: {{ $seccion->codigo }} - {{ $seccion->nombre }}</div>

    @foreach($seccion->series as $serie)
    <div class="nivel" style="margin-left: 40px;">Serie: {{ $serie->codigo }} - {{ $serie->nombre }}</div>

    @foreach($serie->subseries as $subserie)
    <div class="nivel" style="margin-left: 60px;">Subserie: {{ $subserie->codigo }} - {{ $subserie->nombre }}</div>
    @endforeach
    @endforeach
    @endforeach
    @endforeach

    {{-- Secciones que no tienen subfondo --}}
    @foreach($fondo->secciones as $seccion)
    @if(!$seccion->id_subfondo)
    <div class="nivel titulo">Sección: {{ $seccion->codigo }} - {{ $seccion->nombre }}</div>
    @foreach($seccion->series as $serie)
    <div class="nivel" style="margin-left: 40px;">Serie: {{ $serie->codigo }} - {{ $serie->nombre }}</div>
    @foreach($serie->subseries as $subserie)
    <div class="nivel" style="margin-left: 60px;">Subserie: {{ $subserie->codigo }} - {{ $subserie->nombre }}</div>
    @endforeach
    @endforeach
    @endif
    @endforeach

    <br>
    @endforeach
</body>

</html>