@if(auth()->check() && Route::has('red.inicio') && app(\App\Services\RedPuklla\AccesoRed::class)->entrar(auth()->user()))
<li class="nav-item"><a class="nav-link pk-sb-link {{ request()->routeIs('red.*')?'is-activo':'' }}" href="{{ route('red.inicio') }}"><span class="pk-sb-icono"><i class="fas fa-people-carry"></i></span><span class="pk-sb-texto">Red Puklla de Egresados<small>Aprender y construir comunidad</small></span></a></li>
@endif
