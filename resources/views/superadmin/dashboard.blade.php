@extends('layouts.superadmin')
@section('titulo', 'Panel Super Admin')

@section('contenido')
<div class="container-fluid py-3">

    @include('admin.usuarios._encabezado')

    {{-- Alertas --}}
    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @include('admin.usuarios._panel')

</div>{{-- /container --}}
@endsection
