<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Disposición Documental</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            background: #fff;
            color: #000;
        }

        .page {
            padding: 14px 16px;
            width: 100%;
        }

        /* ── HEADER ── */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1.5px solid #000;
            padding: 6px 10px;
            margin-bottom: 0;
        }

        .header-logo-left {
            width: 60px;
            flex-shrink: 0;
        }

        .header-logo-left img {
            width: 100%;
            height: auto;
        }

        .header-title {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }

        .header-title h1 {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-logo-right {
            width: 120px;
            flex-shrink: 0;
            text-align: right;
        }

        .header-logo-right img {
            width: 100%;
            height: auto;
        }

        /* ── FONDO ROW ── */
        .fondo-label {
            border: 1px solid #000;
            border-top: none;
            padding: 3px 6px;
            font-size: 8.5px;
            font-weight: bold;
            background: #fff;
        }

        /* ── TABLE ── */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .tbl {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .tbl th,
        .tbl td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            line-height: 1.2;
        }

        /* Header rows */
        .tbl thead tr th {
            background: #e8e8e8;
            font-weight: bold;
        }

        .th-main {
            font-size: 7px;
        }

        /* The "Nombre Serie / Subserie" column left-aligned */
        .tbl td.nombre,
        .tbl th.nombre {
            text-align: left;
            padding-left: 6px;
        }

        /* Grouped header cells */
        .th-group {
            background: #d0d0d0 !important;
        }

        /* Section rows */
        .row-fondo td {
            background: #b0b8c8;
            font-weight: bold;
            font-size: 8px;
            text-align: left;
            padding-left: 4px;
        }

        .row-subfondo td {
            background: #c8d0dc;
            font-weight: bold;
            font-size: 7.5px;
            text-align: left;
            padding-left: 8px;
        }

        .row-seccion td {
            background: #dde3ec;
            font-weight: bold;
            font-size: 7.5px;
            text-align: left;
            padding-left: 4px;
        }

        /* Funciones comunes */
        .row-funciones td {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
        }

        /* Serie / Subserie data rows */
        .row-serie td {
            background: #fff;
        }

        .row-subserie td {
            background: #fafafa;
        }

        .row-subserie td.nombre {
            padding-left: 18px;
        }

        /* Footer */
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-size: 7.5px;
            color: #333;
        }

        .footer-page {
            font-weight: bold;
        }

        /* Rotated text for grouped headers */
        .rotate-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            font-size: 6.5px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="page">

    <!-- ══ HEADER ══ -->
    <div class="header">
        <!-- Logo izquierdo (escudo del gobierno / logo2) -->
        <div class="header-logo-left">
            <!--
                Reemplaza el src con la ruta real de tu logo izquierdo.
                Ejemplo: <img src="/images/logo_gobierno.png" alt="Gobierno">
            -->
            <img src="{{ $logo_izquierdo ?? '/images/logo_izquierdo.png' }}" alt="Gobierno del Estado">
        </div>

        <!-- Título central -->
        <div class="header-title">
            <h1>Catálogo de Disposición Documental</h1>
        </div>

        <!-- Logo derecho Quintana Roo (logo1) -->
        <div class="header-logo-right">
            <!--
                Reemplaza el src con la ruta real del logo de Quintana Roo.
                Ejemplo: <img src="/images/logo_qroo.png" alt="Quintana Roo">
            -->
            <img src="{{ $logo_derecho ?? '/images/logo_quintanaroo.png' }}" alt="Quintana Roo Unidos para Transformar">
        </div>
    </div>

    <!-- ══ FONDO ══ -->
    <div class="fondo-label">
        Fondo: {{ $fondo_nombre ?? 'SECRETARÍA DE GOBIERNO' }}
    </div>

    <!-- ══ TABLA ══ -->
    <div class="table-wrapper">
        <table class="tbl">
            <thead>
                <!--
                    Fila 1: encabezados agrupados
                    Estructura: Código | Niveles | Nombre | Valor Documental (AD,LE,COR) |
                                Plazos de Conservación (AT,AC,TA) | Técnicas de Selección (Elim,Cons,Muestreo) |
                                Observaciones | Clasificación (R,C)
                -->
                <tr>
                    <th rowspan="2" class="th-main" style="width:40px;">Código</th>
                    <th rowspan="2" class="th-main" style="width:40px;">Niveles de<br>clasificación</th>
                    <th rowspan="2" class="th-main nombre" style="min-width:160px;">Nombre Serie / Subserie</th>

                    <!-- Vigencia / Valor Documental -->
                    <th colspan="3" class="th-main th-group">Valor Documental</th>

                    <!-- Plazos de Conservación -->
                    <th colspan="3" class="th-main th-group">Plazos de<br>Conservación</th>

                    <!-- Técnicas de selección -->
                    <th colspan="3" class="th-main th-group">Técnicas de selección</th>

                    <th rowspan="2" class="th-main" style="width:60px;">Observaciones</th>

                    <!-- Clasificación de la Información -->
                    <th colspan="2" class="th-main th-group">Clasificación de la<br>Información</th>
                </tr>
                <tr>
                    <!-- Valor Documental sub-cols -->
                    <th class="th-main" style="width:14px;">AD</th>
                    <th class="th-main" style="width:14px;">LE</th>
                    <th class="th-main" style="width:14px;">COR</th>

                    <!-- Plazos sub-cols -->
                    <th class="th-main" style="width:14px;">AT</th>
                    <th class="th-main" style="width:14px;">AC</th>
                    <th class="th-main" style="width:14px;">TA</th>

                    <!-- Técnicas sub-cols -->
                    <th class="th-main" style="width:20px;">Eliminación</th>
                    <th class="th-main" style="width:20px;">Conservación</th>
                    <th class="th-main" style="width:20px;">Muestreo</th>

                    <!-- Clasificación sub-cols -->
                    <th class="th-main" style="width:14px;">R</th>
                    <th class="th-main" style="width:14px;">C</th>
                </tr>
            </thead>
            <tbody>

                @foreach($fondos as $fondo)

                    {{-- FONDO --}}
                    <tr class="row-fondo">
                        <td colspan="15">FONDO: {{ $fondo->codigo }} — {{ $fondo->nombre }}</td>
                    </tr>

                    {{-- SUBFONDOS --}}
                    @foreach($fondo->subfondos as $subfondo)
                        <tr class="row-subfondo">
                            <td colspan="15">Subfondo: {{ $subfondo->codigo }} — {{ $subfondo->nombre }}</td>
                        </tr>

                        @foreach($subfondo->secciones as $seccion)
                            {{-- SECCIÓN --}}
                            <tr class="row-seccion">
                                <td colspan="15">Sección: {{ $seccion->codigo }} {{ $seccion->nombre }}</td>
                            </tr>

                            {{-- FUNCIONES COMUNES (si aplica) --}}
                            @if($seccion->es_funciones_comunes ?? false)
                                <tr class="row-funciones">
                                    <td colspan="15">FUNCIONES COMUNES</td>
                                </tr>
                            @endif

                            {{-- SERIES --}}
                            @foreach($seccion->series as $serie)
                                <tr class="row-serie">
                                    <td>{{ $serie->codigo }}</td>
                                    <td>SERIE</td>
                                    <td class="nombre">{{ $serie->nombre }}</td>
                                    <td>{{ $serie->vigencia_ad    ? 'X' : '' }}</td>
                                    <td>{{ $serie->vigencia_le    ? 'X' : '' }}</td>
                                    <td>{{ $serie->vigencia_confi ? 'X' : '' }}</td>
                                    <td>{{ $serie->plazo_at       ?? '-' }}</td>
                                    <td>{{ $serie->plazo_ac       ?? '-' }}</td>
                                    <td>{{ $serie->plazo_ta       ?? '-' }}</td>
                                    <td>{{ $serie->tecnica_eliminacion   ? 'X' : '' }}</td>
                                    <td>{{ $serie->tecnica_conservacion  ? 'X' : '' }}</td>
                                    <td>{{ $serie->tecnica_muestreo      ? 'X' : '' }}</td>
                                    <td class="nombre">{{ $serie->observaciones ?? '' }}</td>
                                    <td>{{ $serie->clasificacion_r ? 'X' : '' }}</td>
                                    <td>{{ $serie->clasificacion_c ? 'X' : '' }}</td>
                                </tr>

                                {{-- SUBSERIES --}}
                                @foreach($serie->subseries as $subserie)
                                    <tr class="row-subserie">
                                        <td>{{ $subserie->codigo }}</td>
                                        <td>SUBSERIE</td>
                                        <td class="nombre" style="padding-left:18px;">{{ $subserie->nombre }}</td>
                                        <td>{{ $subserie->vigencia_ad    ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->vigencia_le    ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->vigencia_confi ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->plazo_at       ?? '-' }}</td>
                                        <td>{{ $subserie->plazo_ac       ?? '-' }}</td>
                                        <td>{{ $subserie->plazo_ta       ?? '-' }}</td>
                                        <td>{{ $subserie->tecnica_eliminacion   ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->tecnica_conservacion  ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->tecnica_muestreo      ? 'X' : '-' }}</td>
                                        <td class="nombre">{{ $subserie->observaciones ?? '-' }}</td>
                                        <td>{{ $subserie->clasificacion_r ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->clasificacion_c ? 'X' : '-' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach

                    {{-- SECCIONES DIRECTAS DEL FONDO (sin subfondo) --}}
                    @foreach($fondo->secciones as $seccion)
                        @if(!$seccion->id_subfondo)

                            <tr class="row-seccion">
                                <td colspan="15">Sección: {{ $seccion->codigo }} {{ $seccion->nombre }}</td>
                            </tr>

                            @if($seccion->es_funciones_comunes ?? false)
                                <tr class="row-funciones">
                                    <td colspan="15">FUNCIONES COMUNES</td>
                                </tr>
                            @endif

                            @foreach($seccion->series as $serie)
                                <tr class="row-serie">
                                    <td>{{ $serie->codigo }}</td>
                                    <td>SERIE</td>
                                    <td class="nombre">{{ $serie->nombre }}</td>
                                    <td>{{ $serie->vigencia_ad    ? 'X' : '' }}</td>
                                    <td>{{ $serie->vigencia_le    ? 'X' : '' }}</td>
                                    <td>{{ $serie->vigencia_confi ? 'X' : '' }}</td>
                                    <td>{{ $serie->plazo_at       ?? '-' }}</td>
                                    <td>{{ $serie->plazo_ac       ?? '-' }}</td>
                                    <td>{{ $serie->plazo_ta       ?? '-' }}</td>
                                    <td>{{ $serie->tecnica_eliminacion   ? 'X' : '' }}</td>
                                    <td>{{ $serie->tecnica_conservacion  ? 'X' : '' }}</td>
                                    <td>{{ $serie->tecnica_muestreo      ? 'X' : '' }}</td>
                                    <td class="nombre">{{ $serie->observaciones ?? '' }}</td>
                                    <td>{{ $serie->clasificacion_r ? 'X' : '' }}</td>
                                    <td>{{ $serie->clasificacion_c ? 'X' : '' }}</td>
                                </tr>

                                @foreach($serie->subseries as $subserie)
                                    <tr class="row-subserie">
                                        <td>{{ $subserie->codigo }}</td>
                                        <td>SUBSERIE</td>
                                        <td class="nombre" style="padding-left:18px;">{{ $subserie->nombre }}</td>
                                        <td>{{ $subserie->vigencia_ad    ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->vigencia_le    ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->vigencia_confi ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->plazo_at       ?? '-' }}</td>
                                        <td>{{ $subserie->plazo_ac       ?? '-' }}</td>
                                        <td>{{ $subserie->plazo_ta       ?? '-' }}</td>
                                        <td>{{ $subserie->tecnica_eliminacion   ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->tecnica_conservacion  ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->tecnica_muestreo      ? 'X' : '-' }}</td>
                                        <td class="nombre">{{ $subserie->observaciones ?? '-' }}</td>
                                        <td>{{ $subserie->clasificacion_r ? 'X' : '-' }}</td>
                                        <td>{{ $subserie->clasificacion_c ? 'X' : '-' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach

                        @endif
                    @endforeach

                @endforeach

            </tbody>
        </table>
    </div>

    <!-- ══ FOOTER ══ -->
    <div class="footer">
        <span>Generado el: {{ now()->format('d/m/Y') }}</span>
        <span class="footer-page">Página {{ $page ?? '1' }} de {{ $total_pages ?? '1' }}</span>
    </div>

</div>
</body>
</html>