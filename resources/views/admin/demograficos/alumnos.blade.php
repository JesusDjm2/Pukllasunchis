@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('titulo', 'Datos Demográficos')
@section('contenido')

@php
    $esFidPeriodo = $tab === 'fid_periodo';
    $esFidTodos   = $tab === 'fid_todos';
    $esPpdPeriodo = $tab === 'ppd_periodo';
    $esPpdTodos   = $tab === 'ppd_todos';
    $esFid        = $esFidPeriodo || $esFidTodos;
    $esPpd        = $esPpdPeriodo || $esPpdTodos;
    $hayDatos     = $totalAlumnos > 0;

    // Descripción del contexto activo
    if ($esFidPeriodo && $periodoSeleccionado) {
        $contextoLabel = 'FID · Alumnos con matrícula en <strong>' . e($periodoSeleccionado->nombre) . '</strong>';
        $contextoIcon  = 'fa-calendar-check';
        $accentColor   = '#4e79a7';
    } elseif ($esFidTodos) {
        $contextoLabel = 'FID · Todos los alumnos registrados en el sistema';
        $contextoIcon  = 'fa-users';
        $accentColor   = '#4e79a7';
    } elseif ($esPpdPeriodo && $periodoPpdSeleccionado) {
        $contextoLabel = 'PPD · Alumnos en el período <strong>' . e($periodoPpdSeleccionado->nombre) . '</strong>';
        $contextoIcon  = 'fa-calendar-alt';
        $accentColor   = '#f59e0b';
    } else {
        $contextoLabel = 'PPD · Todos los alumnos registrados en el sistema';
        $contextoIcon  = 'fa-user-graduate';
        $accentColor   = '#f59e0b';
    }
@endphp

