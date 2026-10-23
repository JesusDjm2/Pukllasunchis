{{-- Integrantes del grupo de titulación (misma RD o misma acta; ver
     GrupoTitulacion). Espera $persona (User dueño de la ficha) y $companeros
     (los demás, con seguimientoTitulacion.estadoTitulacion). Con $paraGestor,
     cada compañero trae un enlace para abrir su propia ficha en el panel.

     Contacto entre integrantes: celular (WhatsApp / llamar) y correo institucional.
     Por protección de datos NO se muestra el DNI ni el número de referencia (es de un
     familiar); esos solo los ve el gestor. --}}
@php
    use App\Support\Telefono;

    $paraGestor = $paraGestor ?? false;
    $integrantesGrupo = collect([$persona])->concat($companeros)->filter()->unique('id');
    $celularDe = function ($u) {
        $ficha = $u->alumno ?? $u->alumnoB;
        return $ficha?->numero ?: $u->telefono;
    };
    $nombreCorto = fn ($u) => trim(strtok((string) $u->name, ' ').' '.strtok((string) $u->apellidos, ' '));
    $saludo = fn ($u) => 'Hola '.strtok((string) $u->name, ' ').', te escribo por nuestro proceso de titulación.';

    // Para "Escribir a todo el grupo" y "Copiar contactos" (sin la propia persona en la vista del alumno).
    $otros = $paraGestor ? $integrantesGrupo : $integrantesGrupo->reject(fn ($u) => $u->id === $persona?->id);
    $correosGrupo = $otros->pluck('email')->filter()->unique()->values();
    $textoContactos = $otros->map(fn ($u) => $u->apellidos.', '.$u->name
        .($celularDe($u) ? ' · Cel. '.$celularDe($u) : '')
        .($u->email ? ' · '.$u->email : ''))->implode("\n");
    $miCelular = $persona ? $celularDe($persona) : null;
@endphp
@if ($companeros->isEmpty())
    <div class="text-muted small py-2">
        <i class="fas fa-user mr-1"></i>
        {{ $paraGestor ? 'Sin grupo registrado' : 'No figuras en un grupo' }}: presenta de forma individual, o el grupo aún no está registrado
        (el grupo se arma al iniciar el proceso, o se reconoce por compartir la misma RD o la misma acta).
    </div>
