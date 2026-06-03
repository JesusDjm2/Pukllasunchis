@extends($layout ?? 'layouts.superadmin')
@section('titulo', 'Nuevo Ejercicio — ' . $unidad->nombre)
@php $rp = $rp ?? 'ce.cursos'; @endphp
@push('styles')
<style>
.cea-form-page{max-width:760px;padding-bottom:2rem}
.cea-form-header{padding:1.5rem 0 1.25rem;border-bottom:2px solid #f0f0f0;margin-bottom:1.75rem}
.cea-form-header h1{font-size:1.3rem;font-weight:800;color:#2d3561;margin:0 0 .25rem}
.cea-breadcrumb{font-size:.78rem;color:#aaa;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap}
.cea-breadcrumb a{color:#4e73df;text-decoration:none;font-weight:600}
.cea-form-footer{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:.5rem}
.cea-submit-btn{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;border:none;border-radius:.6rem;padding:.65rem 1.5rem;font-weight:700;font-size:.9rem;cursor:pointer;transition:opacity .2s}
.cea-submit-btn:hover{opacity:.88}
.cea-cancel-btn{display:inline-flex;align-items:center;gap:.5rem;background:#f0f0f0;color:#555;border:none;border-radius:.6rem;padding:.65rem 1.2rem;font-weight:600;font-size:.9rem;text-decoration:none;transition:background .15s}
.cea-cancel-btn:hover{background:#e0e0e0;text-decoration:none;color:#333}
</style>
@endpush
@section('contenido')
<div class="container-fluid cea-form-page">
    <div class="cea-form-header">
        <div class="cea-breadcrumb mb-2">
            <a href="{{ route($rp . '.index') }}">Cursos</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <a href="{{ route($rp . '.show', $curso) }}">{{ Str::limit($curso->nombre, 30) }}</a>
            <i class="fas fa-chevron-right fa-xs"></i>
            <span>{{ $nivel->nombre }} › {{ Str::limit($unidad->nombre, 25) }}</span>
        </div>
        <h1><i class="fas fa-tasks mr-2" style="color:#f6c23e"></i>Nuevo Ejercicio</h1>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route($rp . '.ejercicios.store', [$curso, $nivel, $unidad]) }}" method="POST">
        @csrf
        @include('cursos-especiales.admin.ejercicios._form')
        <div class="cea-form-footer">
            <button type="submit" class="cea-submit-btn"><i class="fas fa-save"></i> Guardar Ejercicio</button>
            <a href="{{ route($rp . '.show', $curso) }}" class="cea-cancel-btn"><i class="fas fa-times"></i> Cancelar</a>
        </div>
    </form>
</div>
@endsection
