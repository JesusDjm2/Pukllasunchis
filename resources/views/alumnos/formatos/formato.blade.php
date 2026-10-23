@extends('layouts.alumno')
@section('titulo', 'Guías de TI y Tesis')

@section('contenido')
    @include('alumnos.formatos._guias', ['modalidad' => 'FID'])
@endsection