@else
    <div class="mt-grupo-barra">
        <p class="small text-muted mb-0">Integrantes que iniciaron juntos el proceso o comparten la misma RD o acta de titulación.</p>
        <div class="mt-grupo-barra__acciones">
            @if ($correosGrupo->isNotEmpty())
                <a class="btn btn-sm btn-outline-primary" href="mailto:{{ $correosGrupo->implode(',') }}?subject={{ rawurlencode('Proceso de titulación') }}">
                    <i class="far fa-envelope mr-1"></i>Escribir {{ $paraGestor ? 'al grupo' : 'a todo el grupo' }}
                </a>
            @endif
            @if ($textoContactos !== '')
                <button type="button" class="btn btn-sm btn-outline-secondary js-copiar-contactos" data-texto="{{ $textoContactos }}">
                    <i class="far fa-copy mr-1"></i><span>Copiar contactos</span>
                </button>
            @endif
        </div>
    </div>

    @if (! $paraGestor && ! Telefono::normalizar($miCelular))
        <div class="mt-grupo-aviso">
            <i class="fas fa-mobile-alt"></i>
            <span>Tus compañeros no ven un celular tuyo porque no está registrado en tu ficha (o no es válido). Envíanos tu número con <strong>«¿Algún dato no coincide?»</strong> (al final de esta página) para que puedan escribirte por WhatsApp.</span>
        </div>
    @endif

    <div class="mt-grupo">
        @foreach ($integrantesGrupo as $integrante)
            @php
                $esEl = $integrante->id === $persona?->id;
                $estadoIntegrante = $integrante->seguimientoTitulacion?->estadoTitulacion;
                $celular = $celularDe($integrante);
                $wa = Telefono::whatsapp($celular, $saludo($integrante));
                $tel = Telefono::llamar($celular);
                $programa = $integrante->programa?->nombre;
            @endphp
            <div class="mt-grupo__item {{ $esEl ? 'mt-grupo__item--yo' : '' }}">
                @if ($integrante->foto)
                    <img class="mt-grupo__avatar mt-grupo__avatar--foto" src="{{ asset('img/estudiantes/'.$integrante->foto) }}" alt="" loading="lazy">
                @else
                    <div class="mt-grupo__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($integrante->name, 0, 1).mb_substr($integrante->apellidos, 0, 1)) }}</div>
                @endif
                <div class="mt-grupo__cuerpo">
                    <div class="mt-grupo__cab">
                        <div style="min-width:0;">
                            <div class="font-weight-bold">
                                {{ $integrante->apellidos }}, {{ $integrante->name }}
                                @if ($esEl) <span class="text-muted font-weight-normal">({{ $paraGestor ? 'esta ficha' : 'tú' }})</span> @endif
                            </div>
                            @if ($programa) <div class="small text-muted">{{ $programa }}</div> @endif
                        </div>
                        @if ($estadoIntegrante)
                            <span class="badge badge-{{ \App\Services\Titulacion\AvanceTitulacion::badge($estadoIntegrante->clave) }}">{{ $estadoIntegrante->nombre }}</span>
                        @endif
                    </div>

                    <ul class="mt-grupo__datos">
                        <li>
                            <i class="fas fa-mobile-alt"></i>
                            @if ($celular) <span>{{ $celular }}</span> @else <span class="text-muted">Celular no registrado</span> @endif
                        </li>
                        @if ($integrante->email)
                            <li><i class="far fa-envelope"></i><span class="text-truncate">{{ $integrante->email }}</span></li>
                        @endif
                        @if ($paraGestor)
                            @php $fichaInt = $integrante->alumno ?? $integrante->alumnoB; $expInt = $integrante->seguimientoTitulacion?->numero_expediente; @endphp
                            @if ($fichaInt?->numero_referencia) <li title="Número de referencia (familiar)"><i class="fas fa-phone"></i><span>Ref. {{ $fichaInt->numero_referencia }}</span></li> @endif
                            @if ($expInt) <li><i class="far fa-folder"></i><span>Exp. N° {{ $expInt }}</span></li> @endif
                        @endif
                    </ul>

                    @if (! $esEl || $paraGestor)
                        <div class="mt-grupo__acciones">
                            @if ($wa)
                                <a class="mt-grupo__btn mt-grupo__btn--wa" href="{{ $wa }}" target="_blank" rel="noopener" aria-label="WhatsApp a {{ $nombreCorto($integrante) }}"><i class="fab fa-whatsapp"></i>WhatsApp</a>
                            @endif
                            @if ($tel)
                                <a class="mt-grupo__btn" href="{{ $tel }}" aria-label="Llamar a {{ $nombreCorto($integrante) }}"><i class="fas fa-phone-alt"></i>Llamar</a>
                            @endif
                            @if ($integrante->email)
                                <a class="mt-grupo__btn" href="mailto:{{ $integrante->email }}?subject={{ rawurlencode('Proceso de titulación') }}" aria-label="Correo a {{ $nombreCorto($integrante) }}"><i class="far fa-envelope"></i>Correo</a>
                            @endif
                            @if ($paraGestor && ! $esEl)
                                <a class="mt-grupo__btn" href="{{ route('titulacion.index', array_filter(['search' => $integrante->dni, 'ver' => $integrante->id])) }}"><i class="fas fa-external-link-alt"></i>Abrir su ficha</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @unless ($paraGestor)
        <p class="mt-grupo-privacidad">
            <i class="fas fa-shield-alt"></i>Estos datos solo los ven los integrantes de tu grupo y la Unidad de Investigación, y son para coordinar el proceso de titulación. No se muestran el DNI ni los números de referencia.
        </p>
    @endunless

    @once
        <script>
            // "Copiar contactos": nombre, celular y correo de cada integrante, uno por línea.
            document.addEventListener('click', function (e) {
                var boton = e.target.closest('.js-copiar-contactos');
                if (!boton || !navigator.clipboard) return;
                navigator.clipboard.writeText(boton.dataset.texto).then(function () {
                    var etiqueta = boton.querySelector('span');
                    var antes = etiqueta.textContent;
                    etiqueta.textContent = '¡Copiado!';
                    setTimeout(function () { etiqueta.textContent = antes; }, 1800);
                });
            });
        </script>
    @endonce
@endif
