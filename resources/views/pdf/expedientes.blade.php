<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte de Expedientes</title>
    <style>
        body {
            position: relative;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            position: relative;
            border: 2px solid #333;
            padding: 10px 40px;
            margin-bottom: 20px;
            height: 150px;
            box-sizing: border-box;
            text-align: center;
        }

        .logo-left {
            width: 250px;
            height: auto;
            position: absolute;
            bottom: 10px;
            /* 10 píxeles desde abajo */
            left: 1px;
            /* más a la izquierda ahora que no hay margen */
        }

        .logo-right {
            width: 120px;
            /* ajusta si quieres más grande */
            height: auto;
            position: absolute;
            top: 30px;
            right: 50px;
            /* aumenta el espacio a la izquierda */
        }


        h1,
        h2,
        h3 {
            margin: 0;
            padding: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
            word-wrap: break-word;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
            white-space: normal;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="{{ public_path('assets/images/escudo.png') }}" class="logo-left">
        <img src="{{ public_path('assets/images/gobierno.png') }}" class="logo-right">
        <h1>Gobierno del Estado de Quintana Roo</h1>
        <h2>
            INVENTARIO GENERAL DE ARCHIVO
            @if($estadoFiltro)
            @switch($estadoFiltro)
            @case('en_tramite') en trámite @break
            @case('en_concentracion') en concentración @break
            @case('en_historico') en histórico @break
            @endswitch
            @endif
        </h2>
        <h3><strong>Fecha de generación:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</h3>

        @if($fechaInicio || $fechaFin)
        <p><strong>Reporte filtrado por fechas:</strong>
            @if($fechaInicio)
            {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}
            @endif
            @if($fechaInicio && $fechaFin)
            a
            @endif
            @if($fechaFin)
            {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
            @endif
        </p>
        @endif
    </div>

    <table>
    <thead>
        <tr>
            <th rowspan="2" style="width: 8%;">CÓDIGO DE CLASIFICACIÓN ARCHIVÍSTICA</th>
            <th rowspan="2" style="width: 6%;">NO. IDENTIFICACIÓN DEL EXPEDIENTE</th>
            <th rowspan="2" style="width: 12%;">NOMBRE DEL EXPEDIENTE</th>
            <th rowspan="2" style="width: 14%;">DESCRIPCIÓN DEL ASUNTO DEL EXPEDIENTE</th>

            <th colspan="2" style="width: 10%;">FECHAS EXTREMAS</th>

            <th rowspan="2" style="width: 6%;">NÚMERO DE FOJAS</th>

            <th colspan="2" style="width: 8%;">TRADICIÓN DOCUMENTAL</th>
            <th colspan="2" style="width: 8%;">SOPORTE DOCUMENTAL</th>

            <th colspan="3" style="width: 9%;">VALOR DOCUMENTAL</th>

            <th colspan="2" style="width: 8%;">VIGENCIA DOCUMENTAL</th>
        </tr>

        <tr>
            <th>INICIO</th>
            <th>CONCLUSIÓN</th>

            <th>ORIGINAL</th>
            <th>COPIA</th>

            <th>ORIGINAL</th>
            <th>COPIA</th>

            <th>A</th>
            <th>L</th>
            <th>C</th>

            <th>AT</th>
            <th>AC</th>
        </tr>
    </thead>

    <tbody>
        @foreach($expedientes as $exp)
        <tr>
            <!-- Código de clasificación -->
            <td>{{ $exp->clave }}</td>

            <!-- No identificación -->
            <td>{{ $exp->id }}</td>

            <!-- Nombre -->
            <td>{{ $exp->nombre }}</td>

            <!-- Descripción -->
            <td>{{ $exp->descripcion_expediente }}</td>

            <!-- Fechas extremas -->
            <td>
                {{ $exp->fecha_apertura ? \Carbon\Carbon::parse($exp->fecha_apertura)->format('d/m/Y') : '' }}
            </td>
            <td>
                {{ $exp->fecha_cierre ? \Carbon\Carbon::parse($exp->fecha_cierre)->format('d/m/Y') : '' }}
            </td>

            <!-- Número de fojas -->
            <td>{{ $exp->no_hojas }}</td>

            <!-- Tradición documental -->
            <td style="text-align:center;">
                {{ $exp->tradicion_documental === 'Original' ? 'X' : '' }}
            </td>
            <td style="text-align:center;">
                {{ $exp->tradicion_documental === 'Copia' ? 'X' : '' }}
            </td>

            <!-- Soporte documental -->
            <td style="text-align:center;">
                {{ $exp->soporte_documental === 'Original' ? 'X' : '' }}
            </td>
            <td style="text-align:center;">
                {{ $exp->soporte_documental === 'Copia' ? 'X' : '' }}
            </td>

            <!-- Valor documental -->
            <td style="text-align:center;">
                {{ $exp->caracter === 'Administrativo' ? 'X' : '' }}
            </td>
            <td style="text-align:center;">
                {{ $exp->caracter === 'Legal' ? 'X' : '' }}
            </td>
            <td style="text-align:center;">
                {{ $exp->caracter === 'Contable' ? 'X' : '' }}
            </td>

            <!-- Vigencia documental -->
            <td style="text-align:center;">
                {{ $exp->vigencia }}
            </td>
            <td style="text-align:center;">
                {{ $exp->tiempo_conservacion }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>


</body>

</html>