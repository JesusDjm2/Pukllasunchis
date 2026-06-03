@extends($layout ?? 'layouts.superadmin')
@section('titulo', 'Editar Nivel')
@php $rp = $rp ?? 'ce.cursos'; @endphp
@push('styles')
<style>
.cea-form-page{max-width:560px;padding-bottom:2rem}
.cea-form-header{padding:1.5rem 0 1.25rem;border-bottom:2px solid #f0f0f0;margin-bottom:1.75rem}
.cea-form-header h1{font-size:1.3rem;font-weight:800;color:#2d3561;margin:0}
.cea-field-group{background:#fafbff;border:1px solid #e9ecef;border-radius:.75rem;padding:1.25rem 1.25rem 1rem;margin-bottom:1.25rem}
.cea-field-group-title{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#4e73df;margin-bottom:.85rem;display:flex;align-items:center;gap:.4rem}
.cea-form-footer{display:flex;gap:.75rem}
.cea-submit-btn{display:inline-flex;align-items:center;gap:.5rem;background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;border:none;border-radius:.6rem;padding:.65rem 1.5rem;font-weight:700;font-size:.9rem;cursor:pointer;transition:opacity .2s}
.cea-submit-btn:hover{opacity:.88}
.cea-cancel-btn{display:inline-flex;align-items:center;gap:.5rem;background:#f0f0f0;color:#555;border:none;border-radius:.6rem;padding:.65rem 1.2rem;font-weight:600;font-size:.9rem;text-decoration:none}
.cea-cancel-btn:hover{background:#e0e0e0;text-decoration:none;color:#333}
</style>
@endpush
@section('contenido')
<div class="container-fluid cea-form-page">
    <div class="cea-form-header">
        <h1><i class="fas fa-edit mr-2" style="color:#f6c23e"></i>Editar Nivel</h1>
    </div>
    <form action="{{ route($rp . '.niveles.update', [$curso, $nivel]) }}" method="POST">
        @csrf @method('PUT')
        <div class="cea-field-group">
            <div class="cea-field-group-title"><i class="fas fa-tag"></i> Datos del Nivel</div>
            <div class="form-group">
                <label class="font-weight-bold small">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                       value="{{ old('nombre', $nivel->nombre) }}" required>
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group mb-0">
                <label class="font-weight-bold small">Orden</label>
                <input type="number" name="orden" class="form-control" style="max-width:120px"
                       min="0" value="{{ old('orden', $nivel->orden) }}">
            </div>
        </div>
        <div class="cea-form-footer">
            <button type="submit" class="cea-submit-btn"><i class="fas fa-save"></i> Actualizar</button>
            <a href="{{ route($rp . '.show', $curso) }}" class="cea-cancel-btn"><i class="fas fa-times"></i> Cancelar</a>
        </div>
    </form>
</div>
@endsection
