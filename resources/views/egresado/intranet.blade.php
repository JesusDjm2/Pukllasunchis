<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mi cuenta — EESPP Pukllasunchis</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800" rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>
<body style="background:#f8f9fc;">
    <div class="container" style="max-width:640px; margin-top:60px; margin-bottom:60px;">
        <div class="text-center mb-4">
            <img src="{{ asset('img/logoiesp.ico') }}" alt="Logo" style="width:48px; height:48px;">
            <h4 class="font-weight-bold mt-2 mb-0">EESPP Pukllasunchis</h4>
            <small class="text-muted">Mi cuenta de egresado</small>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3"
                         style="width:56px; height:56px;">
                        <i class="fas fa-user-graduate fa-lg text-primary"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 font-weight-bold">{{ $user->apellidos }}, {{ $user->name }}</h5>
                        <span class="badge badge-success">Egresado(a)</span>
                    </div>
                </div>

                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width:180px;">Programa</td>
                            <td class="font-weight-bold">{{ $user->programa->nombre ?? 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Correo</td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Estado de titulación</td>
                            <td>
                                @if ($seguimiento)
                                    <span class="badge badge-info">{{ $seguimiento->estadoTitulacion->nombre ?? '—' }}</span>
                                @else
                                    <span class="text-muted">Aún no hay seguimiento registrado</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if ($seguimiento)
            <div class="mt-3">
                @include('titulacion._avance_alumno')
            </div>
        @endif

        <div class="text-center mt-3">
            <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sign-out-alt mr-1"></i>Cerrar sesión
            </a>
        </div>
    </div>
</body>
</html>
