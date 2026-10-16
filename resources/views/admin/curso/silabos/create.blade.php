@php
    $layout = auth()->user()?->hasRole('docente')
        ? 'layouts.docente'
        : (auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin');
@endphp
@extends($layout)

@section('titulo', 'Crear sílabo')

@section('contenido')
    @include('partials.silabo-form-styles')
    <style>
        table tr,
        table td,
        table th {
            pointer-events: none;
        }

        #acciones {
            pointer-events: auto;
        }

        table input,
        table textarea,
        table select {
            pointer-events: auto;
        }
    </style>
    <div class="container-fluid docente-ui-page silabo-form">
        @include('admin.curso.silabos.partials.cabecera', [
            'accion' => 'Nuevo sílabo',
            'curso' => $curso,
            'periodo' => $periodoActual->nombre,
            'backUrl' => optional($docente)->id ? route('vistaDocente', $docente->id) : route('silabos.index'),
            'backLabel' => 'Mis cursos',
        ])

        <div class="row">
            <div class="col-12">
                @if (Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        {{ Session::get('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
            @if ($errors->any())
                <div class="col-12">
                    <div class="alert alert-danger shadow-sm">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <div class="silabo-layout">
        <aside class="silabo-layout-indice">
            @include('admin.curso.silabos.partials.indice')
        </aside>
        <div class="silabo-layout-main">

        {{-- Sección I: la tabla conserva el formato del Ministerio (es la que se exporta a PDF). --}}
        <div class="silabo-section silabo-section-datos" data-informativa>
            <div class="silabo-section-header">
                <span class="silabo-section-icon"><i class="fas fa-id-card"></i></span>
                <div><span class="silabo-section-eyebrow">Sección I</span><h4>Datos generales</h4></div>
                <button type="button" class="silabo-datos-toggle" aria-expanded="false" aria-controls="silaboDatosCuerpo"
                    onclick="var c=document.getElementById('silaboDatosCuerpo'); c.hidden=!c.hidden; this.setAttribute('aria-expanded', !c.hidden); this.querySelector('span').textContent = c.hidden ? 'Ver datos generales' : 'Ocultar';">
                    <span>Ver datos generales</span> <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="silabo-datos-cuerpo" id="silaboDatosCuerpo" hidden>
            <table class="table table-borderless" style="margin-bottom: 0; font-size: 14px; margin-left: 1em;">
                <tbody>
                    <tr>
                        <td style="font-weight: 600; width: 25%; padding: 2px;">1.1 <span style="margin-left:1em">Programa
                                de Estudios</span></td>
                        <td style="padding: 2px;">: {{ $curso->ciclo->programa->nombre }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.2 <span style="margin-left:1em">Componente
                                Curricular</span></td>
                        <td style="padding: 2px;">: {{ $curso->cc }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.3 <span style="margin-left:1em">Curso o módulo</span>
                        </td>
                        <td style="padding: 2px;">: {{ $curso->nombre }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.4 <span style="margin-left:1em">Competencias</span>
                        </td>
                        <td style="padding: 2px;">:
                            @foreach ($curso->competencias as $competencia)
                                {{ $competencia->nombre }}@if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.5 <span style="margin-left:1em">
                                Semestre Académico</span></td>
                        <td style="padding: 2px;">: <span id="periodo">{{ $periodoActual->nombre }}</span></td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.6 <span style="margin-left:1em">Créditos</span></td>
                        <td style="padding: 2px;">: {{ $curso->creditos }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.7 <span style="margin-left:1em">Horas del ciclo</span>
                        </td>
                        <td style="padding: 2px;">: {{ $curso->horas }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.8 <span style="margin-left:1em">Horas Semanales</span>
                        </td>
                        <td style="padding: 2px;">: {{ $curso->horas * 16 }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.9 <span style="margin-left:1em">Docente(s)</span></td>
                        <td style="padding: 2px;">:
                            @foreach ($curso->docentes as $docenteFila)
                                {{ $docenteFila->nombre }}@if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.10 <span style="margin-left:0.4em">Correo
                                Institucional</span></td>
                        <td style="padding: 2px;">:
                            {{ $curso->docentes->first()->email ?? 'Sin correo asignado' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.11 <span style="margin-left:0.4em">Fecha de
                                inicio</span></td>
                        <td style="padding: 2px;">:
                            {{ $periodoActual->fecha_inicio ? \Carbon\Carbon::parse($periodoActual->fecha_inicio)->translatedFormat('d \d\e F \d\e\l Y') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; padding: 2px;">1.12 <span style="margin-left:0.4em">Fecha de
                                término</span></td>
                        <td style="padding: 2px;">:
                            {{ $periodoActual->fecha_cierre ? \Carbon\Carbon::parse($periodoActual->fecha_cierre)->translatedFormat('d \d\e F \d\e\l Y') : '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <form action="{{ route('silabos.store') }}" method="POST" id="silaboForm">
            @csrf
            <input type="hidden" name="curso_id" value="{{ $curso->id }}">
            <input type="hidden" name="docente_id" value="{{ $docente->id }}">
            {{-- $periodoActual->fecha_inicio/fecha_cierre: al crear desde cero no hay
                 un sílabo previo del cual heredar fecha1/fecha2, siempre son las del
                 periodo actual. --}}
            <input type="hidden" id="hidden_fecha1" name="fecha1"
                value="{{ old('fecha1', $periodoActual->fecha_inicio) }}">
            <input type="hidden" id="hidden_fecha2" name="fecha2"
                value="{{ old('fecha2', $periodoActual->fecha_cierre) }}">

            @include('admin.curso.silabos.partials.aviso-borrador')

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-align-left"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección II</span><h4>Sumilla</h4></div>
                </div>
                <textarea name="sumilla" id="sumilla" class="ckeditor form-control form-control-sm">
                    {{ old('sumilla', $curso->sumilla ?? 'Texto por asignar') }}
                </textarea>
                @error('sumilla')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-project-diagram"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección III</span><h4>Vinculación al proyecto integrador</h4></div>
                </div>
                <div class="mb-3">
                    <label for="proyecto_integrador">Proyecto Integrador:</label>
                    <input type="text" name="proyecto_integrador" class="form-control form-control-sm"
                        value="{{ $curso->ciclo->proyecto->nombre ?? 'Sin proyecto asignado' }}" readonly>
                </div>
                <div class="mb-3">
                    <label for="producto_proyecto">Producto del Proyecto:</label>
                    <input type="text" id="producto_proyecto" name="producto_proyecto"
                        class="form-control form-control-sm" required readonly
                        value="{{ $curso->ciclo->proyecto->producto ?? 'Sin proyecto asignado' }}">
                </div>
                <div class="mb-3">
                    <label for="descripcion_proyecto">Propósito del Proyecto Integrador:</label>
                    <textarea id="descripcion_proyecto_integrador" name="descripcion_proyecto_integrador"
                        class="ckeditor form-control form-control-sm" rows="4">
                        {{ old('descripcion_proyecto', $curso->ciclo->proyecto->descripcion ?? 'Sin descripción asignada') }}
                    </textarea>
                </div>
                <div class="mb-3">
                    <label for="vinculacion_pi">Vinculación o aporte del curso con el proyecto integrador:</label>
                    <textarea name="vinculacion_pi" id="vinculacion_pi" class="ckeditor form-control form-control-sm" rows="6"></textarea>
                </div>
                <div class="mb-0">
                    <label for="producto_curso">Producto del Curso:</label>
                    <textarea name="producto_curso" id="producto_curso" class="ckeditor form-control form-control-sm" rows="6"></textarea>
                </div>
            </div>

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-users"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección IV</span><h4>Enfoques transversales</h4></div>
                </div>

                <!-- Select para elegir Enfoques -->
                <div class="mb-3">
                    <select id="enfoque_id" class="form-control form-control-sm">
                        <option selected disabled>Elegir un Enfoque</option>
                        @foreach ($enfoques as $enfoque)
                            <option value="{{ $enfoque->id }}" data-nombre="{{ $enfoque->nombre }}"
                                data-descripcion="{{ $enfoque->descripcion }}"
                                data-observables="{{ $enfoque->observables }}" data-concretas="{{ $enfoque->concretas }}">
                                {{ $enfoque->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" id="enfoques" name="enfoques">
                <div class="mb-3">
                    <button type="button" id="agregarEnfoque" class="btn btn-sm btn-primary">Agregar Enfoque +</button>
                </div>
                <div class="mb-0">
                    <p style="font-weight: 600">Detalles de los Enfoques Seleccionados:</p>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 30%">Enfoque</th>
                                <th>¿Cuándo son observables en la EESP?</th>
                                <th>¿En qué acciones concretas se observa?</th>
                                <th id="acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaEnfoques">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-table"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección V</span><h4>Matriz de planificación y evaluación de aprendizaje</h4></div>
                </div>
                @foreach ($competencias as $index => $competencia)
                    @php
                        $num = $index + 1;
                        $totalCapacidades = count($competencia->capacidad);
                    @endphp

                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 15%;">Competencia</th>
                                <th style="width: 40%;">Estándares</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($totalCapacidades === 0)
                                <tr>
                                    <td class="text-center align-middle">
                                        <strong>{{ $competencia->nombre }}</strong>
                                        <br>
                                        <p style="font-size: 12px">{{ $competencia->descripcion }}</p>
                                    </td>
                                    <td class="text-center text-muted">No hay capacidades asociadas</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="align-middle">
                                        <strong>{{ $competencia->nombre }}:</strong>
                                        <br>
                                        <p style="text-align: justify ">{{ $competencia->descripcion }}</p>
                                    </td>
                                    <td>
                                        @if ($competencia->estandares->isNotEmpty())
                                            @foreach ($competencia->estandares as $estandar)
                                                <p style="text-align: justify" class="p-2">
                                                    {{ $estandar->descripcion }}</p>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No hay estándares asociados</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <table class="table table-bordered" style="margin-top: -15px">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th style="width: 20%;">Capacidades</th>
                                <th style="width: 20%;">Desempeños Específicos</th>
                                <th style="width: 20%;">Criterios de Evaluación</th>
                                <th style="width: 20%;">Evidencias</th>
                                <th style="width: 20%;">Instrumento/Fuente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <textarea class="form-control form-control-sm auto-expand" name="capacidad{{ $num }}"
                                        style="height: 350px; overflow: hidden;">
@foreach ($competencia->capacidad as $capas)
• {{ $capas->descripcion }}&#10;
@endforeach
</textarea>
                                    @error("capacidad{$num}")
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </td>
                                <td>
                                    <textarea class="form-control form-control-sm" name="desempeno{{ $num }}"
                                        placeholder="Desempeño específico"></textarea>
                                </td>
                                <td>
                                    <textarea class="form-control form-control-sm" name="criterio{{ $num }}"
                                        placeholder="Criterio de evaluación"></textarea>
                                </td>
                                <td>
                                    <textarea class="form-control form-control-sm" name="evidencia{{ $num }}" placeholder="Evidencias"></textarea>
                                </td>
                                <td>
                                    <textarea class="form-control form-control-sm" name="instrumento{{ $num }}"
                                        placeholder="Instrumento/Fuente"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach
            </div>

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-layer-group"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección VI</span><h4>Organización de unidades de aprendizaje</h4></div>
                </div>
            <div id="contenedor-tablas">
                <div class="unidad">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Título de la Unidad</th>
                                <th style="width: 80%">
                                    <input type="text" name="titulo_unidad[]" class="form-control form-control-sm">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Situación de Aprendizaje</td>
                                <td>
                                    <textarea name="situacion_aprendizaje[]" class="form-control form-control-sm" rows="2"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered" style="margin-top:-15px">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th>Duración</th>
                                <th>Desempeño Específico</th>
                                <th>Ejes Temáticos (Conocimiento)</th>
                                <th>Evidencia de Proceso</th>
                                <th>Evidencia Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <textarea name="duracion[]" class="form-control form-control-sm" rows="2"></textarea>
                                </td>
                                <td>
                                    <textarea name="desempeno_especifico[]" class="form-control form-control-sm" rows="2"></textarea>
                                </td>
                                <td>
                                    <textarea name="ejes_tematicos[]" class="form-control form-control-sm" rows="2"></textarea>
                                </td>
                                <td>
                                    <textarea name="evidencia_proceso[]" class="form-control form-control-sm" rows="2"></textarea>
                                </td>
                                <td>
                                    <textarea name="evidencia_final[]" class="form-control form-control-sm" rows="2"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar mb-2">Eliminar Tabla</button>
                </div>
            </div>
            <button type="button" class="btn btn-primary mt-2 btn-sm mb-3" id="btn-agregar">Agregar Unidad +</button>
            </div>

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-clipboard-check"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección VII</span><h4>Rúbricas de evaluación</h4></div>
                </div>
                <table class="table table-bordered" id="tabla-rubricas">
                <thead>
                    <tr>
                        <th>Criterio de Evaluación</th>
                        <th>Destacado</th>
                        <th>Logrado</th>
                        <th>En Proceso</th>
                        <th>En Inicio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <textarea name="criterio[]" class="form-control form-control-sm" rows="2"></textarea>
                        </td>
                        <td>
                            <textarea name="destacado[]" class="form-control form-control-sm" rows="2"></textarea>
                        </td>
                        <td>
                            <textarea name="logrado[]" class="form-control form-control-sm" rows="2"></textarea>
                        </td>
                        <td>
                            <textarea name="proceso[]" class="form-control form-control-sm" rows="2"></textarea>
                        </td>
                        <td>
                            <textarea name="inicio[]" class="form-control form-control-sm" rows="2"></textarea>
                        </td>
                        <td id="acciones">
                            <button type="button" class="btn btn-danger btn-sm btn-eliminar-fila"
                                style="display: none;">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
                <button type="button" class="btn btn-primary btn-sm mt-2" id="btn-agregar-fila">Agregar Criterio +</button>
            </div>

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección VIII</span><h4>Modelos metodológicos (metodología)</h4></div>
                </div>
                <textarea id="modelos_metodologicos" name="modelos_metodologicos" class="ckeditor form-control form-control-sm"
                    rows="6"></textarea>
            </div>

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-toolbox"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección IX</span><h4>Recursos y materiales didácticos</h4></div>
                </div>
                <textarea id="recursos" name="recursos" class="ckeditor form-control form-control-sm" rows="6"></textarea>
            </div>

            <div class="silabo-section">
                <div class="silabo-section-header">
                    <span class="silabo-section-icon"><i class="fas fa-book"></i></span>
                    <div><span class="silabo-section-eyebrow">Sección X</span><h4>Referencias</h4></div>
                </div>
                <textarea id="referencias" name="referencias" class="ckeditor form-control form-control-sm" rows="6"></textarea>
            </div>

            @include('admin.curso.silabos.partials.barra-guardar', ['textoGuardar' => 'Guardar sílabo'])
        </form>
        </div>{{-- /.silabo-layout-main --}}
        </div>{{-- /.silabo-layout --}}
    </div>
        <!-- Enfoques -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var selectEnfoque = document.getElementById('enfoque_id');
                var btnAgregar = document.getElementById('agregarEnfoque');
                var tablaEnfoques = document.getElementById('tablaEnfoques');

                btnAgregar.addEventListener('click', function() {
                    var selectedOption = selectEnfoque.options[selectEnfoque.selectedIndex];
                    if (selectedOption.value === "" || selectedOption.value === "Elegir un Enfoque") {
                        alert("Por favor, seleccione un enfoque válido.");
                        return;
                    }

                    var enfoqueId = selectedOption.value;
                    var nombre = selectedOption.getAttribute('data-nombre');
                    var descripcion = selectedOption.getAttribute('data-descripcion') || 'Sin descripción';
                    var observables = selectedOption.getAttribute('data-observables');
                    var concretas = selectedOption.getAttribute('data-concretas');

                    // Verificar si el enfoque ya está agregado
                    if (document.querySelector(`tr[data-id="${enfoqueId}"]`)) {
                        alert("Este enfoque ya ha sido agregado.");
                        return;
                    }

                    var fila = `
                        <tr data-id="${enfoqueId}">
                            <td>
                                <input type="hidden" name="enfoques[]" value="${enfoqueId}">
                                <div>
                                    <input style="font-weight:800; border:none; background:transparent" 
                                        type="text" class="form-control form-control-sm" value="${nombre}" readonly>
                                    <small style="display:block; color:gray;">${descripcion}</small>
                                </div>
                            </td>
                            <td>
                                <textarea name="observables[]" class="form-control form-control-sm" rows="3">${observables}</textarea>
                            </td>
                            <td>
                                <textarea name="concretas[]" class="form-control form-control-sm" rows="3">${concretas}</textarea>
                            </td>
                            <td class="text-center" id="acciones">
                                <button type="button" class="btn btn-sm btn-danger eliminarEnfoque">Eliminar</button>
                            </td>
                        </tr>
                    `;
                    tablaEnfoques.insertAdjacentHTML('beforeend', fila);
                });

                // Evento para eliminar enfoque seleccionado
                tablaEnfoques.addEventListener('click', function(e) {
                    if (e.target.classList.contains('eliminarEnfoque')) {
                        e.target.closest('tr').remove();
                    }
                });
            });
        </script>

        <!---script para unidades-------->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let btnAgregar = document.getElementById('btn-agregar');
                let contenedor = document.getElementById('contenedor-tablas');

                function agregarEventoEliminar(btn) {
                    btn.addEventListener('click', function() {
                        let unidades = document.querySelectorAll('.unidad');
                        if (unidades.length > 1) {
                            this.closest('.unidad').remove();
                        } else {
                            alert("Debe existir al menos una unidad.");
                        }
                    });
                }

                // Agregar unidad nueva
                btnAgregar.addEventListener('click', function() {
                    let unidadOriginal = document.querySelector('.unidad');

                    if (!unidadOriginal) {
                        // Si no hay unidades, crear una base desde cero
                        let nuevaUnidad = document.createElement('div');
                        nuevaUnidad.classList.add('unidad');
                        nuevaUnidad.innerHTML = `
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Título de la Unidad</th>
                                    <th style="width: 80%">
                                        <input type="text" name="titulo_unidad[]" class="form-control form-control-sm">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Situación de Aprendizaje</td>
                                    <td>
                                        <textarea name="situacion_aprendizaje[]" class="form-control form-control-sm" rows="2"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered" style="margin-top:-15px">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th>Duración</th>
                                    <th>Desempeño Específico</th>
                                    <th>Ejes Temáticos (Conocimiento)</th>
                                    <th>Evidencia de Proceso</th>
                                    <th>Evidencia Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><textarea name="duracion[]" class="form-control form-control-sm" rows="2"></textarea></td>
                                    <td><textarea name="desempeno_especifico[]" class="form-control form-control-sm" rows="2"></textarea></td>
                                    <td><textarea name="ejes_tematicos[]" class="form-control form-control-sm" rows="2"></textarea></td>
                                    <td><textarea name="evidencia_proceso[]" class="form-control form-control-sm" rows="2"></textarea></td>
                                    <td><textarea name="evidencia_final[]" class="form-control form-control-sm" rows="2"></textarea></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-danger btn-sm btn-eliminar mb-3">Eliminar Tabla</button>
                    `;
                        contenedor.appendChild(nuevaUnidad);
                        agregarEventoEliminar(nuevaUnidad.querySelector('.btn-eliminar'));
                        return;
                    }

                    let nuevaUnidad = unidadOriginal.cloneNode(true);
                    nuevaUnidad.querySelectorAll('input, textarea').forEach(input => input.value = '');
                    contenedor.appendChild(nuevaUnidad);
                    agregarEventoEliminar(nuevaUnidad.querySelector('.btn-eliminar'));
                });

                // Agregar evento de eliminación a la unidad inicial
                document.querySelectorAll('.btn-eliminar').forEach(btn => {
                    agregarEventoEliminar(btn);
                });
            });
        </script>

        <!-------------Rubricas de evaluacion--------------->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let tablaRubricas = document.getElementById('tabla-rubricas');
                let btnAgregarFila = document.getElementById('btn-agregar-fila');

                function actualizarBotonesEliminar() {
                    let filas = tablaRubricas.querySelectorAll('tbody tr');
                    filas.forEach((fila, index) => {
                        let btnEliminar = fila.querySelector('.btn-eliminar-fila');
                        btnEliminar.style.display = (filas.length > 1) ? 'inline-block' : 'none';
                    });
                }

                function agregarEventoEliminar(fila) {
                    let btnEliminar = fila.querySelector('.btn-eliminar-fila');
                    btnEliminar.addEventListener('click', function() {
                        fila.remove();
                        actualizarBotonesEliminar();
                    });
                }

                // Evento para agregar nuevas filas
                btnAgregarFila.addEventListener('click', function() {
                    let filaOriginal = tablaRubricas.querySelector('tbody tr');
                    let nuevaFila = filaOriginal.cloneNode(true);

                    nuevaFila.querySelectorAll('textarea').forEach(textarea => textarea.value = '');
                    tablaRubricas.querySelector('tbody').appendChild(nuevaFila);

                    agregarEventoEliminar(nuevaFila); // Asignar evento a la nueva fila
                    actualizarBotonesEliminar();
                });

                // Asignar evento de eliminación inicial y actualizar visibilidad de botones
                let filaInicial = tablaRubricas.querySelector('tbody tr');
                agregarEventoEliminar(filaInicial);
                actualizarBotonesEliminar();
            });
        </script>
        <!-- Script para manejar la selección -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const checkboxes = document.querySelectorAll(".capacidad-checkbox");

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener("change", function() {
                        const inputTexto = this.closest("tr").querySelector(".capacidad-texto");

                        if (this.checked) {
                            inputTexto.classList.remove("d-none");
                            inputTexto.removeAttribute("disabled");
                            inputTexto.focus();
                        } else {
                            inputTexto.classList.add("d-none");
                            inputTexto.setAttribute("disabled", "true");
                            inputTexto.value = "";
                        }
                    });
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const editors = ['sumilla', 'descripcion_proyecto_integrador', 'vinculacion_pi', 'producto_curso',
                    'modelos_metodologicos', 'recursos', 'referencias'
                ];

                editors.forEach(id => {
                    if (document.getElementById(id)) {
                        // Sin img[src]: evita que pegar/arrastrar una imagen incruste un
                        // base64 gigante en el textarea y el POST supere post_max_size
                        // (eso vacía $_POST completo, incluido el token CSRF -> error 419).
                        CKEDITOR.replace(id, { disallowedContent: 'img' });
                    }
                });

                // El formulario ya no tiene un <select id="proyecto_id"> (el proyecto
                // integrador se muestra como texto de solo lectura tomado del ciclo),
                // pero se deja el listener por si vuelve a agregarse ese selector.
                const selectProyecto = document.getElementById('proyecto_id');
                if (selectProyecto) {
                    selectProyecto.addEventListener('change', function() {
                        const selectedOption = selectProyecto.options[selectProyecto.selectedIndex];
                        const descripcion = selectedOption.getAttribute('data-descripcion') || '';

                        if (CKEDITOR.instances.descripcion_proyecto) {
                            CKEDITOR.instances.descripcion_proyecto.setData(descripcion);
                        }
                    });
                }

                // Este formulario es largo: mantener la sesión activa mientras se llena
                // para que el token CSRF no expire antes de guardar (error 419).
                setInterval(function() {
                    fetch('{{ route('ping.sesion') }}', { credentials: 'same-origin' }).catch(function() {});
                }, 4 * 60 * 1000);
            });
        </script>

        {{-- Borrador local: lo NO guardado queda en este navegador y se ofrece recuperarlo. --}}
        @include('admin.curso.silabos.partials.borrador', [
            'claveBorrador' => 'puklla-silabo-draft-create-'.$curso->id,
            'clavesObsoletas' => [],
            'conNombresOcultos' => false,
        ])
    @endsection
