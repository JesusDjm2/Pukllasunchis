@extends('layouts.admin')
@section('contenido')
    <style>
        .imprimir {
            font-size: 12px;
            position: fixed;
            right: 20px;
            bottom: 20px;
            padding: 25px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            border: none;
            transition: 0.2s ease;
        }

        .imprimir:hover {
            color: #fff;
            font-size: 13px;
            transition: 0.2s ease;
        }

        @media print {
            @page {
                margin: 0;
                size: auto;
            }

            body {
                margin: 1cm;
            }

            header,
            footer,
            nav,
            #accordionSidebar,
            .btn {
                display: none;
            }

            body::before,
            body::after {
                display: none !important;
            }

        }
    </style>
    <div style="width: 95%; margin: 1em;">
        <div style="width: 100%; ">
            <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" width="200px"> <span style="float: right;"><a
                    href="javascript:history.back()" class="btn btn-sm btn-primary">Volver</a></span>
            <h2 style="text-align: center; color:#000; margin-top:1em; margin-bottom: 0.5em">Ficha de matrícula</h2>
            <table border="1" style="border-collapse: collapse; width: 100%; color:#000">
                <tr>
                    <td rowspan="2" colspan="2" style="padding: 8px; border-left:3px solid #c78d40; font-weight:bold">
                        Nombre de la
                        institución: Pukllasunchis</td>
                    <td rowspan="2" colspan="3" style="padding: 8px;">Pukllasunchis</td>
                    <td style="padding: 8px; border-left:3px solid #c78d40;font-weight:bold">DRE:</td>
                    <td style="padding: 8px;" colspan="2">Cusco</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-left:3px solid #c78d40;font-weight:bold">UGEL:</td>
                    <td style="padding: 8px;" colspan="2">Cusco</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-left:3px solid #c78d40;font-weight:bold">Código modular</td>
                    <td style="padding: 8px;border-left:3px solid #c78d40;font-weight:bold">Denominación</td>
                    <td style="padding: 8px; border-left:3px solid #c78d40;font-weight:bold">Gestión</td>
                    <td style="padding: 8px;border-left:3px solid #c78d40;font-weight:bold">D.S. / R.M. de Creación y R.D.
                        de
                        Revalidación </td>
                    <td style="padding: 8px; border-left:3px solid #c78d40;font-weight:bold">Dirección</td>
                    <td style="padding: 8px;" colspan="3">Calle 7 diablitos 22 San Blas</td>
                </tr>
                <tr>
                    <td style="padding: 8px;border-left: 3px solid #c78d40">0928655</td>
                    <td style="padding: 8px;border-left: 3px solid #c78d40">EESP</td>
                    <td style="padding: 8px;border-left: 3px solid #c78d40">Privado</td>
                    <td style="padding: 8px;border-left: 3px solid #c78d40">D.S. 003-1994-ED</td>
                    <td style="padding: 8px; border-left:3px solid #c78d40;font-weight:bold">Provincia</td>
                    <td style="padding: 8px;">Cusco</td>
                    <td style="padding: 8px;border-left:3px solid #c78d40;font-weight:bold">Distrito</td>
                    <td style="padding: 8px;">Cusco</td>
                </tr>
            </table>
        </div>
        <div style="width: 100%; display: flex;">
            <!-- Primera columna con el 75% del ancho -->
            <div style="width: 75%; margin-top:1em; color:#000; padding-right:1em">
                <table border="1" style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="padding: 8px; border-left: 3px solid #c78d40;font-weight:bold">Programa de estudios /
                            Turno</td>
                        <td style="padding: 8px;">{{ $alumno->programa->nombre }} (RVM 163-2019-MINEDU) / Turno: Tarde </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px;border-left: 3px solid #c78d40;font-weight:bold">Resolución de Autorización
                        </td>
                        <td style="padding: 8px;">RD</td>
                    </tr>
                </table>
            </div>

            <!-- Segunda columna con el 25% del ancho -->
            <div style="width: 25%;margin-top:1em; color:#000">
                <table border="1" style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="padding: 8px;border-left: 3px solid #c78d40;font-weight:bold">Periodo Académico</td>
                        <td style="padding: 8px;">2024-II </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px;border-left: 3px solid #c78d40;font-weight:bold">Ciclo - Sección</td>
                        <td style="padding: 8px;">{{ $alumno->ciclo->nombre }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div style="width: 100%; display: flex;">
            <!-- Primera columna con el 75% del ancho -->
            <div style="width: 65%; margin-top:1em; color:#000; padding-right:1em">
                <table border="1" style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="padding: 8px; border-left: 3px solid #c78d40;font-weight:bold">Nombres y Apellidos </td>
                        <td style="padding: 8px;">{{ $alumno->apellidos }}, {{ $alumno->nombres }}</td>
                    </tr>
                </table>
            </div>

            <div style="width: 35%;margin-top:1em; color:#000">
                <table border="1" style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td style="padding: 8px;border-left: 3px solid #c78d40;font-weight:bold">Código de matrícula</td>
                        <td style="padding: 8px;">{{ $alumno->dni }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div style="width: 100%; display: flex;margin-top:1em; color:#000;">
            <table border="1" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th style="padding: 8px;border-left: 3px solid #c78d40">N°</th>
                        <th style="padding: 8px;border-left: 3px solid #c78d40">Cursos en los que se matricula</th>
                        <th style="padding: 8px;border-left: 3px solid #c78d40">Horas</th>
                        <th style="padding: 8px;border-left: 3px solid #c78d40">Créditos</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($alumno->ciclo)
                        @php
                            $totalHoras = 0;
                            $totalCreditos = 0;
                        @endphp
                        @foreach ($alumno->ciclo->cursos as $index => $curso)
                            <tr>
                                <td style="padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                                <td style="padding: 8px;">{{ $curso->nombre }}</td>
                                <td style="padding: 8px; text-align: center;">{{ $curso->horas }}</td>
                                <td style="padding: 8px; text-align: center;">{{ $curso->creditos }}</td>
                            </tr>
                            @php
                                $totalHoras += $curso->horas;
                                $totalCreditos += $curso->creditos;
                            @endphp
                        @endforeach
                        <tr>
                            <td colspan="2" style="padding: 8px; text-align: right; font-weight: bold;">Total</td>
                            <td style="padding: 8px; text-align: center; font-weight: bold;">{{ $totalHoras }}</td>
                            <td style="padding: 8px; text-align: center; font-weight: bold;">{{ $totalCreditos }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div style="width: 100%; display: flex;margin-top:6em; color:#000;">
            <div style="text-align: center; width: 50%; ">
                <strong> SUÁREZ SÁNCHEZ, RICHARD </strong><br> DIRECTOR(A) GENERAL
            </div>
            <!-- Right content -->
            <div style="text-align: center; width: 50%;">
                <strong>EGUILUZ DUFFY, CECILIA MARIA</strong> <br> SECRETARIO(A) ACADÉMICO
            </div>
        </div>
    </div>
    <button class="imprimir" onclick="window.print()">
        Guardar /<br> Imprimir
    </button>
@endsection
