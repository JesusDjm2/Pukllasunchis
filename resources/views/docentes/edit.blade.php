@extends('layouts.docente')

@section('titulo', 'Editar perfil — Docente')

@section('contenido')
    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Perfil',
            'title' => 'Editar datos',
            'subtitle' => 'Actualice su descripción, foto o contraseña. Nombre, DNI y correo institucional están bloqueados.',
            'backUrl' => route('docente.show', $docente->id),
            'backLabel' => 'Ver perfil',
        ])

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card docente-ui-card">
            <div class="card-body p-3 p-md-4">
                <form action="{{ route('docente.update', $docente->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700" for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $docente->nombre) }}"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700" for="dni">DNI</label>
                        <input type="text" name="dni" id="dni" class="form-control" value="{{ old('dni', $docente->dni) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700" for="email">Correo</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="{{ old('email', $docente->email) }}" readonly>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700" for="foto">Foto (opcional)</label>
                        <input type="file" name="foto" id="foto" class="form-control-file">
                        @if ($docente->foto)
                            <div class="mt-3">
                                <span class="small text-muted d-block mb-1">Vista previa actual</span>
                                <img src="{{ asset('docentes/fotos/' . $docente->foto) }}" alt="Foto actual"
                                    class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-gray-700" for="descripcion">Descripción (opcional)</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="4">{{ old('descripcion', $docente->descripcion) }}</textarea>
                    </div>

                    <hr class="my-4">

                    <p class="small text-muted mb-3">Cambio de contraseña (opcional)</p>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold text-gray-700" for="password">Nueva contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" autocomplete="new-password">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold text-gray-700" for="password_confirmation">Confirmar</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                                placeholder="Repita la contraseña" autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-save mr-1"></i> Guardar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
