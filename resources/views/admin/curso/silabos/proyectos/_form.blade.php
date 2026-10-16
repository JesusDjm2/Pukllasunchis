{{--
    Formulario de Proyecto Integrador (crear y editar). Espera: $proyecto (nuevo o existente), $ciclos
    (agrupados por programa), $accion (URL), $metodo ('POST'|'PUT'), $titulo.
--}}
@php
    $elegidos = collect(old('ciclos', $proyecto->exists ? $proyecto->ciclos()->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
@endphp
<form action="{{ $accion }}" method="POST" class="al-card">
    @csrf
    @if ($metodo === 'PUT')
        @method('PUT')
    @endif

    <div class="al-card-cab">
        <span class="al-card-cab-icono"><i class="fas fa-project-diagram"></i></span>
        <div><h2>{{ $titulo }}</h2><p>Los campos con * son obligatorios.</p></div>
    </div>

    <div class="al-card-cuerpo">
        <div class="row">
            <div class="col-lg-6 pi-campo">
                <label for="nombre">Nombre del proyecto *</label>
                <input type="text" id="nombre" name="nombre" maxlength="255" required
                    class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $proyecto->nombre) }}"
                    placeholder="Ej.: Escuela y Territorio: Diagnóstico Socio-Cultural">
                @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6 pi-campo">
                <label for="producto">Producto</label>
                <input type="text" id="producto" name="producto" maxlength="255"
                    class="form-control @error('producto') is-invalid @enderror" value="{{ old('producto', $proyecto->producto) }}"
                    placeholder="Ej.: Mapa parlante y tríptico descriptivo del territorio sagrado…">
                @error('producto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small>Lo que entregan los estudiantes. Se muestra en el sílabo{{ \App\Models\Proyecto::taqeActivo() ? " y en Taq'e" : '' }}.</small>
            </div>
        </div>

        <div class="pi-campo">
            <label for="descripcion">Descripción del proyecto *</label>
            <textarea id="descripcion" name="descripcion" rows="8" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $proyecto->exists ? $proyecto->descripcionHtml() : '') }}</textarea>
            @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small>Texto enriquecido: negritas, listas, enlaces. Se muestra en la Sección III de los sílabos{{ \App\Models\Proyecto::taqeActivo() ? " y a los estudiantes en Taq'e" : '' }}.</small>
        </div>

        <div class="pi-campo mb-0">
            <label>Ciclos que trabajan este proyecto</label>
            <div class="al-aviso is-info mb-2"><i class="fas fa-info-circle"></i>
                <div>Cada ciclo tiene un solo Proyecto Integrador. Si eliges un ciclo que ya tiene otro, <strong>pasa a este</strong>.
                    @if (\App\Models\Proyecto::taqeActivo()) Un alumno solo puede formar grupos Taq'e en el proyecto de su ciclo. @endif</div>
            </div>
            @foreach ($ciclos as $programa => $lista)
                <div class="pi-programa">
                    <div class="pi-programa-titulo">{{ $programa }}</div>
                    <div class="pi-ciclos-opciones">
                        @foreach ($lista as $ciclo)
                            @php
                                $deOtro = $ciclo->proyecto_id && $ciclo->proyecto_id !== $proyecto->id;
                            @endphp
                            <div class="pi-ciclo">
                                <input type="checkbox" name="ciclos[]" value="{{ $ciclo->id }}" id="ciclo-{{ $ciclo->id }}" @checked($elegidos->contains($ciclo->id))>
                                <label for="ciclo-{{ $ciclo->id }}" class="{{ $deOtro ? 'is-otro' : '' }}"
                                    title="{{ $deOtro ? 'Hoy está en: '.$ciclo->proyecto?->nombre : '' }}">
                                    {{ $ciclo->nombre }}
                                    <small>{{ $deOtro ? 'En otro proyecto' : ($ciclo->proyecto_id ? 'En este proyecto' : 'Sin proyecto') }}</small>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @php
            // Rol elegido por docente: lo enviado (si hubo error de validación) o lo guardado.
            $rolesDocentes = collect(old('docentes', $proyecto->exists ? $proyecto->docentes->pluck('pivot.rol', 'id')->all() : []))->filter();
            $docentesOrdenados = $docentes->sortBy(fn ($d) => [$rolesDocentes->has($d->id) ? 0 : 1, $d->nombre]);
        @endphp
        <div class="pi-campo mb-0 mt-3">
            <label for="pi-buscar-docente">Encargados y organizadores</label>
            <div class="al-aviso is-info mb-2"><i class="fas fa-user-tie"></i>
                <div>Además de los docentes de cada curso, estos docentes acompañan y organizan el proyecto.
                    @if (\App\Models\Proyecto::taqeActivo()) Ven todos sus grupos Taq'e, con evidencias y trabajos finales. @endif
                    Deja en <strong>—</strong> a quien no participa.</div>
            </div>
            @error('docentes.*')<div class="al-aviso is-mal mb-2"><i class="fas fa-times-circle"></i><div>{{ $message }}</div></div>@enderror
            <div class="pi-docentes">
                <div class="pi-docentes-cab">
                    <input type="search" id="pi-buscar-docente" class="form-control" placeholder="Buscar docente por nombre o correo…" autocomplete="off">
                    <span class="pi-docentes-cuenta" id="pi-docentes-cuenta">{{ $rolesDocentes->count() }} a cargo</span>
                </div>
                <div class="pi-docentes-lista">
                    @forelse ($docentesOrdenados as $docente)
                        @php $rol = $rolesDocentes->get($docente->id); @endphp
                        <div class="pi-docente {{ $rol ? 'is-activo' : '' }}" data-buscar="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($docente->nombre.' '.$docente->email)) }}">
                            <div class="pi-docente-datos">
                                <strong>{{ $docente->nombre }}</strong>
                                <small>{{ $docente->email }}</small>
                            </div>
                            <select name="docentes[{{ $docente->id }}]" class="form-control form-control-sm" aria-label="Rol de {{ $docente->nombre }}">
                                <option value="">—</option>
                                <option value="encargado" @selected($rol === 'encargado')>Encargado</option>
                                <option value="organizador" @selected($rol === 'organizador')>Organizador</option>
                            </select>
                        </div>
                    @empty
                        <div class="al-vacio"><i class="fas fa-user-slash"></i>No hay docentes registrados.</div>
                    @endforelse
                    <div class="al-vacio d-none" id="pi-docentes-vacio"><i class="fas fa-search"></i>Ningún docente coincide con la búsqueda.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="pi-barra">
        <a href="{{ route('proyectos.index') }}" class="al-btn al-btn-borde">Cancelar</a>
        <button type="submit" class="al-btn al-btn-oro"><i class="fas fa-save"></i> Guardar proyecto</button>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Docentes: filtro por nombre/correo (sin tildes) y contador de quiénes están a cargo.
            var buscar = document.getElementById('pi-buscar-docente');
            var filas = Array.prototype.slice.call(document.querySelectorAll('.pi-docente'));
            var cuenta = document.getElementById('pi-docentes-cuenta');
            var vacio = document.getElementById('pi-docentes-vacio');
            var normalizar = function (t) { return t.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); };
            if (buscar) {
                buscar.addEventListener('input', function () {
                    var q = normalizar(buscar.value.trim()), visibles = 0;
                    filas.forEach(function (f) {
                        var ver = !q || f.dataset.buscar.indexOf(q) !== -1;
                        f.classList.toggle('d-none', !ver);
                        if (ver) visibles++;
                    });
                    vacio.classList.toggle('d-none', visibles > 0);
                });
                // Enter en el buscador no debe enviar el formulario
                buscar.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });
            }
            filas.forEach(function (f) {
                f.querySelector('select').addEventListener('change', function () {
                    f.classList.toggle('is-activo', this.value !== '');
                    cuenta.textContent = filas.filter(function (x) { return x.querySelector('select').value !== ''; }).length + ' a cargo';
                });
            });

            // Sin CKEditor (CDN caído) el textarea sigue funcionando como texto plano.
            if (typeof CKEDITOR === 'undefined' || !document.getElementById('descripcion')) return;
            CKEDITOR.replace('descripcion', {
                height: 260,
                disallowedContent: 'img', // nada de imágenes pegadas en base64 (el servidor igual las quita)
                toolbar: [
                    ['Bold', 'Italic', 'Underline', 'Strike'],
                    ['NumberedList', 'BulletedList', 'Blockquote'],
                    ['Link', 'Unlink'],
                    ['Format'], ['RemoveFormat'], ['Undo', 'Redo'],
                ],
                format_tags: 'p;h3;h4',
            });
        });
    </script>
@endpush
