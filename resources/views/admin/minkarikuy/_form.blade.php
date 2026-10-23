{{-- Formulario de Mink'arikuy (crear y editar). $minkarikuy: registro (nuevo o existente). --}}
@php $esNuevo = ! $minkarikuy->exists; @endphp

<form action="{{ $esNuevo ? route('admin.minkarikuy.store') : route('admin.minkarikuy.update', $minkarikuy) }}" method="POST">
    @csrf
    @unless ($esNuevo) @method('PUT') @endunless

    <div class="mk-form">
        <article class="al-card">
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas fa-people-carry"></i></span>
                <div><h2>Datos del encuentro</h2><p>Se muestran en la página web y en el afiche del QR.</p></div>
            </div>
            <div class="al-card-cuerpo">
                <div class="mk-campo">
                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre" maxlength="200" required
                        class="form-control @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre', $minkarikuy->nombre) }}"
                        placeholder="Ej.: Conversatorio con Juan Diego López Giraldo">
                    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="mk-campo">
                            <label for="fecha">Fecha <span class="text-danger">*</span></label>
                            <input type="date" id="fecha" name="fecha" required
                                class="form-control @error('fecha') is-invalid @enderror"
                                value="{{ old('fecha', $minkarikuy->fecha?->format('Y-m-d')) }}">
                            @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="mk-campo">
                            <label for="hora">Hora <span class="text-danger">*</span></label>
                            <input type="time" id="hora" name="hora" required
                                class="form-control @error('hora') is-invalid @enderror"
                                value="{{ old('hora', $minkarikuy->hora_texto) }}">
                            @error('hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="mk-campo">
                    <label for="url">Enlace de redirección <span class="text-danger">*</span></label>
                    <input type="url" id="url" name="url" maxlength="500" required
                        class="form-control @error('url') is-invalid @enderror"
                        value="{{ old('url', $minkarikuy->url) }}"
                        placeholder="https://meet.google.com/abc-defg-hij">
                    <small>El código QR se genera con este enlace: quien lo escanee llega aquí. Es también el botón "Ir al enlace" de la página web.</small>
                    @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <label class="mk-publicar" for="activo">
                    <input type="checkbox" id="activo" name="activo" value="1" @checked(old('activo', $minkarikuy->activo))>
                    <span>
                        <strong>Publicar en la página web</strong>
                        <small>Solo se muestra uno a la vez: si otro estaba publicado, se retira automáticamente.</small>
                    </span>
                </label>

                <div class="mk-barra mt-4">
                    <a href="{{ route('admin.minkarikuy.index') }}" class="al-btn al-btn-borde">Cancelar</a>
                    <button type="submit" class="al-btn al-btn-oro"><i class="fas fa-save"></i> {{ $esNuevo ? 'Crear y generar QR' : 'Guardar cambios' }}</button>
                </div>
            </div>
        </article>

        <aside class="al-card mk-vista">
            <div class="al-card-cuerpo">
                @if ($esNuevo)
                    <div class="mk-qr-vacio"><i class="fas fa-qrcode"></i>El QR aparecerá aquí al guardar</div>
                    <p>Se genera solo, a partir del enlace. Luego podrás descargarlo en PNG o imprimir el afiche.</p>
                @else
                    @include('admin.minkarikuy._qr', ['item' => $minkarikuy, 'tamano' => 240])
                    <p>QR actual. Si cambias el enlace, se actualiza al guardar.</p>
                    @if ($minkarikuy->url)
                        <div class="mk-acciones justify-content-center mt-3">
                            <button type="button" class="al-btn al-btn-oro al-btn-sm" data-mk-descargar="{{ $minkarikuy->id }}"><i class="fas fa-download"></i> PNG</button>
                            <a href="{{ route('admin.minkarikuy.qr', $minkarikuy) }}" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-print"></i> Afiche</a>
                        </div>
                    @endif
                @endif
            </div>
        </aside>
    </div>
</form>
