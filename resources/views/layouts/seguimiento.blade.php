@extends(auth()->user()->hasRole('docente') && auth()->user()->docente ? 'layouts.docente' : 'layouts.seguimiento_base')
@php($docente = auth()->user()->docente)
