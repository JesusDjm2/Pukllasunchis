@extends('layouts.formulario-publico')

@section('titulo', 'Buzón de Sugerencias de Tutoría — Pukllasunchis')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Esta página carga Bootstrap 5, que ya no da estilo a .form-group ni a los
           <label> sueltos (eso era Bootstrap 4) — sin esto, los campos quedan
           pegados unos a otros. */
        #sugPublicForm .form-group {
            margin-bottom: 1.35rem;
        }

        #sugPublicForm .form-group.mb-0 {
            margin-bottom: 0;
        }

        #sugPublicForm label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: #5a5c69;
            margin-bottom: .45rem;
        }

        #sugPublicForm .form-control {
            font-size: .85rem;
            padding: .5rem .8rem;
            border: 1px solid #dfe2ec;
            border-radius: .5rem;
            box-shadow: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        #sugPublicForm .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 .15rem rgba(78, 115, 223, .12);
            outline: none;
        }

        #sugPublicForm small {
            font-size: .7rem;
        }
    </style>
@endpush

@section('content')
    <main class="d-flex justify-content-center align-items-start py-4 py-md-5 px-2 fondoLogin2" style="min-height:100vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6">

                    <div class="text-center mb-3">
                        <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" alt="Pukllasunchis" style="max-width:150px;">
                    </div>

                    <div class="card border-0 shadow-lg" style="border-radius:1rem;overflow:hidden;">
                        {{-- Franja superior de color --}}
                        <div style="height:6px;background:linear-gradient(90deg,#1cc88a 0%,#4e73df 100%);"></div>

                        <div class="card-body p-4 p-md-5">

                            {{-- Alertas --}}
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <strong>Por favor corrige los siguientes errores:</strong>
                                    <ul class="mb-0 mt-2 pl-3">
                                        @foreach ($errors->all() as $error)
                                            <li style="font-size:.875rem;">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                </div>
                            @endif

                            <h5 class="font-weight-bold mb-1">Buzón de Sugerencias de Tutoría</h5>
                            <p class="text-muted mb-4" style="font-size:.875rem;">
                                Ayúdanos a mejorar el espacio de tutoría: cuéntanos qué podríamos mejorar o qué temas
                                te gustaría que se traten.
                            </p>

                            <form action="{{ route('sugerencias.public.store') }}" method="POST" id="sugPublicForm">
                                @csrf
                                <input type="hidden" name="ts_form" value="{{ now()->timestamp }}">
                                <div style="position:absolute; left:-9999px; top:-9999px;" aria-hidden="true">
                                    <label for="sitio_web">Sitio web (no llenar)</label>
                                    <input type="text" name="sitio_web" id="sitio_web" tabindex="-1" autocomplete="off">
                                </div>

                                {{-- ── Ciclo ── --}}
                                <div class="mb-4">
                                    <div class="text-uppercase font-weight-bold text-muted mb-3"
                                        style="font-size:.68rem;letter-spacing:.1em;border-bottom:2px solid #eaecf4;padding-bottom:.35rem;">
                                        <i class="fas fa-layer-group mr-1"></i> Tu ciclo
                                    </div>

                                    @if ($cicloPreseleccionado)
                                        <div class="alert alert-light border mb-0 py-2 px-3 d-flex align-items-center" style="font-size:.85rem;">
                                            <i class="fas fa-layer-group mr-2 text-success"></i>
                                            <div>
                                                <strong>{{ optional($cicloPreseleccionado->programa)->nombre }}</strong>
                                                — Ciclo {{ $cicloPreseleccionado->nombre }}
                                                @php $tutoresPresel = $cicloPreseleccionado->tutores->map->nombreCorto()->filter()->implode(', '); @endphp
                                                @if ($tutoresPresel)
                                                    <div class="text-muted mt-1" style="font-size:.78rem;">
                                                        <i class="fas fa-user-tie mr-1"></i>
                                                        {{ \Illuminate\Support\Str::contains($tutoresPresel, ',') ? 'Tutores' : 'Tutor' }} de este ciclo: {{ $tutoresPresel }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="hidden" name="programa_id" id="selPrograma" value="{{ $cicloPreseleccionado->programa_id }}">
                                        <input type="hidden" name="ciclo_id" id="selCiclo" value="{{ $cicloPreseleccionado->id }}">
                                    @else
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="font-weight-bold small">
                                                        Programa <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="programa_id" id="selPrograma"
                                                        class="form-control @error('programa_id') is-invalid @enderror">
                                                        <option value="">— Seleccione un programa —</option>
                                                        @foreach ($programas as $p)
                                                            <option value="{{ $p->id }}"
                                                                {{ old('programa_id') == $p->id ? 'selected' : '' }}>
                                                                {{ $p->nombre }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('programa_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="font-weight-bold small">
                                                        Ciclo <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="ciclo_id" id="selCiclo"
                                                        class="form-control @error('ciclo_id') is-invalid @enderror" disabled>
                                                        <option value="">— Seleccione primero un programa —</option>
                                                    </select>
                                                    @error('ciclo_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div id="tutoresDelCiclo" class="text-muted mt-2 d-none" style="font-size:.78rem;">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            <span id="tutoresDelCicloTexto"></span>
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <div class="text-uppercase font-weight-bold text-muted mb-3"
                                        style="font-size:.68rem;letter-spacing:.1em;border-bottom:2px solid #eaecf4;padding-bottom:.35rem;">
                                        <i class="fas fa-comment-dots mr-1"></i> Tu sugerencia
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold small">
                                            Alumno <span class="text-danger">*</span>
                                        </label>
                                        <select name="alumno_id" id="selAlumno"
                                            class="form-control @error('alumno_id') is-invalid @enderror" disabled>
                                            <option value="">— Seleccione primero un ciclo —</option>
                                        </select>
                                        @error('alumno_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold small">
                                            Mensaje <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="mensaje" rows="5"
                                            class="form-control @error('mensaje') is-invalid @enderror"
                                            placeholder="Escribe tu sugerencia, propuesta de tema o idea de mejora…">{{ old('mensaje') }}</textarea>
                                        @error('mensaje')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Mínimo 10 caracteres.</small>
                                    </div>
                                </div>

                                {{-- Botón enviar --}}
                                <div class="text-center mt-4">
                                    <button type="submit" id="btnEnviarSugerencia" class="btn btn-success btn-lg btn-block px-5"
                                        style="border-radius:2rem;font-weight:600;">
                                        <i class="fas fa-paper-plane mr-2"></i> Enviar sugerencia
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <p class="text-center text-white-50 mt-3" style="font-size:.78rem;">
                        © {{ date('Y') }} Escuela de Educación Superior Pedagógica Pukllasunchis
                    </p>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            @php
                $destinoIntranet = (auth()->check() && auth()->user()->hasRole('alumno'))
                    ? route('alumnos.index')
                    : route('login');
            @endphp
            Swal.fire({
                icon: 'success',
                title: '¡Gracias por tu sugerencia!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#4e73df',
                confirmButtonText: 'Ir al inicio',
                showDenyButton: true,
                denyButtonColor: '#1cc88a',
                denyButtonText: 'Ir al Intranet',
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('index') }}';
                } else if (result.isDenied) {
                    window.location.href = '{{ $destinoIntranet }}';
                }
            });
        @endif

        (function() {
            var selPrograma = document.getElementById('selPrograma');
            var selCiclo = document.getElementById('selCiclo');
            var selAlumno = document.getElementById('selAlumno');
            var urlAlumnos = '{{ route('api.alumnos', ':c') }}';

            function resetSelect(sel, placeholder) {
                sel.innerHTML = '<option value="">' + placeholder + '</option>';
                sel.disabled = true;
            }

            function cargarAlumnos(cicloId) {
                if (!selAlumno) return;
                resetSelect(selAlumno, 'Elige su nombre...');
                if (!cicloId) return;

                fetch(urlAlumnos.replace(':c', cicloId))
                    .then(function(r) { return r.json(); })
                    .then(function(alumnos) {
                        if (alumnos.length === 0) {
                            selAlumno.innerHTML = '<option value="">— Sin alumnos registrados en este ciclo —</option>';
                            return;
                        }
                        selAlumno.innerHTML = '<option value="">Elige su nombre...</option>';
                        alumnos.forEach(function(a) {
                            var opt = document.createElement('option');
                            opt.value = a.id;
                            opt.textContent = a.nombre;
                            selAlumno.appendChild(opt);
                        });
                        selAlumno.disabled = false;
                    });
            }

            if (selPrograma && selPrograma.tagName === 'SELECT') {
                var urlCiclos = '{{ route('api.ciclos', ':p') }}';

                var tutoresBox = document.getElementById('tutoresDelCiclo');
                var tutoresTexto = document.getElementById('tutoresDelCicloTexto');

                function actualizarTutoresDelCiclo() {
                    if (!tutoresBox) return;
                    var opt = selCiclo.options[selCiclo.selectedIndex];
                    var tutores = opt ? (opt.dataset.tutores || '') : '';
                    if (!tutores) {
                        tutoresBox.classList.add('d-none');
                        return;
                    }
                    var esPlural = tutores.indexOf(',') !== -1;
                    tutoresTexto.textContent = (esPlural ? 'Tutores' : 'Tutor') + ' de este ciclo: ' + tutores;
                    tutoresBox.classList.remove('d-none');
                }

                selPrograma.addEventListener('change', function() {
                    resetSelect(selCiclo, '— Seleccione primero un programa —');
                    if (tutoresBox) tutoresBox.classList.add('d-none');
                    cargarAlumnos(null);
                    if (!this.value) return;

                    fetch(urlCiclos.replace(':p', this.value))
                        .then(function(r) { return r.json(); })
                        .then(function(ciclos) {
                            if (ciclos.length === 0) {
                                selCiclo.innerHTML = '<option value="">— Sin ciclos disponibles —</option>';
                                return;
                            }
                            selCiclo.innerHTML = '<option value="">— Seleccione un ciclo —</option>';
                            ciclos.forEach(function(c) {
                                var opt = document.createElement('option');
                                opt.value = c.id;
                                opt.textContent = c.nombre;
                                opt.dataset.tutores = c.tutores || '';
                                selCiclo.appendChild(opt);
                            });
                            selCiclo.disabled = false;
                        });
                });

                if (selCiclo) {
                    selCiclo.addEventListener('change', function() {
                        actualizarTutoresDelCiclo();
                        cargarAlumnos(this.value);
                    });
                }
            } else if (selCiclo && selCiclo.value) {
                // Ciclo preseleccionado (viene del QR): cargar el listado de alumnos de inmediato.
                cargarAlumnos(selCiclo.value);
            }
        })();
    </script>
@endpush
