{{--
    Formulario para agregar un ARCHIVO o un ENLACE a un grupo Taq'e, como evidencia o directo al trabajo
    final. El selector Archivo/Enlace cambia los campos solo con CSS (:has), sin JavaScript; el servidor
    valida cuál corresponde. Espera: $grupo, $destino ('evidencia'|'final').
--}}
@php $uid = $grupo->id.'-'.$destino; @endphp
<form action="{{ route('taqe.trabajos.store', $grupo->id) }}" method="POST" enctype="multipart/form-data" class="tq-subir">
    @csrf
    <input type="hidden" name="destino" value="{{ $destino }}">
    <div class="tq-modo" role="radiogroup" aria-label="Qué vas a agregar">
        <input type="radio" name="modo" value="archivo" id="modo-archivo-{{ $uid }}" checked>
        <label for="modo-archivo-{{ $uid }}"><i class="fas fa-upload"></i> Archivo</label>
        <input type="radio" name="modo" value="enlace" id="modo-enlace-{{ $uid }}">
        <label for="modo-enlace-{{ $uid }}"><i class="fas fa-link"></i> Enlace</label>
    </div>
    <input type="text" name="nombre" class="form-control form-control-sm" required maxlength="255"
        placeholder="{{ $destino === 'final' ? 'Nombre (p. ej. Informe final, Podcast, Tríptico)' : 'Nombre de la evidencia' }}">
    <input type="file" name="archivo" class="form-control-file tq-campo-archivo" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.mp3,.wav,.m4a,.ogg,.mp4,.webm">
    <input type="url" name="enlace" class="form-control form-control-sm tq-campo-enlace" placeholder="https://… (YouTube, Drive, Canva, Genially…)" maxlength="2048">
    <button type="submit" class="al-btn {{ $destino === 'final' ? 'al-btn-petroleo' : 'al-btn-oro' }} al-btn-sm">
        <i class="fas fa-plus"></i> {{ $destino === 'final' ? 'Agregar al trabajo final' : 'Agregar evidencia' }}
    </button>
    <small class="tq-subir-ayuda tq-campo-archivo"><i class="fas fa-info-circle"></i>
        Imagen, Word o audio MP3/M4A (máx. 10 MB) · PDF (máx. 25 MB) · Video MP4 en 720p, hasta 2 min (máx. 50 MB)</small>
    <small class="tq-subir-ayuda tq-campo-enlace"><i class="fas fa-info-circle"></i>
        Comprueba que el enlace se pueda abrir sin pedir permisos (en Drive: "Cualquier persona con el enlace").</small>
</form>
