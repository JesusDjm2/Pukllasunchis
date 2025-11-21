<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "Segoe UI", Roboto, sans-serif;
        }

        .container {
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 15px;
        }

        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        td:first-child {
            font-weight: bold;
            background-color: #f1f5f9;
            width: 35%;
        }

        .section-title {
            font-weight: bold;
            color: #353535;
            margin-top: 25px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 13px;
            color: #555;
        }

        .signatures {
            margin-top: 30px;
            width: 100%;
            text-align: center;
        }

        .signatures td {
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="padding: 25px 15px 0px 15px ">
            <table width="100%" style="margin-bottom: 10px;">
                <tr>
                    <td
                        style="width: 50%; text-align: left; vertical-align: middle; background: transparent; border: none;">
                        <img src="{{ public_path('img/logo-iesp-pukllasunchis.png') }}" alt="Logo EESP" height="80">
                    </td>
                    <td style="width: 50%; text-align: right; vertical-align: middle; border: none;">
                        <span style="font-weight: bold; font-size: 18px; ">
                            {{ $adminFid->nombre }}
                        </span>
                    </td>
                </tr>
            </table>

            <h1 style="text-align: center;  font-size: 16px; margin-top: 1.2em;">
                Constancia de Inscripción N° {{ $postulante->constancia }}
            </h1>

            <p style="color:#3a3f46; font-size: 13px;">Estimado(a) <strong>{{ $postulante->nombres }}
                    {{ $postulante->apellidos }}</strong>,<br>
                Tu inscripción al examen de admisión ha sido <strong>registrada correctamente</strong>.</p>
            <table style="font-size: 13px">
                <tr>
                    <td>Apellido Materno:</td>
                    <td>{{ $postulante->apellidos ?? '' }}</td>
                </tr>
                <tr>
                    <td>Nombres:</td>
                    <td>{{ $postulante->nombres }}</td>
                </tr>
                <tr>
                    <td>Especialidad:</td>
                    <td>{{ $postulante->programa }}</td>
                </tr>
                <tr>
                    <td>Documento de Identidad:</td>
                    <td>{{ $postulante->dni }}</td>
                </tr>
                <tr>
                    <td>Género</td>
                    <td>
                        @if (is_null($postulante->genero))
                            No registrado
                        @elseif ($postulante->genero)
                            Masculino
                        @else
                            Femenino
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Domicilio:</td>
                    <td>{{ $postulante->direccion ?? 'No registrado' }}</td>
                </tr>
                <tr>
                    <td>Teléfono:</td>
                    <td>{{ $postulante->numero ?? 'No registrado' }}</td>
                </tr>
                <tr>
                    <td>Correo electrónico:</td>
                    <td>{{ $postulante->email }}</td>
                </tr>
                <tr>
                    <td>Lugar de Nacimiento:</td>
                    <td>{{ $postulante->departamento_nacimiento ?? '' }},
                        {{ $postulante->provincia_nacimiento ?? '' }},
                        {{ $postulante->distrito_nacimiento ?? '' }}, {{ $postulante->direccion ?? '' }}</td>
                </tr>
                <tr>
                    <td>Fecha de Nacimiento</td>
                    <td>{{ \Carbon\Carbon::parse($postulante->fecha_nacimiento)->translatedFormat('d \d\e F \d\e Y') }}
                    </td>
                </tr>
            </table>

            <p style="margin-top:1em; font-size: 13px;"><strong>Indicaciones:</strong></p>
            <ol style="font-size: 13px">
                <li>Presentarse en la sede de aplicación con 30 minutos de anticipación.</li>
                <li>Portar su documento de identidad (DNI) al ingresar.</li>
                <li>Presentar esta constancia en la Institución al momento de ingresar.</li>
            </ol>

            <p style="font-size:13px"><strong>Sobre los resultados:</strong><br>
                Consulta tus resultados en
                <a
                    href="https://sia.pedagogicos.pe/site/consultaAdmision">https://sia.pedagogicos.pe/site/consultaAdmision</a>
            </p>

            <p style="font-size: 13px">
                Este es el único documento que acredita al postulante como correctamente registrado en el Sistema de
                Información Académica del MINEDU y permite su acceso al local para rendir las pruebas.
            </p>

            <table width="100%" cellpadding="10" cellspacing="0"
                style="margin-top: 60px; text-align: center; position: relative; border:none!important">
                <tbody style="border: none!important;">
                    <tr>                        
                        <td
                            style="width: 50%; vertical-align: bottom; text-align: center; padding: 20px; border: none!important; background:#fff!important">
                            <img src="{{ public_path('img/firma-director.png') }}" alt="Firma Director" width="265px"
                                height="auto" style="margin-bottom: 2em;">

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>
