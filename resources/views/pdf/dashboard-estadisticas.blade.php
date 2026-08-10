<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estadísticas del sistema</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            border: 2px solid #333;
            margin-bottom: 20px;
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: middle;
            padding: 8px 6px;
            border: none;
        }

        .header-logo {
            width: 22%;
            text-align: center;
        }

        .header-logo img {
            max-width: 110px;
            max-height: 90px;
            height: auto;
        }

        .header-center {
            width: 56%;
            text-align: center;
            padding-left: 8px;
            padding-right: 8px;
        }

        .header-center h1 {
            font-size: 13px;
            margin: 0 0 4px 0;
            font-weight: bold;
        }

        .header-center h2 {
            font-size: 11px;
            margin: 0 0 4px 0;
            font-weight: bold;
            line-height: 1.25;
        }

        .header-center h3 {
            font-size: 10px;
            margin: 0;
            font-weight: normal;
        }

        .meta {
            margin: 0 0 16px 0;
            text-align: center;
            color: #333;
            font-size: 11px;
        }

        .chart-wrap {
            text-align: center;
            margin-top: 10px;
        }

        .chart-wrap img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('assets/images/escudo.png') }}" alt="Escudo">
            </td>
            <td class="header-center">
                <h1>Gobierno del Estado de Quintana Roo</h1>
                <h2>{{ $titulo }}</h2>
                <h3><strong>Fecha de generación:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</h3>
            </td>
            <td class="header-logo">
                <img src="{{ public_path('assets/images/gobierno.png') }}" alt="Gobierno">
            </td>
        </tr>
    </table>

    <p class="meta">
        <strong>{{ $subtituloTipo }}</strong><br>
        Filtro: {{ $dimensionLabel }}<br>
        {{ $alcanceLabel }}
        @if($total > 0)
            — Total: {{ $total }}
        @endif
    </p>

    <div class="chart-wrap">
        <img src="{{ $chartImage }}" alt="Gráfica de estadísticas">
    </div>
</body>
</html>
