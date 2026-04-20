@extends('layouts.docente')

@section('titulo', 'Inicio — Docente')

@section('contenido')
    <div class="container-fluid docente-ui-page">
        <div class="pt-3 mb-4">
            <h1 class="h4 font-weight-bold text-gray-800">Panel docente</h1>
            <p class="text-muted small mb-0">Accesos rápidos a las secciones más usadas.</p>
        </div>

        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6 col-lg-4 mb-3">
                <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}"
                    class="card border-0 shadow-sm h-100 text-decoration-none text-body">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3"
                                style="width:44px;height:44px;"><i class="fas fa-book"></i></span>
                            <div>
                                <h2 class="h6 font-weight-bold text-primary mb-0">Mis cursos</h2>
                                <p class="small text-muted mb-0">FID y PPD, sílabos y Classroom</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <a href="{{ route('calificar', ['id' => $docente->id]) }}"
                    class="card border-0 shadow-sm h-100 text-decoration-none text-body">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3"
                                style="width:44px;height:44px;"><i class="fas fa-pen"></i></span>
                            <div>
                                <h2 class="h6 font-weight-bold text-primary mb-0">Calificaciones</h2>
                                <p class="small text-muted mb-0">Registro y seguimiento</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4 mb-3">
                <a href="{{ route('docente.show', $docente->id) }}"
                    class="card border-0 shadow-sm h-100 text-decoration-none text-body">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center mr-3"
                                style="width:44px;height:44px;"><i class="fas fa-id-card"></i></span>
                            <div>
                                <h2 class="h6 font-weight-bold text-primary mb-0">Mi perfil</h2>
                                <p class="small text-muted mb-0">Datos y descripción pública</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
