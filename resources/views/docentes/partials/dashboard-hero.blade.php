{{-- Cabecera del dashboard: saludo, periodo e interruptor FID/PPD.
     Variables: $primerNombre, $periodoActual, $metricas --}}
<div class="card docente-ui-card docente-ui-hero docente-dash-hero mb-3 mb-md-4">
    <div class="card-body p-3 p-md-4">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <p class="docente-ui-kicker mb-1">Área docente</p>
                <h1 class="mb-2">Hola, {{ $primerNombre }} 👋</h1>
                <p class="docente-ui-subtitle mb-2">
                    Aquí gestionas tus cursos, sílabos y datos de Classroom. Usa el interruptor para
                    alternar entre FID y PPD.
                </p>
                @if (!empty($periodoActual))
                    <span class="badge-periodo"><i class="far fa-calendar-alt mr-1"></i>Periodo:
                        {{ $periodoActual->nombre }}</span>
                @endif
            </div>
            <div class="col-lg-4 text-center text-lg-right">
                <div class="docente-dash-toggle" role="group" aria-label="Vista de cursos">
                    <button type="button" class="btn active" id="btn-tab-fid" onclick="mostrarTabla('fid')"
                        aria-pressed="true">
                        <i class="fas fa-graduation-cap mr-1"></i>FID
                        @if ($metricas['cursosFid'] > 0)
                            <span class="badge badge-light">{{ $metricas['cursosFid'] }}</span>
                        @endif
                    </button>
                    <button type="button" class="btn" id="btn-tab-ppd" onclick="mostrarTabla('ppd')"
                        aria-pressed="false">
                        <i class="fas fa-chalkboard-teacher mr-1"></i>PPD
                        @if ($metricas['cursosPpd'] > 0)
                            <span class="badge badge-light">{{ $metricas['cursosPpd'] }}</span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