<style>
/* ── Tabs ────────────────────────────────────────────────── */
.dem-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:0; }
.dem-tab {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 16px; border-radius:6px 6px 0 0;
    font-size:13px; font-weight:600; text-decoration:none;
    border:1px solid #dee2e6; border-bottom:none;
    background:#f8f9fc; color:#6c757d;
    transition:all .15s;
}
.dem-tab:hover { background:#e9ecef; color:#495057; text-decoration:none; }
.dem-tab.active-fid  { background:#4e79a7; color:#fff; border-color:#4e79a7; }
.dem-tab.active-ppd  { background:#f59e0b; color:#fff; border-color:#f59e0b; }
.dem-tab .badge-tipo {
    font-size:10px; padding:2px 6px; border-radius:10px; font-weight:700;
    background:rgba(255,255,255,.25); color:inherit;
}
.dark-mode .dem-tab, .dim-mode .dem-tab { background:#2c2f3a; color:#adb5bd; border-color:#3a3d4a; }
.dark-mode .dem-tab:hover, .dim-mode .dem-tab:hover { background:#373a47; color:#dee2e6; }
.dark-mode .dem-tab.active-fid, .dim-mode .dem-tab.active-fid { background:#4e79a7; color:#fff; }
.dark-mode .dem-tab.active-ppd, .dim-mode .dem-tab.active-ppd { background:#d97706; color:#fff; }

/* ── KPI cards ───────────────────────────────────────────── */
.kpi-card {
    border-radius:10px; padding:16px 20px; display:flex;
    align-items:center; gap:14px; border:none;
    background:#fff; box-shadow:0 1px 6px rgba(0,0,0,.08);
}
.kpi-icon {
    width:48px; height:48px; border-radius:10px; display:flex;
    align-items:center; justify-content:center; font-size:20px;
    flex-shrink:0;
}
.kpi-value { font-size:28px; font-weight:700; line-height:1; }
.kpi-label { font-size:12px; color:#6c757d; margin-top:2px; }
.dark-mode .kpi-card, .dim-mode .kpi-card { background:#2c2f3a; }
.dark-mode .kpi-label, .dim-mode .kpi-label { color:#adb5bd; }

/* ── Chart cards ─────────────────────────────────────────── */
.chart-card { border:none; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,.07); }
.chart-card .card-header {
    background:transparent; border-bottom:1px solid rgba(0,0,0,.07);
    padding:12px 16px 8px; border-radius:10px 10px 0 0;
}
.chart-title { font-size:13px; font-weight:700; color:#495057; display:flex; align-items:center; gap:6px; }
.chart-title .icon { width:22px; height:22px; border-radius:5px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; }
.dark-mode .chart-card, .dim-mode .chart-card { background:#2c2f3a; }
.dark-mode .chart-card .card-header, .dim-mode .chart-card .card-header { border-color:rgba(255,255,255,.08); }
.dark-mode .chart-title, .dim-mode .chart-title { color:#dee2e6; }
</style>

<div class="container-fluid p-4">

    {{-- ══════════════════════════════════════════════════════
         CABECERA + TABS + SELECTOR DE PERÍODO
    ══════════════════════════════════════════════════════ --}}
    <div class="mb-0">
        <div class="d-flex align-items-end justify-content-between flex-wrap" style="gap:8px">
            <div>
                <h4 class="font-weight-bold mb-1" style="color:{{ $accentColor }}">
                    <i class="fas fa-chart-bar mr-2"></i> Datos Demográficos
                </h4>
                <p class="text-muted small mb-2">{!! $contextoLabel !!}
                    @if ($esFidPeriodo && $periodoSeleccionado?->actual)
                        <span class="badge badge-success ml-1">Período actual</span>
                    @elseif ($esPpdPeriodo && $periodoPpdSeleccionado?->actual)
                        <span class="badge badge-warning ml-1">Período actual</span>
                    @endif
                </p>
            </div>

            {{-- Selector de período (solo para tabs "por período") --}}
            @if ($esFidPeriodo)
                <form method="GET" action="{{ route('alumnos.demograficos') }}" class="d-flex align-items-center" style="gap:8px">
                    <input type="hidden" name="tab" value="fid_periodo">
                    <label class="mb-0 text-muted small font-weight-bold text-nowrap">Ver período FID:</label>
                    <select name="periodo_id" class="form-control form-control-sm" style="max-width:220px"
                            onchange="this.form.submit()">
                        @forelse ($periodos as $p)
                            <option value="{{ $p->id }}"
                                {{ $periodoSeleccionado && $periodoSeleccionado->id == $p->id ? 'selected' : '' }}>
                                {{ $p->nombre }}{{ $p->actual ? ' ★' : '' }}
                            </option>
                        @empty
                            <option disabled>Sin períodos registrados</option>
                        @endforelse
                    </select>
                </form>
            @elseif ($esPpdPeriodo)
                <form method="GET" action="{{ route('alumnos.demograficos') }}" class="d-flex align-items-center" style="gap:8px">
                    <input type="hidden" name="tab" value="ppd_periodo">
                    <label class="mb-0 text-muted small font-weight-bold text-nowrap">Ver período PPD:</label>
                    <select name="periodo_ppd_id" class="form-control form-control-sm" style="max-width:220px"
                            onchange="this.form.submit()">
                        @forelse ($periodosPpd as $p)
                            <option value="{{ $p->id }}"
                                {{ $periodoPpdSeleccionado && $periodoPpdSeleccionado->id == $p->id ? 'selected' : '' }}>
                                {{ $p->nombre }}{{ $p->actual ? ' ★' : '' }}
                            </option>
                        @empty
                            <option disabled>Sin períodos PPD registrados</option>
                        @endforelse
                    </select>
                </form>
            @endif
        </div>

        {{-- Tabs --}}
        <div class="dem-tabs">
            @php
                $baseUrl = route('alumnos.demograficos');
                $fidPeriodoId = $periodoSeleccionado?->id ?? ($periodos->first()?->id ?? '');
                $ppdPeriodoId = $periodoPpdSeleccionado?->id ?? ($periodosPpd->first()?->id ?? '');
            @endphp
            <a href="{{ $baseUrl }}?tab=fid_periodo{{ $fidPeriodoId ? '&periodo_id='.$fidPeriodoId : '' }}"
               class="dem-tab {{ $esFidPeriodo ? 'active-fid' : '' }}">
                <i class="fas fa-calendar-check"></i>
                <span>FID · Por Período</span>
            </a>
            <a href="{{ $baseUrl }}?tab=fid_todos"
               class="dem-tab {{ $esFidTodos ? 'active-fid' : '' }}">
                <i class="fas fa-users"></i>
                <span>FID · Todos</span>
            </a>
            <a href="{{ $baseUrl }}?tab=ppd_periodo{{ $ppdPeriodoId ? '&periodo_ppd_id='.$ppdPeriodoId : '' }}"
               class="dem-tab {{ $esPpdPeriodo ? 'active-ppd' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>PPD · Por Período</span>
            </a>
            <a href="{{ $baseUrl }}?tab=ppd_todos"
               class="dem-tab {{ $esPpdTodos ? 'active-ppd' : '' }}">
                <i class="fas fa-user-graduate"></i>
                <span>PPD · Todos</span>
            </a>
        </div>
    </div>

    {{-- Línea de separación bajo las tabs --}}
    <div style="border-top:2px solid {{ $accentColor }}; margin-bottom:20px; opacity:.6;"></div>

    {{-- ══════════════════════════════════════════════════════
         ESTADOS VACÍOS
    ══════════════════════════════════════════════════════ --}}
    @if ($esFidPeriodo && ! $periodoSeleccionado)
        <div class="alert alert-warning text-center py-4">
            <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
            <strong>No hay períodos FID configurados.</strong><br>
            <span class="small">Crea un período activo desde <em>Gestión académica → Periodos</em>.</span>
        </div>
    @elseif ($esPpdPeriodo && ! $periodoPpdSeleccionado)
        <div class="alert alert-warning text-center py-4">
            <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
            <strong>No hay períodos PPD configurados.</strong>
        </div>
    @elseif (! $hayDatos)
        <div class="alert alert-info text-center py-4">
            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
            @if ($esFidPeriodo)
                <strong>Sin matrículas para «{{ $periodoSeleccionado->nombre }}».</strong><br>
                <span class="small">
                    {{ $periodoSeleccionado->actual ? 'Este es el período actual. Aún no hay alumnos matriculados.' : 'Los datos de períodos sin matrículas no están disponibles.' }}
                </span>
            @elseif ($esPpdPeriodo)
                <strong>Sin alumnos PPD en «{{ $periodoPpdSeleccionado->nombre }}».</strong><br>
                <span class="small">No se encontraron registros de calificaciones para este período PPD.</span>
            @else
                <strong>Sin registros disponibles.</strong>
            @endif
        </div>
    @else

    {{-- ══════════════════════════════════════════════════════
         KPI SUMMARY ROW
    ══════════════════════════════════════════════════════ --}}
    @php
        $generoMasc    = $generos->get('Masculino', $generos->get('masculino', 0));
        $generoFem     = $generos->get('Femenino', $generos->get('femenino', 0));
        $conEdad       = $edad_18_25 + $edad_26_35;
        $programaTop   = $porPrograma->keys()->first() ?? '—';
    @endphp
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:{{ $esFid ? '#e8f0f8' : '#fef3c7' }}; color:{{ $accentColor }}">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="kpi-value" style="color:{{ $accentColor }}">{{ $totalAlumnos }}</div>
                    <div class="kpi-label">Total alumnos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#fce4ec; color:#e91e63">
                    <i class="fas fa-venus-mars"></i>
                </div>
                <div>
                    <div class="kpi-value" style="color:#e91e63; font-size:18px">
                        {{ $generoMasc }}<span class="text-muted" style="font-size:13px"> H</span>
                        &nbsp;/&nbsp;
                        {{ $generoFem }}<span class="text-muted" style="font-size:13px"> M</span>
                    </div>
                    <div class="kpi-label">Distribución de género</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#e8f5e9; color:#43a047">
                    <i class="fas fa-birthday-cake"></i>
                </div>
                <div>
                    <div class="kpi-value" style="color:#43a047">{{ $conEdad }}</div>
                    <div class="kpi-label">Entre 18 – 35 años</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="kpi-card">
                <div class="kpi-icon" style="background:#e3f2fd; color:#1976d2">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <div class="kpi-value" style="color:#1976d2; font-size:14px; line-height:1.3">
                        {{ Str::limit($programaTop, 28) }}
                    </div>
                    <div class="kpi-label">Programa con más alumnos</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 1: Género · Edades · Sector socioeconómico · Programa
    ══════════════════════════════════════════════════════ --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#fce4ec; color:#e91e63"><i class="fas fa-venus-mars"></i></span>
                        Género
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:200px">
                    <canvas id="chartGenero"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#e8f5e9; color:#43a047"><i class="fas fa-birthday-cake"></i></span>
                        Rango de Edades
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:200px">
                    <canvas id="chartEdad"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#fff8e1; color:#f9a825"><i class="fas fa-coins"></i></span>
                        Sector Socioeconómico
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:200px">
                    <canvas id="chartSectorSocio"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#e3f2fd; color:#1976d2"><i class="fas fa-university"></i></span>
                        Por Programa
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:200px">
                    <canvas id="chartPrograma"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 2: Ciclos + Estado civil + Trabaja
    ══════════════════════════════════════════════════════ --}}
    <div class="row mb-4">
        <div class="col-md-5 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#e8eaf6; color:#3949ab"><i class="fas fa-layer-group"></i></span>
                        Alumnos por Ciclo
                    </span>
                </div>
                <div class="card-body p-2" style="min-height:220px">
                    <canvas id="chartCiclo"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#fce4ec; color:#c2185b"><i class="fas fa-ring"></i></span>
                        Estado Civil
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:220px">
                    <canvas id="chartEstadoCivil"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#e0f2f1; color:#00796b"><i class="fas fa-hard-hat"></i></span>
                        Trabaja
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:220px">
                    <canvas id="chartTrabajo"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 3: Procedencia familiar (horizontal, full-width)
    ══════════════════════════════════════════════════════ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#e8f5e9; color:#388e3c"><i class="fas fa-home"></i></span>
                        Procedencia Familiar
                    </span>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartProcedencia" style="max-height:230px"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 4: Sector laboral (horizontal, full-width)
    ══════════════════════════════════════════════════════ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#fff3e0; color:#e65100"><i class="fas fa-briefcase"></i></span>
                        Sector Laboral
                    </span>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartSectorLaboral" style="max-height:240px"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 5: Autoidentificación · Lengua materna · Quién mantiene
    ══════════════════════════════════════════════════════ --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#f3e5f5; color:#7b1fa2"><i class="fas fa-id-card"></i></span>
                        Autoidentificación
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:220px">
                    <canvas id="chartTeConsideras"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#e8eaf6; color:#3949ab"><i class="fas fa-language"></i></span>
                        Lengua Materna
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:220px">
                    <canvas id="chartLenguas"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card chart-card h-100">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#fce4ec; color:#c2185b"><i class="fas fa-hand-holding-heart"></i></span>
                        Quién Mantiene al Alumno
                    </span>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-2" style="min-height:220px">
                    <canvas id="chartMantiene"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FILA 6: Ingreso mensual familiar (full-width)
    ══════════════════════════════════════════════════════ --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card">
                <div class="card-header">
                    <span class="chart-title">
                        <span class="icon" style="background:#e0f7fa; color:#00838f"><i class="fas fa-money-bill-wave"></i></span>
                        Ingreso Mensual Familiar
                    </span>
                </div>
                <div class="card-body p-2">
                    <canvas id="chartIngreso" style="max-height:220px"></canvas>
                </div>
            </div>
        </div>
    </div>

    @endif {{-- fin @if hayDatos --}}
</div>

{{-- ══════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@if ($hayDatos)
<script>
// ── Datos del backend ────────────────────────────────────────────────────────
const edad_18_25    = {{ $edad_18_25 }};
const edad_26_35    = {{ $edad_26_35 }};
const programas     = @json($porPrograma);
const ciclos        = @json($porCiclo);
const generos       = @json($generos);
const sectorSocio   = @json($sectorSocio);
const procedencia   = @json($procedencia);
const sectorLaboral = @json($sectorLaboral);
const teConsideras  = @json($teConsideras);
const lenguas       = @json($lenguas);
const estadoCivil   = @json($estadoCivil);
const quienMantiene = @json($quienMantiene);
const trabajo       = @json($trabajo);
const ingresoMensual= @json($ingresoMensual);
const accentColor   = '{{ $accentColor }}';
const esFid         = {{ $esFid ? 'true' : 'false' }};

// ── Paletas ──────────────────────────────────────────────────────────────────
const PALETA_FID = [
    '#4e79a7','#76b7b2','#59a14f','#edc949','#f28e2b',
    '#e15759','#af7aa1','#ff9da7','#9c755f','#bab0ac',
    '#d87c7c','#919e8b','#6baed6','#74c476','#fd8d3c',
];
const PALETA_PPD = [
    '#f59e0b','#d97706','#b45309','#92400e','#78350f',
    '#fbbf24','#fcd34d','#fde68a','#f87171','#fb923c',
    '#a78bfa','#60a5fa','#34d399','#f472b6','#94a3b8',
];
const PALETA = esFid ? PALETA_FID : PALETA_PPD;

// ── Detectar modo oscuro ─────────────────────────────────────────────────────
const isDark = () => document.body.classList.contains('dark-mode') || document.body.classList.contains('dim-mode');
const textColor = () => isDark() ? '#dee2e6' : '#495057';
const gridColor = () => isDark() ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';

// ── Limpiar datos (sin nulls, vacíos, ceros) ─────────────────────────────────
const limpiar = (obj) => {
    const out = {};
    Object.entries(obj || {}).forEach(([k, v]) => {
        const key = String(k ?? '').trim();
        const val = Number(v ?? 0);
        if (key && !['undefined','null','[object object]'].includes(key.toLowerCase()) && val > 0)
            out[key] = val;
    });
    return out;
};

// ── Abreviar etiquetas largas ────────────────────────────────────────────────
const abreviar = (str, max = 38) => str.length > max ? str.substring(0, max) + '…' : str;

// ── Calcular % en tooltip ────────────────────────────────────────────────────
const tooltipConPct = {
    callbacks: {
        label: (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
            const pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
            return ` ${ctx.label}: ${ctx.parsed}  (${pct}%)`;
        }
    }
};
const tooltipConPctDoughnut = {
    callbacks: {
        label: (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
            const pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
            return ` ${ctx.label}: ${ctx.parsed}  (${pct}%)`;
        }
    }
};

// ── Función base para crear chart ────────────────────────────────────────────
const crearChart = (id, tipo, labels, datos, opts = {}) => {
    const ctx = document.getElementById(id);
    if (!ctx) return;

    if (labels.length === 0) {
        const p = document.createElement('p');
        p.className = 'text-muted text-center small mt-3 py-2';
        p.innerHTML = '<i class="fas fa-inbox mr-1"></i> Sin datos registrados';
        ctx.replaceWith(p);
        return;
    }

    const horizontal = opts.horizontal ?? false;
    const isBar      = tipo === 'bar';
    const tc         = textColor();
    const gc         = gridColor();

    new Chart(ctx, {
        type: tipo,
        data: {
            labels: labels.map(l => abreviar(l, horizontal ? 42 : 18)),
            datasets: [{
                data: datos,
                backgroundColor: opts.colors ?? PALETA,
                borderWidth: isBar ? 0 : 2,
                borderColor: '#fff',
                borderRadius: isBar ? 5 : 0,
                hoverOffset: isBar ? 0 : 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: horizontal ? 'y' : 'x',
            animation: { duration: 400 },
            plugins: {
                legend: {
                    display: !isBar,
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: { size: 11 },
                        color: tc,
                    }
                },
                tooltip: opts.tooltip ?? tooltipConPct,
            },
            scales: isBar ? {
                [horizontal ? 'x' : 'y']: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: tc, font: { size: 11 } },
                    grid: { color: gc },
                },
                [horizontal ? 'y' : 'x']: {
                    ticks: { color: tc, font: { size: 11 } },
                    grid: { display: false },
                }
            } : {},
        }
    });
};

// ── Crear todos los gráficos ──────────────────────────────────────────────────
const graficos = [
    // Pie / Doughnut
    { id: 'chartGenero',      data: generos,       tipo: 'pie'      },
    { id: 'chartPrograma',    data: programas,     tipo: 'doughnut' },
    { id: 'chartSectorSocio', data: sectorSocio,   tipo: 'doughnut' },
    { id: 'chartTeConsideras',data: teConsideras,  tipo: 'pie'      },
    { id: 'chartEstadoCivil', data: estadoCivil,   tipo: 'doughnut' },
    { id: 'chartTrabajo',     data: trabajo,       tipo: 'pie'      },
    // Bar vertical
    { id: 'chartCiclo',       data: ciclos,        tipo: 'bar'                },
    { id: 'chartLenguas',     data: lenguas,       tipo: 'bar'                },
    { id: 'chartIngreso',     data: ingresoMensual,tipo: 'bar'                },
    // Bar horizontal
    { id: 'chartProcedencia', data: procedencia,   tipo: 'bar', horizontal: true },
    { id: 'chartSectorLaboral',data:sectorLaboral, tipo: 'bar', horizontal: true },
    { id: 'chartMantiene',    data: quienMantiene, tipo: 'bar', horizontal: true },
];

graficos.forEach(({ id, data, tipo, horizontal }) => {
    const limpio = limpiar(data);
    crearChart(id, tipo, Object.keys(limpio), Object.values(limpio), { horizontal });
});

// Rango de edades (datos directos del backend)
crearChart('chartEdad', 'bar',
    ['18 – 25 años', '26 – 35 años'],
    [edad_18_25, edad_26_35],
    { colors: ['#59a14f', '#edc949'] }
);
</script>
@endif

@endsection
