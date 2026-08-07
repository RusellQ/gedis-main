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
            left: 1px;
        }

        .logo-right {
            width: 120px;
            height: auto;
            position: absolute;
            top: 30px;
            right: 50px;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 4px 0;
        }

        .meta {
            margin: 0 0 16px 0;
            text-align: center;
            color: #333;
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
    <div class="header">
        <img src="{{ public_path('assets/images/escudo.png') }}" class="logo-left" alt="Escudo">
        <img src="{{ public_path('assets/images/gobierno.png') }}" class="logo-right" alt="Gobierno">
        <h1>Gobierno del Estado de Quintana Roo</h1>
        <h2>{{ $titulo }}</h2>
        <h3><strong>Fecha de generación:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</h3>
    </div>

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
