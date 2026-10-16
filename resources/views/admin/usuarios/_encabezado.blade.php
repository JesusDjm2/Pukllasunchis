{{--
    Hero del directorio (/Administrador), compartido por admin.index y superadmin.dashboard.
    Mismo diseño que el de Gestión académica (admin.curso.index): .pg-hero + .cu-hero de
    admin._partials.prog-styles. Las becas se cuentan solo sobre estudiantes vigentes (FID y PPD,
    sin egresados ni retirados), nunca sobre el personal.
    Recibe: $totalRecords, $becas, $estudiantesVigentes.
--}}
@include('admin._partials.prog-styles')
@php
    $pctBecas = $estudiantesVigentes > 0 ? round($becas['total'] * 100 / $estudiantesVigentes, 1) : 0;
@endphp
<div class="pg-hero cu-hero" id="pg-hero">
    <div class="cu-hero__fila">
        <div class="cu-hero__titulo">
            <h4 class="mb-0">Directorio</h4>
            <p class="pg-hero-sub">Estudiantes, docentes y personal · EESP Pukllasunchis</p>
        </div>
        <div class="cu-hero__kpis" aria-label="Resumen del directorio">
            <div class="cu-kpi" title="Todas las cuentas del sistema: estudiantes, docentes y personal">
                <span class="cu-kpi__val">{{ number_format($totalRecords) }}</span>
                <span class="cu-kpi__lbl"><i class="fas fa-users"></i> Cuentas</span>
            </div>
            <div class="cu-kpi" title="Estudiantes FID y PPD sin egresados ni retirados">
                <span class="cu-kpi__val">{{ number_format($estudiantesVigentes) }}</span>
                <span class="cu-kpi__lbl"><i class="fas fa-user-graduate"></i> Estudiantes vigentes</span>
            </div>
            <a href="#" class="cu-kpi cu-kpi--beca cu-kpi--link" data-up-filtro="tab=fid&beca=1&sinegr=1"
               title="Estudiantes vigentes con beca (FID {{ $becas['fid'] }} · PPD {{ $becas['ppd'] }}). No cuenta egresados, retirados ni personal. Clic para verlos.">
                <span class="cu-kpi__val">{{ $becas['total'] }}<span class="cu-kpi__sub">{{ str_replace('.', ',', $pctBecas) }} %</span></span>
                <span class="cu-kpi__lbl"><i class="fas fa-award"></i> Becados</span>
            </a>
        </div>
        <a href="{{ route('registerAdmin') }}" class="pg-hero-btn cu-hero__btn">
            <i class="fas fa-plus fa-xs"></i> Nuevo registro
        </a>
    </div>
</div>
