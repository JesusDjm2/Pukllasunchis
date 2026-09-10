@php $activo = $activo ?? request()->route()->getName(); @endphp
<ul class="nav nav-pills mb-4">
    <li class="nav-item mr-2">
        <a class="nav-link {{ $activo === 'bitacora.index' ? 'active' : '' }}" href="{{ route('bitacora.index') }}">
            <i class="fa fa-history mr-1"></i> Bitácora
        </a>
    </li>
    <li class="nav-item mr-2">
        <a class="nav-link {{ $activo === 'bitacora.activos' ? 'active' : '' }}" href="{{ route('bitacora.activos') }}">
            <i class="fa fa-circle text-success mr-1" style="font-size:9px"></i> Conectados ahora
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activo === 'bitacora.fallidos' ? 'active' : '' }}" href="{{ route('bitacora.fallidos') }}">
            <i class="fa fa-exclamation-triangle mr-1"></i> Intentos fallidos
        </a>
    </li>
</ul>
