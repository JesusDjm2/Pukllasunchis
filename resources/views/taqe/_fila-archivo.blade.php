{{--
    Una fila de archivo o enlace de Taq'e — la MISMA en alumno, docente, Investigador y Super Admin.
    Espera: $trabajo (con subidoPor cargado). Opcionales:
      $rutaVer      name de la ruta {trabajo}/ver (null = sin vista previa ni descarga, p. ej. docente)
      $urlMover     URL POST para pasar al trabajo final / devolver a evidencias (alumnos)
      $urlEliminar  URL DELETE para eliminar
--}}
@php
    $iconos = [
        'imagen' => ['fa-image', '#0e9467'], 'pdf' => ['fa-file-pdf', '#c0392b'], 'word' => ['fa-file-word', '#2563eb'],
        'audio' => ['fa-file-audio', '#8b5cf6'], 'video' => ['fa-file-video', '#d97706'], 'enlace' => ['fa-link', '#0891b2'],
        'otro' => ['fa-file', '#64748b'],
    ];
    [$icono, $color] = $iconos[$trabajo->tipo_archivo] ?? $iconos['otro'];
    $rutaVer = $rutaVer ?? null;
    $esEnlace = $trabajo->esEnlace();
    $host = $esEnlace ? (parse_url($trabajo->enlace, PHP_URL_HOST) ?: $trabajo->enlace) : null;
@endphp
<div class="tq-archivo {{ $trabajo->esFinal() ? 'is-final' : '' }}">
    <span class="tq-archivo-icono" style="--c: {{ $color }};"><i class="fas {{ $icono }}"></i></span>
    <div class="tq-archivo-texto">
        <div class="tq-archivo-nombre">{{ $trabajo->nombre }}</div>
        <small>
            {{ $esEnlace ? 'Enlace · '.preg_replace('/^www\./', '', $host) : ucfirst($trabajo->tipo_archivo) }}
            · {{ optional($trabajo->subidoPor)->nombres }} · {{ $trabajo->created_at->format('d/m/Y') }}
        </small>
    </div>
    <div class="tq-archivo-acciones">
        @if ($esEnlace)
            {{-- El enlace es visible para todos los involucrados, también para el docente --}}
            <a href="{{ $trabajo->enlace }}" target="_blank" rel="noopener noreferrer" title="Vista previa del enlace" class="js-tq-previa"
                data-tipo="enlace" data-nombre="{{ $trabajo->nombre }}"><i class="fas fa-external-link-alt"></i></a>
        @elseif ($rutaVer)
            <a href="{{ route($rutaVer, $trabajo->id) }}" target="_blank" title="Vista previa" class="js-tq-previa"
                data-tipo="{{ $trabajo->tipo_archivo }}" data-nombre="{{ $trabajo->nombre }}"
                data-descargar="{{ route($rutaVer, [$trabajo->id, 'descargar' => 1]) }}"><i class="fas fa-eye"></i></a>
            <a href="{{ route($rutaVer, [$trabajo->id, 'descargar' => 1]) }}" title="Descargar"><i class="fas fa-download"></i></a>
        @endif
        @if (! empty($urlMover))
            <form action="{{ $urlMover }}" method="POST">
                @csrf
                <button type="submit" class="is-mover" title="{{ $trabajo->esFinal() ? 'Devolver a evidencias' : 'Pasar al trabajo final' }}">
                    <i class="fas {{ $trabajo->esFinal() ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                </button>
            </form>
        @endif
        @if (! empty($urlEliminar))
            <form action="{{ $urlEliminar }}" method="POST"
                data-confirm="¿Eliminar &quot;{{ $trabajo->nombre }}&quot;?{{ $esEnlace ? '' : ' También se borra de Drive.' }}" data-confirm-danger>
                @csrf
                @method('DELETE')
                <button type="submit" class="is-peligro" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
            </form>
        @endif
    </div>
</div>
