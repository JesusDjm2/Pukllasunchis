{{-- Datos personales de quien está en proceso de titulación: cuenta (users) +
     ficha de estudiante (alumnos FID / ppds PPD). Solo lo útil para titulación;
     la encuesta socioeconómica no se muestra. OJO: ficha->correo es un sí/no de
     la encuesta, no una dirección. Lo usan la pestaña "Mis datos" del alumno y
     "Datos personales" del gestor. Espera $seguimiento. --}}
@php
    $persona = $seguimiento->user()->with('programa', 'ciclo', 'alumno')->first();
    $ficha = $persona?->alumno ?? \App\Models\ppd::where('user_id', $persona?->id)->first();
    $fechaNac = $ficha?->fecha_nacimiento ?: $persona?->fecha_nacimiento;
    try {
        $fechaNac = $fechaNac ? \Illuminate\Support\Carbon::parse($fechaNac)->format('d/m/Y') : null;
    } catch (\Throwable) {
        // texto libre no parseable: se muestra tal cual
    }
    $ptiDatos = $seguimiento->pti;
    $tesisDatos = $seguimiento->tesis;
    $secciones = [
        'Identidad' => [
            'Apellidos y nombres' => $persona ? $persona->apellidos.', '.$persona->name : null,
            'DNI' => $persona?->dni ?: $ficha?->dni,
            'Fecha de nacimiento' => $fechaNac,
            'Género' => $ficha?->genero ?: $persona?->genero,
        ],
        'Contacto' => [
            'Correo institucional' => $persona?->email,
            'Celular' => $ficha?->numero ?: $persona?->telefono,
            'Dirección' => ($ficha?->direccion ?? null) ?: $persona?->domicilio,
        ],
        'Datos académicos' => [
            'Programa' => $persona?->programa ? $persona->programa->modalidad->value.' — '.$persona->programa->nombre : null,
            'Promoción / ciclo' => $persona?->ciclo?->nombre,
            'Condición' => $persona?->condicion,
            'Periodo de estudios' => $seguimiento->periodo_estudios,
            'N° de expediente' => $seguimiento->numero_expediente,
            'Asesor(a) del PTI' => $ptiDatos?->asesorNombre(),
            'Asesor(a) de Tesis' => $tesisDatos?->asesorNombre(),
            'Institución de procedencia' => $ficha?->institucion_procedencia ?? null,
            'Carrera de procedencia' => $ficha?->carrera_procedencia ?? null,
        ],
    ];
@endphp
<div class="row">
    @foreach ($secciones as $seccion => $campos)
        @php $campos = array_filter($campos, fn ($v) => filled($v) || $seccion !== 'Datos académicos'); @endphp
        @continue(empty($campos))
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="mt-subtitulo">{{ $seccion }}</div>
            <dl class="mt-datos">
                @foreach ($campos as $etiqueta => $valor)
                    <dt>{{ $etiqueta }}</dt>
                    <dd>{!! filled($valor) ? e($valor) : '<span class="text-muted">No registrado</span>' !!}</dd>
                @endforeach
            </dl>
        </div>
    @endforeach
</div>
