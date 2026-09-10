<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Oficio {{ $correspondencia->numero_oficio ?? $correspondencia->id }}</title>
    <style>
        @page { margin: 28px 36px 40px 36px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.45;
        }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header td { vertical-align: middle; border: none; padding: 4px; }
        .header-logo { width: 22%; text-align: center; }
        .header-logo img { max-width: 95px; max-height: 80px; }
        .header-center { width: 56%; text-align: center; }
        .header-center h1 { font-size: 12px; margin: 0 0 4px; font-weight: bold; text-transform: uppercase; }
        .meta { text-align: right; margin: 12px 0 8px; font-size: 11px; }
        .meta .num { font-weight: bold; }
        .leyenda { text-align: right; font-style: italic; font-size: 10px; margin-bottom: 14px; }
        .destinatario { margin: 16px 0; white-space: pre-line; font-weight: bold; text-transform: uppercase; }
        .cuerpo { margin: 12px 0; text-align: justify; }
        .cuerpo table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        .cuerpo table th, .cuerpo table td { border: 1px solid #333; padding: 4px 6px; font-size: 10px; }
        .cierre { margin-top: 18px; }
        .atentamente { text-align: center; margin: 28px 0 40px; letter-spacing: 2px; font-weight: bold; }
        .firma { text-align: center; }
        .firma .nombre { font-weight: bold; margin-top: 48px; }
        .firma .cargo { font-size: 10px; }
        .ccp { margin-top: 28px; font-size: 9px; }
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; font-size: 9px; color: #666; }
        .footer img { max-height: 36px; }
    </style>
</head>
<body>
    @php
        $escudo = public_path('assets/images/escudo.png');
        $gobierno = public_path('assets/images/gobierno.png');
        $agqroo = public_path('assets/agqroo.png');
        $lugar = config('services.oficio.lugar', 'Chetumal, Quintana Roo');
        $fecha = $correspondencia->fecha_oficio
            ? $correspondencia->fecha_oficio->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
            : now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $firmaNombre = config('services.oficio.firma_nombre');
        $firmaCargo = config('services.oficio.firma_cargo');
    @endphp

    <table class="header">
        <tr>
            <td class="header-logo">
                @if(file_exists($escudo))
                    <img src="{{ $escudo }}" alt="Escudo">
                @endif
            </td>
            <td class="header-center">
                <h1>Archivo General del Estado de Quintana Roo</h1>
            </td>
            <td class="header-logo">
                @if(file_exists($gobierno))
                    <img src="{{ $gobierno }}" alt="Gobierno">
                @endif
            </td>
        </tr>
    </table>

    <div class="meta">
        <div class="num">Oficio No. {{ $correspondencia->numero_oficio ?: '————————' }}.</div>
        <div><strong>ASUNTO:</strong> {{ $correspondencia->asunto }}</div>
        <div>{{ $lugar }} a {{ $fecha }}.</div>
    </div>

    @if($correspondencia->leyenda_anio)
        <div class="leyenda">"{{ $correspondencia->leyenda_anio }}"</div>
    @endif

    <div class="destinatario">
{!! nl2br(e($correspondencia->destinatario ?: '')) !!}
        @if($correspondencia->destinatario)
            <br>PRESENTE.
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
        {{ $correspondencia->usuarioAutor?->email ? strtoupper(substr($correspondencia->usuarioAutor->email, 0, 20)) : '' }}
    </div>

    @if($correspondencia->firma && $correspondencia->firma->estado_firma === 'exitosa')
        <p style="margin-top:16px;font-size:9px;color:#066;">
            Documento con firma de prueba (sandbox)
            @if($correspondencia->firma->timestamp_firma)
                — {{ $correspondencia->firma->timestamp_firma->format('Y-m-d H:i') }}
            @endif
        </p>
    @endif

    <div class="footer">
        @if(file_exists($agqroo))
            <img src="{{ $agqroo }}" alt="AGQROO" style="float:right;">
        @endif
    </div>
</body>
</html>
