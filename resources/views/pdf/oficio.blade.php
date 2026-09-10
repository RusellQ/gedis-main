<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Oficio {{ $correspondencia->numero_oficio ?? $correspondencia->id }}</title>
    <style>
        @page {
            margin: 28px 48px 58px 48px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.45;
        }

        /* Pie institucional a ancho de página (imagen 1 completa) */
        .page-footer {
            position: fixed;
            left: -48px;
            bottom: -58px;
            width: 708px; /* letter 612 + márgenes 48*2 */
            z-index: -1000;
        }
        .page-footer img {
            width: 708px;
            height: auto;
            display: block;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .header td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .header-left {
            width: 26%;
            text-align: left;
        }
        .header-left img {
            width: 92px;
            height: auto;
        }
        .header-right {
            width: 74%;
            text-align: right;
        }
        .header-right .logo-qroo {
            width: 195px;
            height: auto;
            margin-bottom: 10px;
        }

        .meta {
            text-align: right;
            font-size: 10.5px;
            line-height: 1.38;
        }
        .meta .entidad {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10.5px;
            margin-bottom: 2px;
        }
        .meta .num {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .meta .asunto {
            margin-bottom: 2px;
        }
        .meta .asunto strong {
            font-weight: bold;
        }
        .meta .fecha {
            font-style: italic;
            margin-bottom: 6px;
        }
        .meta .leyenda {
            font-style: italic;
            font-size: 9px;
            max-width: 340px;
            margin-left: auto;
            text-align: center;
            display: block;
        }

        .destinatario {
            margin: 28px 0 18px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            line-height: 1.35;
        }

        .cuerpo {
            margin: 8px 0;
            text-align: justify;
            font-size: 11px;
        }
        .cuerpo p {
            margin: 0 0 8px;
        }
        .cuerpo table {
            border-collapse: collapse;
            width: 100%;
            margin: 12px 0;
            font-size: 10px;
        }
        .cuerpo table th,
        .cuerpo table td {
            border: 1px solid #222;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            background: #fff;
        }
        .cuerpo table th {
            background: #efefef;
            font-weight: bold;
        }

        .cierre {
            margin-top: 14px;
            text-align: justify;
        }

        .atentamente {
            text-align: center;
            margin: 36px 0 10px;
            letter-spacing: 3px;
            font-weight: bold;
            font-size: 12px;
        }

        .firma {
            text-align: center;
            margin-top: 56px;
            margin-bottom: 8px;
        }
        .firma .nombre {
            font-weight: bold;
            font-size: 11px;
        }
        .firma .cargo {
            font-size: 10px;
            margin-top: 2px;
        }

        .ccp {
            margin-top: 28px;
            font-size: 9px;
            line-height: 1.3;
        }

        .sandbox {
            margin-top: 8px;
            font-size: 8px;
            color: #066;
        }
    </style>
</head>
<body>
    @php
        $escudo = public_path('assets/images/escudo.png');
        $headerQroo = public_path('assets/images/oficio/header-qroo.png');
        $pie = public_path('assets/images/oficio/pie-agqroo.png');
        if (!file_exists($pie)) {
            $pie = public_path('assets/images/oficio/pie-agqroo.jpg');
        }
        $lugar = config('services.oficio.lugar', 'Chetumal, Quintana Roo');
        try {
            $fecha = $correspondencia->fecha_oficio
                ? $correspondencia->fecha_oficio->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                : now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        } catch (\Throwable $e) {
            $fecha = ($correspondencia->fecha_oficio ?? now())->format('d/m/Y');
        }
        $firmaNombre = config('services.oficio.firma_nombre');
        $firmaCargo = config('services.oficio.firma_cargo');
    @endphp

    {{-- Fondo pie institucional (imagen 1) --}}
    @if(file_exists($pie))
        <div class="page-footer">
            <img src="{{ $pie }}" alt="">
        </div>
    @endif

    {{-- Encabezado: escudo izq. + logo QROO der. (imagen 2) --}}
    <table class="header">
        <tr>
            <td class="header-left">
                @if(file_exists($escudo))
                    <img src="{{ $escudo }}" alt="Escudo Quintana Roo">
                @endif
            </td>
            <td class="header-right">
                @if(file_exists($headerQroo))
                    <img class="logo-qroo" src="{{ $headerQroo }}" alt="Quintana Roo">
                @endif
                <div class="meta">
                    <div class="entidad">Archivo General del Estado de Quintana Roo.</div>
                    <div class="num">Oficio No. {{ $correspondencia->numero_oficio ?: '————————' }}.</div>
                    <div class="asunto"><strong>ASUNTO:</strong> {{ $correspondencia->asunto }}</div>
                    <div class="fecha">{{ $lugar }} a {{ $fecha }}.</div>
                    @if($correspondencia->leyenda_anio)
                        <div class="leyenda">"{{ $correspondencia->leyenda_anio }}"</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="destinatario">
        @if($correspondencia->destinatario)
            {!! nl2br(e($correspondencia->destinatario)) !!}<br>
            PRESENTE.
        @endif
    </div>

    <div class="cuerpo">
        {!! $correspondencia->contenido !!}
    </div>

    <div class="cierre">
        Sin otro particular por el momento y agradeciendo de antemano su atención al presente, aprovecho la ocasión para enviarle un cordial saludo.
    </div>

    <div class="atentamente">A T E N T A M E N T E</div>

    <div class="firma">
        <div class="nombre">{{ $firmaNombre }}</div>
        <div class="cargo">{{ $firmaCargo }}</div>
    </div>

    <div class="ccp">
        C.c.p.- Archivo.<br>
        @php
            $autor = $correspondencia->usuarioAutor;
            $iniciales = $autor
                ? strtolower(substr($autor->nombre ?? '', 0, 1) . substr($autor->apellido_paterno ?? '', 0, 1) . substr($autor->apellido_materno ?? '', 0, 1))
                : '';
        @endphp
        @if($iniciales !== '')
            DTlyC\{{ $iniciales }}.
        @endif
    </div>

    @if($correspondencia->firma && $correspondencia->firma->estado_firma === 'exitosa')
        <p class="sandbox">
            Documento con firma de prueba (sandbox)
            @if($correspondencia->firma->timestamp_firma)
                — {{ $correspondencia->firma->timestamp_firma->format('Y-m-d H:i') }}
            @endif
        </p>
    @endif
</body>
</html>
