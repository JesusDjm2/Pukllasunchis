@extends('layouts.admin')
@section('contenido')
<div class="container-fluid bg-white pt-3">
    <h4 class="text-primary">Detalles del Período Actual</h4>

    <p><strong>ID:</strong> {{ $periodoactual->id }}</p>
    <p><strong>Nombre:</strong> {{ $periodoactual->nombre }}</p>
    <p><strong>¿Es actual?:</strong> {{ $periodoactual->actual ? 'Sí' : 'No' }}</p>

    <a href="{{ route('periodoactual.index') }}" class="btn btn-danger">Volver</a>
</div>
@endsection
