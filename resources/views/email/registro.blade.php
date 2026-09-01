<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Matrícula</title>
</head>

<body style="margin:0; padding:0; background-color:#eef1f6; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="width:100%; max-width:600px; background-color:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(16,59,134,0.08);">

                    {{-- Encabezado --}}
                    <tr>
                        <td style="background-color:#ffffff; padding:22px 32px 16px 32px; border-bottom:3px solid #103B86;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="middle" align="left">
                                        <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" alt="EESP Pukllasunchis"
                                            width="150" height="69" style="display:block; width:150px; height:69px; max-width:100%;">
                                    </td>
                                    <td valign="middle" align="right">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="background-color:#e8f6ee; border-radius:20px; padding:5px 12px; white-space:nowrap;">
                                                    <span style="color:#0B954E; font-size:11.5px; font-weight:700; letter-spacing:.03em;">
                                                        &#10003; MATRÍCULA REGISTRADA
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    {{-- Título --}}
                    <tr>
                        <td style="padding:20px 32px 2px 32px;">
                            <h1 style="margin:0 0 4px 0; font-size:19px; color:#1e293b; font-weight:700;">
                                {{ $tituloMensaje }}
                            </h1>
                            <p style="margin:0; font-size:13px; color:#64748b; line-height:1.45;">
                                {{ $subtituloMensaje }}
                            </p>
                        </td>
                    </tr>
                    {{-- Tarjeta de datos --}}
                    <tr>
                        <td style="padding:14px 32px 4px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="border:1px solid #e3e8f0; border-radius:8px; overflow:hidden;">
                                <tr>
                                    <td style="padding:8px 16px; border-bottom:1px solid #eef1f6; background-color:#fafbfd; width:38%;">
                                        <span style="font-size:11.5px; color:#64748b; font-weight:600;">ALUMNO</span>
                                    </td>
                                    <td style="padding:8px 16px; border-bottom:1px solid #eef1f6;">
                                        <span style="font-size:13.5px; color:#1e293b; font-weight:600;">{{ $nombres }} {{ $apellidos }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 16px; border-bottom:1px solid #eef1f6; background-color:#fafbfd;">
                                        <span style="font-size:11.5px; color:#64748b; font-weight:600;">DNI</span>
                                    </td>
                                    <td style="padding:8px 16px; border-bottom:1px solid #eef1f6;">
                                        <span style="font-size:13.5px; color:#1e293b;">{{ $dni }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 16px; border-bottom:1px solid #eef1f6; background-color:#fafbfd;">
                                        <span style="font-size:11.5px; color:#64748b; font-weight:600;">PROGRAMA &middot; CICLO</span>
                                    </td>
                                    <td style="padding:8px 16px; border-bottom:1px solid #eef1f6;">
                                        <span style="font-size:13.5px; color:#1e293b;">{{ $programa }} &middot; {{ $ciclo }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 16px; border-bottom:1px solid #eef1f6; background-color:#fafbfd;">
                                        <span style="font-size:11.5px; color:#64748b; font-weight:600;">PERIODO</span>
                                    </td>
                                    <td style="padding:8px 16px; border-bottom:1px solid #eef1f6;">
                                        <span style="display:inline-block; font-size:12px; color:#103B86; font-weight:700; background-color:#e8edfa; padding:2px 9px; border-radius:12px;">
                                            {{ $periodo ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 16px; background-color:#fafbfd;">
                                        <span style="font-size:11.5px; color:#64748b; font-weight:600;">N&deg; DE COMPROBANTE</span>
                                    </td>
                                    <td style="padding:8px 16px;">
                                        <span style="font-size:13.5px; color:#1e293b; font-weight:600;">{{ $num_comprobante ?? '—' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Pie --}}
                    <tr>
                        <td style="padding:18px 32px 26px 32px;">
                            <p style="margin:0; font-size:11.5px; color:#94a3b8; line-height:1.6; border-top:1px solid #eef1f6; padding-top:14px;">
                                Este es un mensaje automático generado por el sistema de EESP Pukllasunchis el
                                {{ now()->translatedFormat('d \d\e F \d\e Y') }} a las {{ now()->format('H:i') }}.
                                Por favor no respondas a este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
