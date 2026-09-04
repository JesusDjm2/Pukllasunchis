@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
    <style>
        :root {
            --cf-primary: #2e5c8a;
            --cf-primary-dark: #1e3a5f;
            --cf-primary-light: #e7f0fa;
            --cf-ink: #1e293b;
            --cf-muted: #64748b;
            --cf-border: #dbe4f0;
            --cf-bg: #f8fafc;
        }

        .cf-page {
            background: var(--cf-bg);
            border-radius: 18px;
            padding: 1.5rem;
            margin: 1rem;
        }

        @media (min-width: 992px) {
            .cf-page {
                padding: 2rem;
                margin: 1.5rem;
            }
        }

        .cf-hero {
            background: linear-gradient(135deg, var(--cf-primary-dark), var(--cf-primary) 60%, #4e73df);
            border-radius: 18px;
            padding: 1.75rem 1.75rem;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 14px 30px -12px rgba(30, 58, 95, .4);
            margin-bottom: 1.75rem;
        }

        .cf-hero-kicker {
            text-transform: uppercase;
            letter-spacing: .09em;
            font-size: .7rem;
            font-weight: 700;
            opacity: .8;
            margin-bottom: .25rem;
        }

        .cf-hero h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -.01em;
        }

        .cf-hero p {
            margin: .35rem 0 0;
            font-size: .87rem;
            opacity: .85;
            max-width: 46ch;
        }

        .cf-hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .16);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .cf-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 999px;
            padding: .45rem 1rem;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }

        .cf-back:hover {
            background: rgba(255, 255, 255, .26);
            color: #fff;
        }

        .cf-errors {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            border-radius: 10px;
            padding: .9rem 1.1rem;
            margin-bottom: 1.5rem;
            color: #991b1b;
            font-size: .85rem;
        }

        .cf-errors strong {
            display: block;
            margin-bottom: .3rem;
        }

        .cf-errors ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        .cf-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.25rem;
        }

        .cf-col-left {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            min-width: 0;
        }

        @media (min-width: 1100px) {
            .cf-layout {
                grid-template-columns: 1.15fr .85fr;
                align-items: start;
            }

            .cf-card--competencias {
                position: sticky;
                top: 1rem;
            }
        }

        .cf-card {
            background: #fff;
            border: 1px solid var(--cf-border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px -4px rgba(30, 58, 95, .05);
        }

        .cf-card-head {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-bottom: 1.25rem;
        }

        .cf-card-num {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: var(--cf-primary-light);
            color: var(--cf-primary);
            font-weight: 800;
            font-size: .85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cf-card-head h2 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--cf-ink);
            margin: 0;
        }

        .cf-card-head span {
            display: block;
            font-size: .78rem;
            color: var(--cf-muted);
            font-weight: 400;
        }

        .cf-field {
            margin-bottom: 1.1rem;
        }

        .cf-field:last-child {
            margin-bottom: 0;
        }

        .cf-field label {
            font-size: .82rem;
            font-weight: 600;
            color: var(--cf-ink);
            margin-bottom: .35rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .cf-field label i {
            color: var(--cf-primary);
            font-size: .78rem;
            width: 14px;
        }

        .cf-hint {
            font-size: .74rem;
            color: var(--cf-muted);
            margin-top: .3rem;
        }

        .cf-control {
            border: 1.5px solid var(--cf-border);
            border-radius: 10px;
            padding: .55rem .8rem;
            font-size: .88rem;
            width: 100%;
            transition: border-color .15s, box-shadow .15s;
            background: #fff;
        }

        .cf-control:focus {
            outline: none;
            border-color: var(--cf-primary);
            box-shadow: 0 0 0 3px rgba(46, 92, 138, .15);
        }

        .cf-control.is-invalid {
            border-color: #dc2626;
        }

        .cf-invalid-feedback {
            color: #dc2626;
            font-size: .76rem;
            margin-top: .3rem;
        }

        .cf-row2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.1rem;
        }

        @media (min-width: 640px) {
            .cf-row2 {
                grid-template-columns: 1fr 1fr;
            }
        }

        .cf-row3 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.1rem;
        }

        @media (min-width: 640px) {
            .cf-row3 {
                grid-template-columns: 1.3fr 1fr 1fr;
            }
        }

        .cf-counter {
            font-size: .72rem;
            color: var(--cf-muted);
            text-align: right;
            margin-top: .25rem;
        }

        /* ── Aviso Extracurricular ── */
        .cf-extra-note {
            display: none;
            align-items: flex-start;
            gap: .55rem;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 10px;
            padding: .65rem .85rem;
            font-size: .78rem;
            margin-top: .9rem;
        }

        .cf-extra-note i {
            margin-top: .1rem;
        }

        .cf-horas-wrap {
            overflow: hidden;
        }

        /* ── Competencias ── */
        .cf-comp-toolbar {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: .9rem;
            flex-wrap: wrap;
        }

        .cf-comp-search {
            position: relative;
            flex: 1;
            min-width: 180px;
        }

        .cf-comp-search i {
            position: absolute;
            left: .75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--cf-muted);
            font-size: .78rem;
        }

        .cf-comp-search input {
            padding-left: 2.1rem;
        }

        .cf-comp-mini-btn {
            border: 1.5px solid var(--cf-border);
            background: #fff;
            color: var(--cf-primary);
            font-size: .76rem;
            font-weight: 600;
            border-radius: 999px;
            padding: .4rem .85rem;
            cursor: pointer;
            transition: background .15s, border-color .15s;
            white-space: nowrap;
        }

        .cf-comp-mini-btn:hover {
            background: var(--cf-primary-light);
            border-color: var(--cf-primary);
        }

        .cf-comp-count {
            font-size: .76rem;
            font-weight: 700;
            color: var(--cf-primary);
            white-space: nowrap;
        }

        .cf-comp-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: .4rem;
            padding: .1rem;
        }

        @media (min-width: 480px) {
            .cf-comp-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .cf-comp-card {
            display: flex;
            align-items: center;
            gap: .45rem;
            border: 1.5px solid var(--cf-border);
            border-radius: 8px;
            padding: .32rem .55rem;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            font-size: .77rem;
            line-height: 1.25;
            color: var(--cf-ink);
        }

        .cf-comp-card:hover {
            border-color: #a8c5e3;
            background: #f5f9fd;
        }

        .cf-comp-card.is-checked {
            border-color: var(--cf-primary);
            background: var(--cf-primary-light);
        }

        .cf-comp-card input {
            margin-top: .18rem;
            accent-color: var(--cf-primary);
            flex-shrink: 0;
        }

        .cf-comp-empty {
            grid-column: 1 / -1;
            text-align: center;
            color: var(--cf-muted);
            font-size: .82rem;
            padding: 1.5rem 0;
        }

        /* ── Barra de acciones ── */
        .cf-actions {
            position: sticky;
            bottom: 1rem;
            display: flex;
            justify-content: flex-end;
            gap: .6rem;
            background: #fff;
            border: 1px solid var(--cf-border);
            border-radius: 14px;
            padding: .75rem 1rem;
            box-shadow: 0 10px 30px -10px rgba(30, 58, 95, .2);
        }

        .cf-btn {
            border-radius: 10px;
            padding: .55rem 1.3rem;
            font-size: .85rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            text-decoration: none;
        }

        .cf-btn-ghost {
            background: #f4f2fb;
            color: var(--cf-ink);
        }

        .cf-btn-ghost:hover {
            background: #ece8fa;
            color: var(--cf-ink);
        }

        .cf-btn-primary {
            background: linear-gradient(135deg, var(--cf-primary-dark), var(--cf-primary));
            color: #fff;
            box-shadow: 0 8px 18px -6px rgba(30, 58, 95, .45);
        }

        .cf-btn-primary:hover {
            filter: brightness(1.06);
        }

        .cf-btn-primary:disabled {
            opacity: .7;
            cursor: not-allowed;
        }

        @media (prefers-reduced-motion: reduce) {
            .cf-card, .cf-hero {
                transition: none !important;
            }
        }
    </style>

    <div class="cf-page">
        <div class="cf-hero" id="cfHero">
            <div class="d-flex align-items-center" style="gap: .9rem;">
                <div class="cf-hero-icon"><i class="fas fa-book-open"></i></div>
                <div>
                    <div class="cf-hero-kicker">Cursos</div>
                    <h1>Crear nuevo curso</h1>
                    <p>Regístralo, asígnalo a un programa/ciclo y define sus competencias.</p>
                </div>
            </div>
            <a href="{{ route('curso.index') }}" class="cf-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        @if ($errors->any())
            <div class="cf-errors">
                <strong><i class="fas fa-triangle-exclamation mr-1"></i>Revisa estos campos:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('curso.store') }}" method="POST" autocomplete="off" id="cfForm">
            @csrf
            <div class="cf-layout">
              <div class="cf-col-left">
                <div class="cf-card">
                    <div class="cf-card-head">
                        <div class="cf-card-num">1</div>
                        <div>
                            <h2>Ubicación académica</h2>
                            <span>¿En qué programa y ciclo se dicta?</span>
                        </div>
                    </div>
                    <div class="cf-row2">
                        <div class="cf-field">
                            <label for="programa_id"><i class="fas fa-graduation-cap"></i>Programa</label>
                            <select id="programa_id" name="programa_id" class="cf-control" required>
                                <option value="" selected disabled>Elegir programa…</option>
                                @foreach ($programas as $programa)
                                    <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="cf-field">
                            <label for="ciclo_id"><i class="fas fa-layer-group"></i>Ciclo</label>
                            <select id="ciclo_id" name="ciclo_id" class="cf-control" required>
                                <option value="" selected disabled>Elige un programa primero…</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="cf-card">
                    <div class="cf-card-head">
                        <div class="cf-card-num">2</div>
                        <div>
                            <h2>Datos del curso</h2>
                            <span>Nombre, descripción y carga académica</span>
                        </div>
                    </div>
                    <div class="cf-field">
                        <label for="nombre"><i class="fas fa-signature"></i>Nombre del curso</label>
                        <input type="text" name="nombre" id="nombre"
                            class="cf-control @error('nombre') is-invalid @enderror"
                            value="{{ old('nombre') }}" placeholder="Ej. Didáctica de la Matemática" required>
                        @error('nombre')
                            <div class="cf-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="cf-field">
                        <label for="sumilla"><i class="fas fa-align-left"></i>Sumilla</label>
                        <textarea name="sumilla" id="sumilla" maxlength="1000"
                            class="cf-control @error('sumilla') is-invalid @enderror"
                            rows="4" placeholder="Describe brevemente el propósito y alcance del curso…">{{ old('sumilla') }}</textarea>
                        <div class="cf-counter"><span id="sumillaCount">0</span>/1000</div>
                        @error('sumilla')
                            <div class="cf-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="cf-field">
                        <label for="cc"><i class="fas fa-shapes"></i>Componente curricular</label>
                        <select name="cc" id="cc" class="cf-control @error('cc') is-invalid @enderror" required>
                            <option value="" selected disabled>Selecciona una opción</option>
                            <option value="Formacion General" {{ old('cc') == 'Formacion General' ? 'selected' : '' }}>Formación General</option>
                            <option value="Formacion Específica" {{ old('cc') == 'Formacion Específica' ? 'selected' : '' }}>Formación Específica</option>
                            <option value="Formacion Práctica e Investigación" {{ old('cc') == 'Formacion Práctica e Investigación' ? 'selected' : '' }}>Formación Práctica e Investigación</option>
                            <option value="Electivo" {{ old('cc') == 'Electivo' ? 'selected' : '' }}>Electivo</option>
                            @role('super-admin')
                            <option value="Extracurricular" {{ old('cc') == 'Extracurricular' ? 'selected' : '' }}>Extracurricular</option>
                            @endrole
                        </select>
                        @error('cc')
                            <div class="cf-invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="cf-extra-note" id="cfExtraNote">
                            <i class="fas fa-circle-info"></i>
                            <span>Los cursos <strong>Extracurriculares</strong> no requieren horas ni créditos — puedes dejarlos en blanco.</span>
                        </div>
                    </div>
                    <div class="cf-horas-wrap" id="cfHorasWrap">
                        <div class="cf-row2" style="margin-top: 1.1rem;">
                            <div class="cf-field">
                                <label for="horas"><i class="fas fa-clock"></i>Horas</label>
                                <input type="text" name="horas" id="horas"
                                    class="cf-control @error('horas') is-invalid @enderror"
                                    value="{{ old('horas') }}" placeholder="Ej. 4">
                                @error('horas')
                                    <div class="cf-invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="cf-field">
                                <label for="creditos"><i class="fas fa-star"></i>Créditos</label>
                                <input type="text" name="creditos" id="creditos"
                                    class="cf-control @error('creditos') is-invalid @enderror"
                                    value="{{ old('creditos') }}" placeholder="Ej. 3">
                                @error('creditos')
                                    <div class="cf-invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
              </div>

                <div class="cf-card cf-card--competencias">
                    <div class="cf-card-head">
                        <div class="cf-card-num">3</div>
                        <div>
                            <h2>Competencias a evaluar</h2>
                            <span>Marca las que se calificarán en este curso</span>
                        </div>
                    </div>
                    <div class="cf-comp-toolbar">
                        <div class="cf-comp-search">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" id="cfCompSearch" class="cf-control" placeholder="Buscar competencia…">
                        </div>
                        <button type="button" class="cf-comp-mini-btn" id="cfCompAll">Marcar todas</button>
                        <button type="button" class="cf-comp-mini-btn" id="cfCompNone">Quitar todas</button>
                        <span class="cf-comp-count"><span id="cfCompCount">0</span> seleccionadas</span>
                    </div>
                    <div class="cf-comp-grid" id="cfCompGrid">
                        @forelse ($competencias as $competencia)
                            <label class="cf-comp-card" data-comp-nombre="{{ strtolower($competencia->nombre) }}">
                                <input type="checkbox" name="competencias[]" value="{{ $competencia->id }}">
                                <span>{{ $competencia->nombre }}</span>
                            </label>
                        @empty
                            <div class="cf-comp-empty">No hay competencias registradas todavía.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="cf-actions" id="cfActions">
                <a href="{{ route('curso.index') }}" class="cf-btn cf-btn-ghost">Cancelar</a>
                <button type="submit" class="cf-btn cf-btn-primary" id="cfSubmitBtn" data-loading-text="Guardando…">
                    <i class="fas fa-save"></i>Guardar curso
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>
        (function() {
            'use strict';
            var hasGsap = typeof window.gsap !== 'undefined';

            // ── Ciclos dinámicos según el Programa elegido ──
            var selectPrograma = document.getElementById('programa_id');
            var selectCiclo = document.getElementById('ciclo_id');
            selectPrograma.addEventListener('change', function() {
                var programaId = this.value;
                selectCiclo.innerHTML = '<option value="" selected disabled>Cargando…</option>';
                fetch('/obtener-ciclos/' + programaId)
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        selectCiclo.innerHTML = '';
                        if (!data.length) {
                            selectCiclo.innerHTML = '<option value="" selected disabled>Sin ciclos para este programa</option>';
                            return;
                        }
                        var placeholder = document.createElement('option');
                        placeholder.value = '';
                        placeholder.selected = true;
                        placeholder.disabled = true;
                        placeholder.text = 'Elegir ciclo…';
                        selectCiclo.appendChild(placeholder);
                        data.forEach(function(ciclo) {
                            var option = document.createElement('option');
                            option.value = ciclo.id;
                            option.text = ciclo.nombre;
                            selectCiclo.appendChild(option);
                        });
                    });
            });

            // ── Horas/créditos opcionales para cursos Extracurriculares (siempre visibles y editables) ──
            var selectCc = document.getElementById('cc');
            var horas = document.getElementById('horas');
            var creditos = document.getElementById('creditos');
            var extraNote = document.getElementById('cfExtraNote');

            function actualizarRequeridos() {
                var esExtracurricular = selectCc.value === 'Extracurricular';
                extraNote.style.display = esExtracurricular ? 'flex' : 'none';
                horas.required = !esExtracurricular;
                creditos.required = !esExtracurricular;
            }
            selectCc.addEventListener('change', actualizarRequeridos);
            actualizarRequeridos();

            // ── Contador de caracteres de la sumilla ──
            var sumilla = document.getElementById('sumilla');
            var sumillaCount = document.getElementById('sumillaCount');
            function actualizarContador() { sumillaCount.textContent = sumilla.value.length; }
            sumilla.addEventListener('input', actualizarContador);
            actualizarContador();

            // ── Competencias: buscador, marcar/quitar todas, contador, resaltado ──
            var compGrid = document.getElementById('cfCompGrid');
            var compCards = Array.prototype.slice.call(compGrid.querySelectorAll('.cf-comp-card'));
            var compCount = document.getElementById('cfCompCount');
            var compSearch = document.getElementById('cfCompSearch');

            function actualizarContadorComp() {
                var n = compCards.filter(function(c) { return c.querySelector('input').checked; }).length;
                compCount.textContent = n;
            }

            compCards.forEach(function(card) {
                var checkbox = card.querySelector('input');
                checkbox.addEventListener('change', function() {
                    card.classList.toggle('is-checked', checkbox.checked);
                    actualizarContadorComp();
                });
            });

            var btnAll = document.getElementById('cfCompAll');
            var btnNone = document.getElementById('cfCompNone');
            if (btnAll) {
                btnAll.addEventListener('click', function() {
                    compCards.forEach(function(card) {
                        if (card.style.display === 'none') return;
                        var checkbox = card.querySelector('input');
                        checkbox.checked = true;
                        card.classList.add('is-checked');
                    });
                    actualizarContadorComp();
                });
            }
            if (btnNone) {
                btnNone.addEventListener('click', function() {
                    compCards.forEach(function(card) {
                        var checkbox = card.querySelector('input');
                        checkbox.checked = false;
                        card.classList.remove('is-checked');
                    });
                    actualizarContadorComp();
                });
            }
            if (compSearch) {
                compSearch.addEventListener('input', function() {
                    var q = compSearch.value.toLowerCase().trim();
                    compCards.forEach(function(card) {
                        var match = !q || (card.dataset.compNombre || '').includes(q);
                        card.style.display = match ? '' : 'none';
                    });
                });
            }
            actualizarContadorComp();

            // ── Evitar doble envío ──
            var form = document.getElementById('cfForm');
            var submitBtn = document.getElementById('cfSubmitBtn');
            form.addEventListener('submit', function() {
                if (submitBtn.disabled) return;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>' + submitBtn.dataset.loadingText;
            });
            window.addEventListener('pageshow', function(event) {
                if (!event.persisted) return;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i>Guardar curso';
            });

            // ── Entrada con vida ──
            if (hasGsap) {
                try {
                    gsap.from('#cfHero', { opacity: 0, y: -12, duration: .45, ease: 'power2.out' });
                    gsap.from('.cf-card', { opacity: 0, y: 14, duration: .45, stagger: .08, delay: .1, ease: 'power2.out' });
                } catch (e) {}
            }
        })();
    </script>
@endsection
