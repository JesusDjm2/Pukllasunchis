@extends('layouts.admin')
@section('titulo', 'Datos Demográficos de Alumnos')
@section('contenido')

    <div class="container-fluid bg-white p-4 shadow-sm rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="font-weight-bold text-primary m-0">
                Datos Demográficos de Estudiantes FID
            </h4>
            <h5 class="text-muted m-0">
                Total de alumnos registrados: <strong>{{ $totalAlumnos }}</strong>
            </h5>
        </div>

        <div style="width: 100%; height: 2px; border-top: 1px dashed #c78d40;"></div>

        <div class="row mt-4">
            <!-- Distribución general -->
            <div class="col-md-2 mb-4">
                <h5 class="text-center text-info">Distribución por Género</h5>
                <canvas id="chartGenero"></canvas>
            </div>

            <div class="col-md-3">
                <h5 class="text-center text-info">Rango de Edades</h5>
                <canvas id="chartEdad"></canvas>
            </div>

            <div class="col-md-3 mb-4">
                <h5 class="text-center text-info">Sector Socioeconómico</h5>
                <canvas id="chartSectorSocio"></canvas>
            </div>

            <!-- Programas y Ciclos -->
            <div class="col-md-4 mb-4">
                <h5 class="text-center text-info">Alumnos por Programa</h5>
                <canvas id="chartPrograma"></canvas>
            </div>

            <div class="col-md-6 mb-4">
                <h5 class="text-center">Alumnos por Ciclo</h5>
                <canvas id="chartCiclo"></canvas>
            </div>

            <!-- Más datos demográficos -->
            <div class="col-md-6 mb-4">
                <h5 class="text-center text-info">Procedencia Familiar</h5>
                <canvas id="chartProcedencia"></canvas>
            </div>

            <div class="col-md-12 mb-4">
                <h5 class="text-center text-info">Sector Laboral</h5>
                <canvas id="chartSectorLaboral"></canvas>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <h5 class="text-center text-info">Autoidentificación ("Te consideras")</h5>
                <canvas id="chartTeConsideras"></canvas>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="text-center text-info">Estado Civil</h5>
                <canvas id="chartEstadoCivil"></canvas>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="text-center text-info">Trabaja Actualmente</h5>
                <canvas id="chartTrabajo"></canvas>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <h5 class="text-center text-info">Quién Mantiene al Alumno</h5>
                <canvas id="chartMantiene"></canvas>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="text-center text-info">Lengua Materna</h5>
                <canvas id="chartLenguas"></canvas>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="text-center text-info">Ingreso Mensual Familiar</h5>
                <canvas id="chartIngreso"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // --- Datos del backend ---
        const edad_18_25 = {{ $edad_18_25 }};
        const edad_26_35 = {{ $edad_26_35 }};
        const programas = @json($porPrograma);
        const ciclos = @json($porCiclo);
        const generos = @json($generos);
        const sectorSocio = @json($sectorSocio);
        const procedencia = @json($procedencia);
        const sectorLaboral = @json($sectorLaboral);
        const teConsideras = @json($teConsideras);
        const lenguas = @json($lenguas);
        const estadoCivil = @json($estadoCivil);
        const quienMantiene = @json($quienMantiene);
        const trabajo = @json($trabajo);
        const ingresoMensual = @json($ingresoMensual);

        // --- Limpiar datos ---
        const limpiarDatos = (obj) => {
            const limpio = {};
            Object.entries(obj || {}).forEach(([key, val]) => {
                const etiqueta = String(key ?? '').trim();
                const valor = Number(val ?? 0);

                if (
                    etiqueta !== '' &&
                    etiqueta.toLowerCase() !== 'undefined' &&
                    etiqueta.toLowerCase() !== 'null' &&
                    etiqueta.toLowerCase() !== '[object object]' &&
                    valor > 0
                ) {
                    limpio[etiqueta] = valor;
                }
            });
            return limpio;
        };

        // --- Función para crear gráficos ---
        const crearGrafico = (id, tipo, etiquetas, datos, colores = null) => {
            const ctx = document.getElementById(id);
            if (!ctx || etiquetas.length === 0) return;

            new Chart(ctx, {
                type: tipo,
                data: {
                    labels: etiquetas,
                    datasets: [{
                        data: datos,
                        backgroundColor: colores ?? [
                            '#4e79a7', '#f28e2b', '#e15759', '#76b7b2',
                            '#59a14f', '#edc949', '#af7aa1', '#ff9da7', '#9c755f'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: tipo === 'bar' ? {
                        y: {
                            beginAtZero: true
                        }
                    } : {}
                }
            });
        };

        // --- Render de gráficos ---
        const dataSets = {
            chartGenero: generos,
            chartPrograma: programas,
            chartCiclo: ciclos,
            chartSectorSocio: sectorSocio,
            chartProcedencia: procedencia,
            chartSectorLaboral: sectorLaboral,
            chartTeConsideras: teConsideras,
            chartLenguas: lenguas,
            chartEstadoCivil: estadoCivil,
            chartMantiene: quienMantiene,
            chartTrabajo: trabajo,
            chartIngreso: ingresoMensual
        };

        Object.entries(dataSets).forEach(([id, data]) => {
            const limpio = limpiarDatos(data);
            const tipo = ['chartGenero', 'chartTeConsideras', 'chartTrabajo'].includes(id) ? 'pie' :
                id === 'chartEstadoCivil' ? 'doughnut' :
                'bar';
            crearGrafico(id, tipo, Object.keys(limpio), Object.values(limpio));
        });

        // --- Rango de edades ---
        new Chart(document.getElementById('chartEdad'), {
            type: 'bar',
            data: {
                labels: ['18 - 25 años', '26 - 35 años'],
                datasets: [{
                    label: 'Cantidad de Estudiantes',
                    data: [edad_18_25, edad_26_35],
                    backgroundColor: ['#59a14f', '#edc949']
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

@endsection
