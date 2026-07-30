<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Constancia de Reclamo {{ $reclamo->numero_reclamo }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 2px; }
        h2 { font-size: 13px; text-align: center; margin-top: 0; color: #555; font-weight: normal; }
        .cabecera { border-bottom: 2px solid #314e98; padding-bottom: 8px; margin-bottom: 12px; }
        .cabecera table { width: 100%; font-size: 11px; }
        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.datos th, table.datos td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; vertical-align: top; }
        table.datos th { background: #f2f4f9; width: 32%; font-weight: bold; }
        .seccion { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #314e98; margin: 14px 0 6px; border-bottom: 1px solid #314e98; padding-bottom: 3px; }
        .consentimientos li { margin-bottom: 4px; }
        .footer { margin-top: 24px; font-size: 10px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h1>{{ config('libro_reclamaciones.razon_social') }}</h1>
        <h2>Libro de Reclamaciones</h2>
        <table>
            <tr>
                <td>RUC: {{ config('libro_reclamaciones.ruc') }}</td>
                <td>Dirección: {{ config('libro_reclamaciones.direccion') }}</td>
            </tr>
            <tr>
                <td>N° de reclamo: <strong>{{ $reclamo->numero_reclamo }}</strong></td>
                <td>Fecha: {{ $reclamo->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="seccion">Datos del reclamante</div>
    <table class="datos">
        <tr><th>Nombre</th><td>{{ $reclamo->nombre }}</td></tr>
        <tr><th>DNI</th><td>{{ $reclamo->dni }}</td></tr>
        <tr><th>Domicilio</th><td>{{ $reclamo->domicilio }}</td></tr>
        <tr><th>Teléfono</th><td>{{ $reclamo->telefono }}</td></tr>
        <tr><th>Correo</th><td>{{ $reclamo->correo }}</td></tr>
        <tr><th>Condición</th><td>{{ $reclamo->condicion_reclamante }}</td></tr>
    </table>

    @if ($reclamo->programa || $reclamo->ciclo || $reclamo->codigo_estudiante)
        <div class="seccion">Datos académicos</div>
        <table class="datos">
            <tr><th>Programa</th><td>{{ $reclamo->programa ?? '—' }}</td></tr>
            <tr><th>Ciclo</th><td>{{ $reclamo->ciclo ?? '—' }}</td></tr>
            <tr><th>Código de estudiante</th><td>{{ $reclamo->codigo_estudiante ?? '—' }}</td></tr>
        </table>
    @endif

    <div class="seccion">Identificación del servicio</div>
    <table class="datos">
        <tr><th>Tipo de servicio</th><td>{{ $reclamo->tipo_servicio }}</td></tr>
        <tr><th>Descripción del servicio</th><td>{{ $reclamo->descripcion_servicio ?? '—' }}</td></tr>
        <tr><th>Área involucrada</th><td>{{ $reclamo->area_involucrada }}</td></tr>
        <tr><th>Servicio contratado</th><td>{{ $reclamo->servicio_contratado ?? '—' }}</td></tr>
    </table>

    <div class="seccion">{{ $reclamo->tipo_reclamacion }}</div>
    <table class="datos">
        <tr><th>Tipo de reclamación</th><td>{{ $reclamo->tipo_reclamacion }}</td></tr>
        <tr><th>Descripción de los hechos</th><td>{{ $reclamo->descripcion_hechos }}</td></tr>
        <tr><th>Pedido</th><td>{{ $reclamo->pedido }}</td></tr>
        <tr><th>Adjunto</th><td>{{ $reclamo->adjunto ? 'Sí, adjuntado por el reclamante' : 'Ninguno' }}</td></tr>
    </table>

    <div class="seccion">Consentimientos</div>
    <ul class="consentimientos">
        <li>[X] Declaro que la información proporcionada es verdadera.</li>
        <li>[X] Autorizo el tratamiento de mis datos personales para la atención del reclamo.</li>
        <li>[X] Confirmo haber leído la información del Libro de Reclamaciones.</li>
    </ul>

    <div class="footer">
        Documento generado automáticamente el {{ now()->format('d/m/Y H:i') }} — {{ config('libro_reclamaciones.razon_social') }}
    </div>
</body>
</html>
