@php
    $fotoSrc = $fotoSrc ?? null;
    $logoSrc = $logoSrc ?? null;
    $carnetId = $carnetId ?? 'carnet';
    $cabeceraTitulo = $cabeceraTitulo ?? 'ID Estudiante';
    $outerBackground = $outerBackground ?? '#e8dcc8';
    $primaryBackground = $primaryBackground ?? '#b96328';
    $labelColor = $labelColor ?? '#c41e3a';
@endphp
<div id="{{ $carnetId }}"
    style="width: 246px; margin: 0 auto; font-family: DejaVu Sans, Arial, sans-serif; box-sizing: border-box; background: {{ $outerBackground }}; padding: 8px; border-radius: 4px;">
    <div
        style="background: {{ $primaryBackground }}; border-radius: 6px; overflow: hidden; position: relative; box-shadow: 0 2px 6px rgba(0,0,0,0.15); padding-top: 1em;">
        <div class="carnet-colgador-guia-print" aria-hidden="true"></div>
        <div
            style="position: absolute; inset: 0; z-index: 0; pointer-events: none; opacity: 0.14; background: url('{{ asset('img/aRBOL-BLANCO.png') }}') center center / 120% no-repeat;">
        </div>
        <div
            style="position: absolute; inset: 0; z-index: 1; opacity: 0.06; background: #fff; pointer-events: none;">
        </div>
        <table role="presentation" cellpadding="0" cellspacing="0"
            style="width: 100%; border-collapse: collapse; position: relative; z-index: 2;">
            <tr>
                <td style="width: 52%; padding: 20px 8px 10px 10px; vertical-align: middle;">
                    <div style="background: transparent; padding: 4px 6px; text-align: center; line-height: 0;">
                        @if (!empty($logoSrc))
                            <img src="{{ $logoSrc }}" alt="Logo"
                                style="max-width: 100%; height: 40px; width: auto; display: inline-block; vertical-align: middle;">
                        @else
                            <span style="font-size: 8px; font-weight: bold; color: #fff;">EESP PUKLLASUNCHIS</span>
                        @endif
                    </div>
                </td>
                <td style="width: 10px; padding: 20px 0px 0px 0px; vertical-align: middle; text-align: center;">
                    <div
                        style="display: inline-block; width: 1px; height: 18px; margin: 0 auto; background: rgba(255,255,255,0.55); border-radius: 1px; line-height: 0; font-size: 0;">
                    </div>
                </td>
                <td style="padding: 20px 5px 5px 4px; vertical-align: middle; text-align: center;">
                    <div
                        style="color: #fff; font-weight: bold; font-size: 10px; letter-spacing: 0.06em; line-height: 1.25;">
                        {{ $cabeceraTitulo }}
                    </div>
                </td>
            </tr>
        </table>
        <div style="padding: 0 10px 10px; position: relative; z-index: 2;">
            <div style="background: #fff; border-radius: 10px; padding: 8px 6px 8px; position: relative;">
                <table role="presentation" cellpadding="0" cellspacing="0"
                    style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: top; padding-right: 6px; width: 60%;">
                            <div style="margin-bottom: 8px;">
                                <div style="color: {{ $labelColor }}; font-size: 8px; font-weight: bold;">Programa</div>
                                <div
                                    style="color: #111; font-size: 8px; font-weight: bold; text-transform: uppercase; line-height: 1.2;">
                                    {{ \Illuminate\Support\Str::limit($programaNombre, 48) }}</div>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <div style="color: {{ $labelColor }}; font-size: 8px; font-weight: bold;">Año de Ingreso</div>
                                <div style="color: #111; font-size: 11px; font-weight: bold;">{{ $anioIngreso }}</div>
                            </div>
                            <div>
                                <div style="color: {{ $labelColor }}; font-size: 8px; font-weight: bold;">DNI</div>
                                <div style="color: #111; font-size: 11px; font-weight: bold;">{{ $dni }}</div>
                            </div>
                        </td>
                        <td style="vertical-align: top; width: 40%; text-align: center;">
                            <div
                                style="width: 85px; margin: 0 auto; border-radius: 0px 0px 10px 10px; overflow: hidden; background: #e5e5e5; ">
                                @if (!empty($fotoSrc))
                                    <div style="width: 85px; height: 104px; overflow: hidden;">
                                        <img src="{{ $fotoSrc }}" alt="Foto"
                                            style="display: block; width: 85px; height: auto; min-height: 104px;">
                                    </div>
                                @else
                                    <div
                                        style="width: 85px; height: 104px; line-height: 104px; text-align: center; font-size: 8px; color: #888; box-sizing: border-box;">
                                        Sin foto</div>
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div style="padding: 10px 12px 14px; text-align: center; color: #fff; position: relative; z-index: 2;">
            <div style="margin-bottom: 6px;">
                <div style="font-size: 9px; opacity: 0.9;">Nombres</div>
                <div style="font-size: 14px; font-weight: bold; text-transform: uppercase; line-height: 1.15;">
                    {{ $nombres }}
                </div>
            </div>
            <div style="margin-bottom: 6px;">
                <div style="font-size: 9px; opacity: 0.9;">Apellido Paterno</div>
                <div style="font-size: 14px; font-weight: bold; text-transform: uppercase; line-height: 1.15;">
                    {{ $apellidoPaterno }}
                </div>
            </div>
            <div>
                <div style="font-size: 9px; opacity: 0.9; ">Apellido Materno</div>
                <div style="font-size: 14px; font-weight: bold; text-transform: uppercase; line-height: 1.15;">
                    {{ $apellidoMaterno }}
                </div>
            </div>
        </div>
    </div>
</div>
