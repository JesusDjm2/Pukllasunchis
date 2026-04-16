@php
    $fotoSrc = $fotoSrc ?? null;
    $logoSrc = $logoSrc ?? null;
@endphp
<div id="carnet"
    style="width: 246px; margin: 0 auto; font-family: DejaVu Sans, Arial, sans-serif; box-sizing: border-box; background: #e8dcc8; padding: 8px; border-radius: 4px;">
    <div
        style="background: #b96328; border-radius: 6px; overflow: hidden; position: relative; box-shadow: 0 2px 6px rgba(0,0,0,0.15); padding-top: 1em;">
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
                                style="max-width: 100%; height: 36px; width: auto; display: inline-block; vertical-align: middle;">
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
                        ID Estudiante
                    </div>
                </td>
            </tr>
        </table>
        <div style="padding: 0 10px 10px; position: relative; z-index: 2;">
            <div style="background: #fff; border-radius: 10px; padding: 10px 10px 12px; position: relative;">
                <table role="presentation" cellpadding="0" cellspacing="0"
                    style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: top; padding-right: 8px; width: 58%;">
                            <div style="margin-bottom: 8px;">
                                <div style="color: #c41e3a; font-size: 8px; font-weight: bold;">Programa</div>
                                <div
                                    style="color: #111; font-size: 9px; font-weight: bold; text-transform: uppercase; line-height: 1.2;">
                                    {{ \Illuminate\Support\Str::limit($programaNombre, 48) }}</div>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <div style="color: #c41e3a; font-size: 8px; font-weight: bold;">Año de Ingreso</div>
                                <div style="color: #111; font-size: 11px; font-weight: bold;">{{ $anioIngreso }}</div>
                            </div>
                            <div>
                                <div style="color: #c41e3a; font-size: 8px; font-weight: bold;">DNI</div>
                                <div style="color: #111; font-size: 11px; font-weight: bold;">{{ $dni }}</div>
                            </div>
                        </td>
                        <td style="vertical-align: top; width: 42%; text-align: center;">
                            <div
                                style="width: 88px; margin: 0 auto; border-radius: 0px 0px 10px 10px; overflow: hidden; background: #e5e5e5; ">
                                @if (!empty($fotoSrc))
                                    <img src="{{ $fotoSrc }}" alt="Foto"
                                        style="display: block; width: 88px; height: 104px; object-fit: cover;">
                                @else
                                    <div
                                        style="width: 88px; height: 104px; line-height: 104px; text-align: center; font-size: 8px; color: #888; box-sizing: border-box;">
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
                <div style="font-size: 9px; opacity: 0.9; color: #ffeb50;">Nombres</div>
                <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; line-height: 1.15;">
                    {{ $nombres }}
                </div>
            </div>
            <div style="margin-bottom: 6px;">
                <div style="font-size: 9px; opacity: 0.9; color: #ffeb50;">Apellido Paterno</div>
                <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; line-height: 1.15;">
                    {{ $apellidoPaterno }}
                </div>
            </div>
            <div>
                <div style="font-size: 9px; opacity: 0.9; color: #ffeb50;">Apellido Materno</div>
                <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; line-height: 1.15;">
                    {{ $apellidoMaterno }}
                </div>
            </div>
        </div>
    </div>
</div>
