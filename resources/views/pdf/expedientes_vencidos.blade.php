<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de expedientes vencidos</title>
    <style>
        @page {
            margin: 12mm 10mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
            margin: 0;
            padding: 0;
        }
        h1, h2, h3, p { margin: 0 0 6px; }
        .header {
            border: 1px solid #444;
            padding: 8px 10px;
            margin-bottom: 10px;
            background: #f7f7f7;
        }
        .header h1 {
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7.5px;
        }
        th, td {
            border: 1px solid #444;
            padding: 3px 4px;
            vertical-align: top;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: anywhere;
            word-break: break-all;
            white-space: normal;
        }
        th {
            background: #e9ecef;
            font-size: 7px;
            text-transform: uppercase;
        }
        .col-clave { width: 14%; }
        .col-codigo { width: 10%; }
        .col-nombre { width: 14%; }
        .col-serie { width: 11%; }
        .col-subserie { width: 8%; }
        .col-fondo { width: 9%; }
        .col-seccion { width: 9%; }
        .col-valor { width: 7%; }
        .col-plazo { width: 4%; }
        .col-fecha { width: 6%; }
        .col-dias { width: 5%; }
        .muted { color: #666; }
        .title-row {
            width: 100%;
            margin-bottom: 10px;
            position: relative;
            min-height: 52px;
        }
        .left-brand {
            position: absolute;
            left: 0;
            top: 0;
        }
        .right-brand {
            position: absolute;
            right: 0;
            top: 0;
        }
        .left-brand img,
        .right-brand img {
            height: 52px;
            width: auto;
            max-width: 110px;
        }
        .title-block {
            text-align: center;
            width: 100%;
            padding: 0 120px;
            box-sizing: border-box;
        }
        .title-block h1 {
            font-size: 13px;
            margin-bottom: 2px;
        }
        .title-block .muted {
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="title-row">
        <div class="left-brand">
            <img src="{{ public_path('assets/QRletras.png') }}" alt="QR letras">
        </div>
        <div class="title-block">
            <h1>Gobierno del Estado de Quintana Roo</h1>
            <p class="muted">Reporte de expedientes vencidos</p>
        </div>
        <div class="right-brand">
            <img src="{{ public_path('assets/logo.png') }}" alt="Logo">
        </div>
    </div>

    <div class="header">
        <h1>Reporte de expedientes vencidos</h1>
        <p>Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        @if($serieSeleccionada)
            <p><strong>Serie:</strong> {{ $serieSeleccionada }}</p>
        @endif
        @if($fechaDesde || $fechaHasta)
            <p><strong>Rango de cierre:</strong> {{ $fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') : '-' }} al {{ $fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') : '-' }}</p>
        @endif
        @if($valorDocumental)
            <p><strong>Valor documental:</strong> {{ $valorDocumental }}</p>
        @endif
        @if($busqueda)
            <p><strong>Búsqueda:</strong> {{ $busqueda }}</p>
        @endif
    </div>

    @if(count($expedientes) === 0)
        <p class="muted">No se encontraron expedientes con los filtros aplicados.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th class="col-clave">Clave</th>
                    <th class="col-codigo">Código</th>
                    <th class="col-nombre">Nombre</th>
                    <th class="col-serie">Serie</th>
                    <th class="col-subserie">Subserie</th>
                    <th class="col-fondo">Fondo</th>
                    <th class="col-seccion">Sección</th>
                    <th class="col-valor">Valor documental</th>
                    <th class="col-plazo">Plazo AT</th>
                    <th class="col-plazo">Plazo AC</th>
                    <th class="col-fecha">Fecha cierre</th>
                    <th class="col-fecha">Fecha vencimiento</th>
                    <th class="col-dias">Días vencidos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expedientes as $expediente)
                    <tr>
                        <td class="col-clave">{{ $expediente['clave'] ?? '' }}</td>
                        <td class="col-codigo">{{ $expediente['codigo'] ?? '' }}</td>
                        <td class="col-nombre">{{ $expediente['nombre'] ?? '' }}</td>
                        <td class="col-serie">{{ $expediente['serie'] ?? '' }}</td>
                        <td class="col-subserie">{{ $expediente['subserie'] ?? '' }}</td>
                        <td class="col-fondo">{{ $expediente['fondo'] ?? '' }}</td>
                        <td class="col-seccion">{{ $expediente['seccion'] ?? '' }}</td>
                        <td class="col-valor">{{ $expediente['valor_documental'] ?? '' }}</td>
                        <td class="col-plazo">{{ $expediente['plazo_at'] ?? '' }}</td>
                        <td class="col-plazo">{{ $expediente['plazo_ac'] ?? '' }}</td>
                        <td class="col-fecha">{{ $expediente['fecha_cierre'] ? \Carbon\Carbon::parse($expediente['fecha_cierre'])->format('d/m/Y') : '' }}</td>
                        <td class="col-fecha">{{ $expediente['fecha_vencimiento'] ? \Carbon\Carbon::parse($expediente['fecha_vencimiento'])->format('d/m/Y') : '' }}</td>
                        <td class="col-dias">{{ $expediente['dias_vencidos'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
