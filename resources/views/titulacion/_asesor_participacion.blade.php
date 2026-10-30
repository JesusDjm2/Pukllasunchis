{{-- En qué procesos participa un asesor, por rol y por trabajo. Cada estudiante abre su ficha en otra pestaña.
     Espera $part (ver ParticipacionAsesores::porAsesor). --}}
@php
    use App\Services\Titulacion\AvanceTitulacion;
    use App\Services\Titulacion\ParticipacionAsesores;
@endphp
<div class="as-rel">
    @foreach (ParticipacionAsesores::ROLES as $rol => [$nombreRol, $paso])
        @continue(empty($part['roles'][$rol]))
        @php $datos = $part['roles'][$rol]; @endphp
        <section class="as-rel__rol">
            <h3 class="as-rel__titulo">{{ $nombreRol }}
                <span>{{ $datos['personas'] }} {{ $datos['personas'] === 1 ? 'estudiante' : 'estudiantes' }} · {{ count($datos['trabajos']) }} {{ count($datos['trabajos']) === 1 ? 'trabajo' : 'trabajos' }}</span>
            </h3>
            <ul class="as-rel__lista">
                @foreach ($datos['trabajos'] as $trabajo)
                    <li class="as-rel__trabajo">
                        <div class="as-rel__nombre {{ $trabajo['titulo'] ? '' : 'is-falta' }}">{{ $trabajo['titulo'] ?: 'Sin título registrado' }}</div>
                        <div class="as-rel__personas">
                            @foreach ($trabajo['personas'] as $s)
                                <a href="{{ route('titulacion.show', [$s->user_id, 'tab' => 'proceso', 'paso' => $paso]) }}" target="_blank" rel="noopener" class="as-rel__persona" title="Abrir la ficha en otra pestaña">
                                    {{ ParticipacionAsesores::nombre($s) }}<i class="fas fa-external-link-alt" aria-hidden="true"></i>
                                </a>
                                @if ($s->estadoTitulacion)
                                    <span class="tl-pill tl-pill--{{ AvanceTitulacion::badge($s->estadoTitulacion->clave) }}">{{ $s->estadoTitulacion->nombre }}</span>
                                @endif
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</div>
