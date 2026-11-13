@extends('layouts.seguimiento')
@section('titulo','Red Puklla de Egresados · Ayllu Puklla')
@section('contenido')
<link rel="stylesheet" href="{{ asset('css/red-puklla.css') }}">
<div class="rp-red"><header class="rp-hero"><div><small>EESP Pukllasunchis · Ayllu Puklla</small><h1>Red Puklla de Egresados</h1><p>Un espacio para aprender y construir comunidad.</p></div><a class="rp-pill" href="{{ route('red.espacio') }}">{{ auth()->user()->name }} · Mi espacio <span aria-label="Avisos sin leer">({{ auth()->user()->unreadNotifications()->where('type',\App\Notifications\RedPukllaAviso::class)->count() }})</span></a></header>
<nav class="rp-tabs" aria-label="Navegación Red Puklla">@foreach(['inicio'=>'Inicio','comunidad'=>'Comunidad','oportunidades'=>'Oportunidades','espacio'=>'Mi espacio'] as $clave=>$texto)<a class="{{ request()->routeIs('red.'.$clave)?'activo':'' }}" href="{{ route('red.'.$clave) }}">{{ $texto }}</a>@endforeach @if($puedeModerar)<a href="{{ route('red.moderacion') }}">Moderación</a>@endif</nav>
@yield('red-contenido')
<footer class="rp-footer">Crianza mutua, diálogo de saberes y colaboración · Castellano y quechua son bienvenidos. Comparte lo que tienes autorización para difundir.</footer></div>
@endsection
@push('scripts')<script src="{{ asset('js/red-puklla.js') }}" defer></script>@endpush
