@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('titulo', 'Registros de Comunicados')
@section('contenido')
    <div class="container-fluid bg-white pt-3">
        <div class="d-sm-flex align-items-center justify-content-between mb-3 flex-wrap">
            <h4 class="text-primary font-weight-bold mb-2 mb-sm-0">Registros de comunicados</h4>
            <div class="d-flex align-items-center flex-wrap">
                <span class="badge badge-primary mr-2 mb-1">
                    Total comunicados: {{ $comunicados->count() }}
                </span>
                <a href="{{ route('novedades') }}" class="btn btn-sm btn-info mb-1" target="_blank"
                    rel="noopener noreferrer">
                    Ver página pública de novedades
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white py-2">
                <strong>Nuevo comunicado</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.comunicados.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="titulo">Titulo</label>
                            <input type="text" name="titulo" id="titulo" class="form-control form-control-sm"
                                value="{{ old('titulo') }}" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="fecha_publicacion">Fecha de publicación</label>
                            <input type="date" name="fecha_publicacion" id="fecha_publicacion"
                                class="form-control form-control-sm" value="{{ old('fecha_publicacion', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="archivo">Archivo (Imagen o PDF)</label>
                            <input type="file" name="archivo" id="archivo" class="form-control-file form-control-sm"
                                accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label for="descripcion">Descripción (opcional)</label>
                        <textarea name="descripcion" id="descripcion" class="form-control form-control-sm" rows="3">{{ old('descripcion') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Guardar comunicado</button>
                </form>
            </div>
        </div>

        <form method="get" action="{{ route('admin.comunicados.index') }}" class="form-inline flex-wrap mb-3">
            <label class="mr-2 small font-weight-bold">Año</label>
            <select name="anio" class="form-control form-control-sm mr-3 mb-2">
                <option value="">Todos</option>
                @foreach ($anios as $a)
                    <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
            <label class="mr-2 small font-weight-bold">Mes</label>
            <select name="mes" class="form-control form-control-sm mr-3 mb-2">
                <option value="">Todos</option>
                @foreach ($mesesNombres as $num => $label)
                    <option value="{{ $num }}" {{ (int) request('mes') === $num ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm mb-2">Filtrar</button>
            @if (request()->hasAny(['anio', 'mes']))
                <a href="{{ route('admin.comunicados.index') }}"
                    class="btn btn-outline-secondary btn-sm mb-2 ml-2">Limpiar</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 120px;">Archivo</th>
                        <th>Comunicado</th>
                        <th style="width: 130px;">Fecha</th>
                        <th style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comunicados as $c)
                        <tr>
                            <td class="align-middle">
                                @if ($c->esImagen())
                                    <img src="{{ asset($c->archivo) }}" alt="" class="img-fluid rounded border"
                                        style="max-height: 72px;">
                                @else
                                    <a href="{{ asset($c->archivo) }}" target="_blank" rel="noopener noreferrer"
                                        class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                @endif
                            </td>
                            <td>
                                <h6 class="mb-1">{{ $c->titulo }}</h6>
                                <p class="small mb-0">{{ $c->descripcion ?: 'Sin descripción.' }}</p>
                            </td>
                            <td class="align-middle">
                                <span class="small font-weight-bold text-dark">
                                    {{ $c->fecha_publicacion?->format('d/m/Y') ?? '—' }}
                                </span>
                            </td>
                            <td class="align-middle">
                                <a href="{{ route('admin.comunicados.edit', $c) }}"
                                    class="btn btn-warning btn-sm btn-block mb-2">Editar</a>
                                <form action="{{ route('admin.comunicados.destroy', $c) }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar este comunicado?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-block">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay comunicados registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
