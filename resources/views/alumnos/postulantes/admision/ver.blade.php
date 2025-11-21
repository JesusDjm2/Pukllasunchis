@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white pt-3 pb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-primary font-weight-bold">
                Postulantes del periodo: {{ $adminFid->nombre }} ({{ $adminFid->anio }})
            </h3>
            <a href="{{ route('admin-fids.index') }}" class="btn btn-secondary btn-sm">Volver</a>
        </div>

        {{-- 🔹 Selector de periodo --}}
        <div class="d-flex justify-content-center flex-wrap mb-4 gap-2">
            @foreach ($periodos as $fid)
                <a href="{{ route('admin-fids.verPostulantes', $fid->id) }}"
                    class="btn {{ $fid->id === $adminFid->id ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                    {{ $fid->nombre }}
                </a>
            @endforeach
        </div>
        @if ($postulantes->isEmpty())
            <div class="alert alert-info">No hay postulantes asociados a este periodo.</div>
        @else
            <div class="row mt-4 d-flex justify-content-center gap-3">
                <div class="col-lg-12">
                    <h4 class="text-primary">
                        Datos demográficos:
                    </h4>
                </div>
                <div class="col-md-2 mb-4">
                    <div class="card shadow-sm p-3">
                        <h5 class="text-center">Beca vs No Beca</h5>
                        <canvas id="becaChart"></canvas>
                    </div>
                </div>

                <div class="col-md-2 mb-4">
                    <div class="card shadow-sm p-3">
                        <h5 class="text-center">Distribución por Género</h5>
                        <canvas id="generoChart"></canvas>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm p-3">
                        <h5 class="text-center">Top 10 Colegios</h5>
                        <canvas id="colegioChart"></canvas>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm p-3">
                        <h5 class="text-center">Lengua 1</h5>
                        <canvas id="lengua1Chart"></canvas>
                    </div>
                </div>

                <div class="col-md-2 mb-4">
                    <div class="card shadow-sm p-3">
                        <h5 class="text-center">Lengua 2</h5>
                        <canvas id="lengua2Chart"></canvas>
                    </div>
                </div>
            </div>


            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-dark text-white sticky-top">
                        <tr>
                            <th>#</th>
                            <th>Apellidos</th>
                            <th>Nombres</th>
                            <th>Email</th>
                            <th>DNI</th>
                            <th>Programa</th>
                            <th>Beca</th>
                            <th>Fecha registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($postulantes as $key => $postulante)
                            <tr
                                @if ($postulante->estudio_beca && strtolower($postulante->estudio_beca) != 'no') class="table-success"
                        @else @endif>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $postulante->apellidos }}</td>
                                <td>{{ $postulante->nombres }}</td>
                                <td>{{ $postulante->email }}</td>
                                <td>{{ $postulante->dni }}</td>
                                <td>{{ $postulante->programa }}</td>
                                <td>
                                    @if ($postulante->estudio_beca && strtolower($postulante->estudio_beca) != 'no')
                                        <span class="badge bg-success text-white">Si</span>
                                    @else
                                        <span class="badge bg-secondary text-white">No</span>
                                    @endif
                                </td>
                                <td>{{ $postulante->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // ==============================
            // 1️⃣ Beca vs No Beca
            // ==============================
            new Chart(document.getElementById('becaChart'), {
                type: 'pie',
                data: {
                    labels: ['Con Beca', 'Sin Beca'],
                    datasets: [{
                        data: [{{ $beca->con_beca }}, {{ $beca->sin_beca }}],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // ==============================
            // 2️⃣ Género
            // ==============================
            new Chart(document.getElementById('generoChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Masculino', 'Femenino'],
                    datasets: [{
                        data: [{{ $genero->masculino }}, {{ $genero->femenino }}],
                        backgroundColor: ['#007bff', '#ff66b2']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // ==============================
            // 3️⃣ Top 5 Colegios
            // ==============================
            new Chart(document.getElementById('colegioChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($colegios->pluck('colegio')) !!},
                    datasets: [{
                        data: {!! json_encode($colegios->pluck('total')) !!},
                        backgroundColor: '#ffc107'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // ==============================
            // 4️⃣ Lengua 1
            // ==============================
            new Chart(document.getElementById('lengua1Chart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($lengua1->pluck('lengua_1')) !!},
                    datasets: [{
                        data: {!! json_encode($lengua1->pluck('total')) !!},
                        backgroundColor: '#17a2b8'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // ==============================
            // 5️⃣ Lengua 2
            // ==============================
            new Chart(document.getElementById('lengua2Chart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($lengua2->pluck('lengua_2')) !!},
                    datasets: [{
                        data: {!! json_encode($lengua2->pluck('total')) !!},
                        backgroundColor: '#6f42c1'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </div>
@endsection
