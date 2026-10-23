@extends('layouts.profesionalizacion')
@section('titulo', 'Guías de TI y Tesis')
@section('contenido')
    @include('alumnos.formatos._guias', ['modalidad' => 'PPD'])
@endsection
