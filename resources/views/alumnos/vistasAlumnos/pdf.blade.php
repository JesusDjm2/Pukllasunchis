<div style="width: 95%; margin: 0 auto; font-size: 12px;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="width: 100%;">
                <img src="img/logo-iesp-pukllasunchis.png" width="120px" style="float: left;">
            </td>
        </tr>
        <tr>
            <td style="width: 100%; text-align: center; padding-top: 80px; padding-bottom: 22px;">
                <h2 style="color:#000; font-size: 14px; margin: 0;">Ficha de matrícula</h2>
            </td>
        </tr>
    </table>
    <!-- Encabezado -->
    <div style="width: 100%; margin-bottom: 5px;">
        <!-- Tabla de información de la institución -->
        <div class="width: 100%; margin-top: 20px; display: flex">
            <table border="1" style="border-collapse: collapse; width: 100%; color:#000; font-size: 12px;">
                <tr>
                    <td rowspan="2" colspan="2"
                        style="padding: 4px; border-left:2px solid #c78d40; font-weight:bold">
                        Nombre de la institución: Pukllasunchis</td>
                    <td rowspan="2" colspan="3" style="padding: 4px;">Pukllasunchis</td>
                    <td style="padding: 4px; border-left:2px solid #c78d40;font-weight:bold">DRE:</td>
                    <td style="padding: 4px;" colspan="2">Cusco</td>
                </tr>
                <tr>
                    <td style="padding: 4px; border-left:2px solid #c78d40;font-weight:bold">UGEL:</td>
                    <td style="padding: 4px;" colspan="2">Cusco</td>
                </tr>
                <tr>
                    <td style="padding: 4px; border-left:2px solid #c78d40;font-weight:bold">Código modular</td>
                    <td style="padding: 4px;border-left:2px solid #c78d40;font-weight:bold">Denominación</td>
                    <td style="padding: 4px; border-left:2px solid #c78d40;font-weight:bold">Gestión</td>
                    <td style="padding: 4px;border-left:2px solid #c78d40;font-weight:bold">D.S. / R.M. de Creación</td>
                    <td style="padding: 4px; border-left:2px solid #c78d40;font-weight:bold">Dirección</td>
                    <td style="padding: 4px;" colspan="3">Calle 7 diablitos 22 San Blas</td>
                </tr>
                <tr>
                    <td style="padding: 4px;border-left: 2px solid #c78d40">0928655</td>
                    <td style="padding: 4px;border-left: 2px solid #c78d40">EESP</td>
                    <td style="padding: 4px;border-left: 2px solid #c78d40">Privado</td>
                    <td style="padding: 4px;border-left: 2px solid #c78d40">D.S. 003-1994-ED</td>
                    <td style="padding: 4px; border-left:2px solid #c78d40;font-weight:bold">Provincia</td>
                    <td style="padding: 4px;">Cusco</td>
                    <td style="padding: 4px;border-left:2px solid #c78d40;font-weight:bold">Distrito</td>
                    <td style="padding: 4px;">Cusco</td>
                </tr>
            </table>
        </div>
    </div>
    <!-- Información del programa y periodo académico -->
    <table style="width: 100%; margin-bottom: 5px; border: none; font-size: 12px;">
        <tr>
            <!-- Columna 70% -->
            <td style="width: 70%; vertical-align: top;">
                <table border="1" style="border-collapse: collapse; width: 100%; font-size: 10px;">
                    <tr>
                        <td style="padding: 4px; border-left: 2px solid #c78d40; font-weight: bold; width: 30%;">
                            Programa de estudios / Turno
                        </td>
                        <td style="padding: 4px;">
                            {{ $alumno->programa->nombre }} (RVM 163-2019-MINEDU) / Turno: Tarde
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px; border-left: 2px solid #c78d40; font-weight: bold;">
                            Resolución de Autorización
                        </td>
                        <td style="padding: 4px;">RD</td>
                    </tr>
                </table>
            </td>

            <!-- Columna 30% -->
            <td style="width: 30%; vertical-align: top;">
                <table border="1" style="border-collapse: collapse; width: 100%; font-size: 10px;">
                    <tr>
                        <td style="padding: 4px; border-left: 2px solid #c78d40; font-weight: bold; width: 50%;">
                            Periodo Académico
                        </td>
                        <td style="padding: 4px;">2025-I</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px; border-left: 2px solid #c78d40; font-weight: bold;">
                            Ciclo - Sección
                        </td>
                        <td style="padding: 4px;">{{ $alumno->ciclo->nombre }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Información del alumno: 100% ancho -->
    <table border="1" style="border-collapse: collapse; width: 100%; font-size: 12px; margin-bottom: 5px;">
        <tr>
            <td style="padding: 4px; border-left: 2px solid #c78d40; font-weight: bold; width: 25%;">
                Nombres y Apellidos
            </td>
            <td style="padding: 4px; width: 45%;">
                {{ $alumno->apellidos }}, {{ $alumno->nombres }}
            </td>
            <td style="padding: 4px; border-left: 2px solid #c78d40; font-weight: bold; width: 15%;">
                Código de matrícula
            </td>
            <td style="padding: 4px; width: 15%;">
                {{ $alumno->dni }}
            </td>
        </tr>
    </table>
    <!-- Tabla de cursos -->
    <div style="width: 100%; margin-bottom: 5px; margin-top: 20px; ">
        <table border="1" style="border-collapse: collapse; width: 100%; font-size: 12px;">
            <thead>
                <tr>
                    <th style="padding: 4px;border-left: 2px solid #c78d40; width: 5%">N°</th>
                    <th style="padding: 4px;border-left: 2px solid #c78d40; width: 65%">Cursos en los que se matricula
                    </th>
                    <th style="padding: 4px;border-left: 2px solid #c78d40; width: 15%">Horas</th>
                    <th style="padding: 4px;border-left: 2px solid #c78d40; width: 15%">Créditos</th>
                </tr>
            </thead>
            <tbody>
                @if ($alumno->ciclo)
                    @php
                        $totalHoras = 0;
                        $totalCreditos = 0;
                        $cursos = $alumno->cursos->isNotEmpty() ? $alumno->cursos : $alumno->ciclo->cursos;
                    @endphp

                    @foreach ($cursos as $index => $curso)
                        <tr>
                            <td style="padding: 4px; text-align: center;">{{ $index + 1 }}</td>
                            <td style="padding: 4px;">{{ $curso->nombre }}</td>
                            <td style="padding: 4px; text-align: center;">{{ $curso->horas }}</td>
                            <td style="padding: 4px; text-align: center;">{{ $curso->creditos }}</td>
                        </tr>
                        @php
                            $totalHoras += $curso->horas;
                            $totalCreditos += $curso->creditos;
                        @endphp
                    @endforeach

                    <tr>
                        <td colspan="2" style="padding: 4px; text-align: right; font-weight: bold;">Total</td>
                        <td style="padding: 4px; text-align: center; font-weight: bold;">{{ $totalHoras }}</td>
                        <td style="padding: 4px; text-align: center; font-weight: bold;">{{ $totalCreditos }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <!-- Firmas -->
    <table style="width: 100%; margin-top: 120px; font-size: 12px; text-align: center;">
        <tr>
            <td style="width: 50%;">
                <strong>SUÁREZ SÁNCHEZ, RICHARD</strong><br>
                DIRECTOR(A) GENERAL
            </td>
            <td style="width: 50%;">
                <strong>EGUILUZ DUFFY, CECILIA MARIA</strong><br>
                SECRETARIO(A) ACADÉMICO
            </td>
        </tr>
    </table>
</div>
