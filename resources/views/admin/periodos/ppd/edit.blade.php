@extends('layouts.admin')
@section('contenido')
    <div class="container bg-white p-4">
        <h4 class="text-primary font-weight-bold mb-4">Editar Período PPD</h4>

        <form action="{{ route('periodos.admin.ppd.update', $periodoActualPpd) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" class="form-control form-control-sm @error('nombre') is-invalid @enderror"
                    value="{{ old('nombre', $periodoActualPpd->nombre) }}" required>

                @error('nombre')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror
            </div>

            {{-- Calendario --}}
            <div class="form-group">
                <div class="row">
                    <div class="col-10">
                        <label>Calendario (imagen o PDF):</label>
                        <input type="file" name="calendario"
                            class="form-control form-control-sm @error('calendario') is-invalid @enderror">
                        @error('calendario')
                            <span class="text-danger d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-2">
                        @if ($periodoActualPpd->calendario)
                            <label>Calendario actual:</label>
                            <div class="mb-2">
                                @if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $periodoActualPpd->calendario))
                                    <img src="{{ asset($periodoActualPpd->calendario) }}" alt="Calendario"
                                        style="width: 100%;">
                                @else
                                    <a href="{{ asset($periodoActualPpd->calendario) }}" target="_blank"
                                        class="btn btn-info btn-sm">
                                        Ver archivo actual
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                {{-- Fecha Inicio --}}
                <div class="col-6">
                    <label>Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio"
                        class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror"
                        value="{{ old('fecha_inicio', $periodoActualPpd->fecha_inicio ? \Carbon\Carbon::parse($periodoActualPpd->fecha_inicio)->format('Y-m-d') : '') }}">

                    @error('fecha_inicio')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Fecha Cierre --}}
                <div class="col-6">
                    <label>Fecha Cierre:</label>
                    <input type="date" name="fecha_cierre"
                        class="form-control form-control-sm @error('fecha_cierre') is-invalid @enderror"
                        value="{{ old('fecha_cierre', $periodoActualPpd->fecha_cierre ? \Carbon\Carbon::parse($periodoActualPpd->fecha_cierre)->format('Y-m-d') : '') }}">

                    @error('fecha_cierre')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Actual --}}
            <div class="form-check mb-4">
                <input type="hidden" name="actual" value="0">
                <input type="checkbox" name="actual" value="1"
                    class="form-check-input @error('actual') is-invalid @enderror" id="actualCheck"
                    {{ old('actual', $periodoActualPpd->actual) ? 'checked' : '' }}>
                <label class="form-check-label" for="actualCheck">Marcar como período actual</label>
                @error('actual')
                    <span class="text-danger d-block">{{ $message }}</span>
                @enderror
            </div>

            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('periodoactual.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection
