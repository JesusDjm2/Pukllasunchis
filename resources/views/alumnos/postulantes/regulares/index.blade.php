@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="text-primary font-weight-bold">Postulantes Regulares:</h3>
            {{--  @php
                $conteoProgramas = $postulantes->groupBy('programa')->map->count();
                $totalSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca)->count();
                $conteoBecas = $postulantes->filter(fn($postulante) => $postulante->estudio_beca)->count();
                $conteoProgramas = $postulantes->groupBy('programa')->map->count();
                $postulantesSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca);
                $conteoFiltrado = $postulantesSinBeca->groupBy('programa')->map->count();
            @endphp

            <span class="badge bg-primary text-white p-2">
                Total de registros: {{ $postulantes->count() }}
            </span>


            @foreach ($conteoProgramas as $programa => $cantidad)
                <span class="badge bg-info text-white p-2">{{ $programa }}: {{ $cantidad }}</span>
            @endforeach

            @foreach ($conteoFiltrado as $programa => $cantidad)
                <span class="badge bg-info text-white p-2">{{ $programa }} sin Beca: {{ $cantidad }}</span>
            @endforeach
            <span class="badge bg-warning text-white p-2">Becas: {{ $conteoBecas }}</span>
 --}}
            
            <div class="div">
                <form action="{{ route('postulantes.exportar') }}" method="GET">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm mb-2">
                        Exportar CSV <i class="fa fa-file-csv"></i>
                    </button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                @php
                $conteoProgramas = $postulantes->groupBy('programa')->map->count();
                $totalSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca)->count();
                $conteoBecas = $postulantes->filter(fn($postulante) => $postulante->estudio_beca)->count();
                $postulantesSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca);
                $conteoFiltrado = $postulantesSinBeca->groupBy('programa')->map->count();
                $totalSinBeca = $postulantes->reject(fn($postulante) => $postulante->estudio_beca)->count();

            @endphp

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Programa</th>
                        <th>Total Postulantes</th>
                        <th>Postulantes sin Beca</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($conteoProgramas as $programa => $cantidad)
                        <tr>
                            <td>{{ $programa }}</td>
                            <td>{{ $cantidad }}</td>
                            <td>{{ $conteoFiltrado[$programa] ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>Total de registros</strong></td>
                        <td>{{ $postulantes->count() }}</td>
                        <td>{{ $totalSinBeca }} -  <small>(BECAS: {{ $conteoBecas }} registros)</small></td>
                    </tr>
                </tfoot>
            </table>
            </div>
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>#</th>
                                <th>Postulantes</th>
                                <th>Fecha de Registro</th>
                                <th>Opciones</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($postulantes as $key => $postulante)
                                <tr style="{{ $postulante->estudio_beca ? 'background-color: #d6efe6 !important' : '' }}">
                                    <td>{{ $key + 1 }}</td>
                                    <td><strong>{{ $postulante->apellidos }}, {{ $postulante->nombres }}</strong>
                                        <ul>
                                            <li>DNI: {{ $postulante->dni }}</li>
                                            <li>Programa: {{ $postulante->programa }}</li>
                                            <li>Email: {{ $postulante->email }}</li>
                                            <li>Beca: @if ($postulante->estudio_beca)
                                                    Sí
                                                @else
                                                    No
                                                @endif
                                            </li>
                                            <li>Voucher de pago:
                                                @if ($postulante->voucher_pago)
                                                    <a href="{{ asset($postulante->voucher_pago) }}"
                                                        target="_blank">Ver</a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </li>
                                            <li>Por donde se enteró de la EESPP:
                                                {{ !empty($postulante->contacto) ? $postulante->contacto : 'Sin datos para mostrar' }}
                                            </li>
                                        </ul>
                                    </td>
                                    {{-- <td>{{ $postulante->created_at->format('d/m/Y H:i') }}</td> --}}
                                    <td>{{ $postulante->created_at->timezone('America/Lima')->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('regulares.show', $postulante->id) }}"
                                            class="btn btn-sm btn-info">
                                            Ver
                                        </a>
                                        <a href="{{ route('regulares.edit', $postulante->id) }}"
                                            class="btn btn-sm btn-warning">
                                            Editar
                                        </a>
                                        <form action="{{ route('regulares.destroy', $postulante->id) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Estás seguro de eliminar este postulante?')">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        {{-- <a href="" class="btn btn-sm btn-info">Sin Observaciones</a> --}}
                                        <a href="{{ route('postulante.toggleObservacion', $postulante->id) }}"
                                            class="btn btn-sm {{ $postulante->observaciones ? 'btn-danger' : 'btn-info' }}">
                                            {{ $postulante->observaciones ? 'Con Observaciones' : 'Sin Observaciones' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay postulantes registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
