@extends('layouts.seguimiento')
@section('titulo','Mi cuenta de egresado')
@section('contenido')

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
                        @unless ($seguimiento)
                            <tr>
                                <td class="text-muted">Titulación</td>
                                <td><span class="text-muted">Aún no hay seguimiento registrado. Comunícate con la coordinación de titulación.</span></td>
                            </tr>
                        @endunless
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ayllu Puklla — Comunidad Académica e Intercultural --}}
        @if (Route::has('ayllu.inicio'))
            <div class="card shadow-sm mt-3" style="border-left: 4px solid #cd9244; background: #fffcf7;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: .75rem;">
                        <div class="d-flex align-items-center" style="gap: .75rem;">
                            <div style="width: 44px; height: 44px; border-radius: 10px; background: #fef5e7; color: #9c6b37; display: grid; place-items: center; font-size: 1.2rem;">
                                <i class="fas fa-home"></i>
                            </div>
                            <div>
                                <h6 class="font-weight-bold mb-0" style="color: #33445a;">Inicio — Ayllu Puklla</h6>
                                <small class="text-muted d-block">
                                    Un espacio para aprender y construir comunidad en Pukllasunchis.
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('ayllu.inicio') }}" class="btn btn-sm text-white font-weight-bold" style="background: #cd9244; border-radius: 20px; padding: 0.35rem 1rem;">
                                <i class="fas fa-comments mr-1"></i> Entrar a Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Puklla en Red — Seguimiento y Actualización de Egresados --}}
        <div class="card shadow-sm mt-3" style="border-left: 4px solid #832128; background: #fffdfd;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: .75rem;">
                    <div class="d-flex align-items-center" style="gap: .75rem;">
                        <div style="width: 44px; height: 44px; border-radius: 10px; background: #fce8e9; color: #832128; display: grid; place-items: center; font-size: 1.2rem;">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-0" style="color: #832128;">Puklla en Red — Seguimiento de Egresados</h6>
                            <small class="text-muted d-block">
                                Mantén al día tu historia laboral y formativa institucional.
                            </small>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('seguimiento-egresados.index') }}" class="btn btn-sm text-white font-weight-bold" style="background: #832128; border-radius: 20px; padding: 0.35rem 1rem;">
                            <i class="fas fa-edit mr-1"></i> Actualizar Ficha
                        </a>
                    </div>
                </div>
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

@endsection
