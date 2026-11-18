@php
    $u = auth()->user();
    $docente = $u?->docente;
    if ($u?->hasRole('super-admin')) {
        $parentLayout = 'layouts.superadmin';
    } elseif ($u?->hasRole('docente') && $docente) {
        $parentLayout = 'layouts.docente';
    } elseif ($u?->hasAnyRole(['admin', 'adminB', 'gestor-titulacion', 'tutor', 'secretaria', 'direccion', 'bienestar'])) {
        $parentLayout = 'layouts.admin';
    } elseif ($u?->hasRole('alumnoB') || $u?->alumnoB) {
        $parentLayout = 'layouts.profesionalizacion';
        $alumno = $u?->alumnoB;
    } elseif ($u?->hasRole('alumno') || $u?->alumno || $u?->condicion === 'Egresado' || $u?->hasRole('egresado')) {
        $parentLayout = 'layouts.alumno';
        $alumno = $u?->alumno;
    } else {
        $parentLayout = 'layouts.seguimiento_base';
    }
@endphp
@extends($parentLayout)
