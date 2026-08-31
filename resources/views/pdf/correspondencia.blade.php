<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Correspondencia {{ $correspondencia->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
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
        }

        .metadata {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .metadata-row {
            margin: 5px 0;
            font-size: 11px;
        }

        .metadata-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .contenido {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #dee2e6;
            min-height: 200px;
            page-break-inside: avoid;
        }

        .firma-block {
            margin-top: 40px;
            padding: 15px;
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
        }

        .firma-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #004085;
        }

        .firma-info {
            font-size: 11px;
            margin: 3px 0;
        }

        .certificado-info {
            font-size: 10px;
            background-color: #f0f0f0;
            padding: 8px;
            margin-top: 10px;
            border-radius: 3px;
            word-break: break-all;
            max-height: 100px;
            overflow: hidden;
        }

        .pie-pagina {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            text-align: center;
            color: #666;
        }

        .expediente-info {
            font-size: 10px;
            color: #666;
            margin-top: 10px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            margin: 0 3px;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        h3 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin: 20px 0 10px 0;
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <table class="header">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('assets/images/escudo.png') }}" alt="Escudo">
            </td>
            <td class="header-center">
                <h1>Gobierno del Estado de Quintana Roo</h1>
                <h2>Archivo General del Estado</h2>
            </td>
            <td class="header-logo">
                <img src="{{ public_path('assets/images/gobierno.png') }}" alt="Gobierno">
            </td>
        </tr>
    </table>

    <!-- Título -->
    <h3>Documento de Correspondencia</h3>

    <!-- Metadatos -->
    <div class="metadata">
        <div class="metadata-row">
            <span class="metadata-label">Número:</span>
            Doc-{{ $correspondencia->id }}
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Asunto:</span>
            <strong>{{ $correspondencia->asunto }}</strong>
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Remitente:</span>
            {{ $correspondencia->remitente ?? '(No especificado)' }}
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Destinatario:</span>
            {{ $correspondencia->destinatario ?? '(No especificado)' }}
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Clasificación:</span>
            <span
                class="badge {{ $correspondencia->clasificacion === 'publica' ? 'badge-info' : ($correspondencia->clasificacion === 'reservada' ? 'badge-warning' : 'badge-danger') }}">
                {{ strtoupper($correspondencia->clasificacion) }}
            </span>
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Estado:</span>
            <span
                class="badge {{ $correspondencia->estado === 'firmado' ? 'badge-success' : 'badge-info' }}">
                {{ strtoupper($correspondencia->estado) }}
            </span>
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Fecha de creación:</span>
            {{ $correspondencia->fecha_creacion->format('d/m/Y H:i:s') }}
        </div>
        <div class="metadata-row">
            <span class="metadata-label">Autor:</span>
            {{ $correspondencia->usuarioAutor->nombre }} {{ $correspondencia->usuarioAutor->apellido_paterno }}
        </div>
        <div class="expediente-info">
            <strong>Expediente:</strong> {{ $correspondencia->expediente->nombre }} ({{ $correspondencia->expediente->codigo }})
        </div>
    </div>

    <!-- Contenido -->
    <h3>Contenido</h3>
    <div class="contenido">
        {!! $correspondencia->contenido !!}
    </div>

    <!-- Firma -->
    @if ($correspondencia->firma)
    <div class="firma-block">
        <div class="firma-title">
            <i class="fas fa-certificate"></i> Documento Firmado Electrónicamente
        </div>
        <div class="firma-info">
            <strong>Estado de firma:</strong>
            <span class="badge badge-success">{{ strtoupper($correspondencia->firma->estado_firma) }}</span>
        </div>
        <div class="firma-info">
            <strong>Firmante:</strong> {{ $correspondencia->firma->usuarioFirmante->nombre }}
            {{ $correspondencia->firma->usuarioFirmante->apellido_paterno }}
        </div>
        @if ($correspondencia->firma->timestamp_firma)
        <div class="firma-info">
            <strong>Timestamp de firma SAT:</strong>
            {{ $correspondencia->firma->timestamp_firma->format('d/m/Y H:i:s') }}
        </div>
        @endif
        @if ($correspondencia->firma->certificado_sat)
        <div class="certificado-info">
            <strong>Certificado (ref):</strong> {{ substr($correspondencia->firma->certificado_sat, 0, 100) }}...
        </div>
        @endif
    </div>
    @endif

    <!-- Pie de página -->
    <div class="pie-pagina">
        <p>
            Este documento fue generado automáticamente por el sistema GEDIS<br>
            Fecha de generación: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}<br>
            <strong>Este es un documento de carácter oficial del Archivo General del Estado de Quintana Roo</strong>
        </p>
    </div>
</body>
</html>
