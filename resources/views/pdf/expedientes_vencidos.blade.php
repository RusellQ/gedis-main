<!DOCTYPE html>
<html lang="es">
    
    <meta charset="utf-8">
    <title>Reporte de expedientes vencidos</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 20px;
        }
        h1, h2, h3, p { margin: 0 0 8px; }
        .header {
            border: 1px solid #444;
            padding: 12px;
            margin-bottom: 14px;
            background: #f7f7f7;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #444;
            padding: 6px;
            vertical-align: top;
        }
        th {
            background: #e9ecef;
            text-align: left;
        }
        .muted { color: #666; }
        .title-row {
            width: 100%;
            margin-bottom: 12px;
            position: relative;
            min-height: 60px;
        }
        .left-brand {
            position: absolute;
            left: 0;
            top: 0;
            display: flex;
            align-items: center;
        }
        .right-brand {
            position: absolute;
            right: 0;
            top: 0;
            display: flex;
            align-items: center;
        }
        .left-brand img,
        .right-brand img {
            height: 68px;
            width: auto;
            max-width: 130px;
            object-fit: contain;
        }
        .title-block {
            text-align: center;
            width: 100%;
            padding: 0 120px;
            box-sizing: border-box;
        }
        .title-block h1 {
            font-size: 16px;
            margin-bottom: 4px;
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
                    <th>Clave</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Serie</th>
                    <th>Subserie</th>
                    <th>Fondo</th>
                    <th>Sección</th>
                    <th>Valor documental</th>
                    <th>Plazo AT</th>
                    <th>Plazo AC</th>
                    <th>Fecha cierre</th>
                    <th>Fecha vencimiento</th>
                    <th>Días vencidos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expedientes as $expediente)
                    <tr>
                        <td>{{ $expediente['clave'] ?? '' }}</td>
                        <td>{{ $expediente['codigo'] ?? '' }}</td>
                        <td>{{ $expediente['nombre'] ?? '' }}</td>
                        <td>{{ $expediente['serie'] ?? '' }}</td>
                        <td>{{ $expediente['subserie'] ?? '' }}</td>
                        <td>{{ $expediente['fondo'] ?? '' }}</td>
                        <td>{{ $expediente['seccion'] ?? '' }}</td>
                        <td>{{ $expediente['valor_documental'] ?? '' }}</td>
                        <td>{{ $expediente['plazo_at'] ?? '' }}</td>
                        <td>{{ $expediente['plazo_ac'] ?? '' }}</td>
                        <td>{{ $expediente['fecha_cierre'] ? \Carbon\Carbon::parse($expediente['fecha_cierre'])->format('d/m/Y') : '' }}</td>
                        <td>{{ $expediente['fecha_vencimiento'] ? \Carbon\Carbon::parse($expediente['fecha_vencimiento'])->format('d/m/Y') : '' }}</td>
                        <td>{{ $expediente['dias_vencidos'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
