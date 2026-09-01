<div class="table-responsive" id="ppd-tabla-responsive">
    <table class="table table-hover" style="font-size: 14px">
        <thead class="thead-dark">
            <tr>
                <th scope="col">N°</th>
                <th scope="col">Nombre</th>
                <th scope="col">Detalles académicos</th>
                <th scope="col">Ficha Técnica</th>
                <th scope="col">Voucher</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @php $grupoActual = null; $n = 1; @endphp
            @forelse ($alumnos as $alumno)
                @php
                    $grupo =
                        (optional($alumno->programa)->nombre
                            ?? optional(optional($alumno->ciclo)->programa)->nombre
                            ?? 'Sin programa') .
                        ' — Ciclo ' .
                        (optional($alumno->ciclo)->nombre ?? '?');
                @endphp
                @if ($grupo !== $grupoActual)
                    @php
                        $grupoActual = $grupo;
                        $kGrupo =
                            (string) ($alumno->programa_id ?? '0') .
                            '|' .
                            (string) ($alumno->ciclo_id ?? '0');
                        $nGrupo = $conteoGrupoListado[$kGrupo] ?? 0;
                        $nTotalCiclo = optional($totalesPorCicloId->get($alumno->ciclo_id))->total;
                    @endphp
                    <tr class="table-active">
                        <td colspan="6" class="py-2">
                            <strong>{{ $grupo }}</strong>
                            <span class="badge badge-secondary ml-2">
                                {{ $nGrupo }} {{ $nGrupo === 1 ? 'alumno' : 'alumnos' }}
                            </span>
                            @if ($nTotalCiclo !== null && (int) $nGrupo !== (int) $nTotalCiclo)
                                <span class="text-muted small ml-2"
                                    title="Total PPD en este ciclo (sin filtros de búsqueda)">
                                    · {{ $nTotalCiclo }} en ciclo (total)
                                </span>
                            @endif
                        </td>
                    </tr>
                @endif
                <tr>
                    <td class="text-muted font-weight-bold">{{ $n }}</td>
                    <td>
                        <strong>{{ $alumno->apellidos }}, {{ $alumno->name }}</strong>
                        @if (($periodoFiltroId ?? null) && $alumno->alumnoB)
                            @if ($alumno->alumnoB->matriculas->isNotEmpty())
                                <span class="badge badge-success ml-1 align-middle" title="Matriculado en el período filtrado">
                                    <i class="fas fa-check-circle fa-xs"></i> Matriculado
                                </span>
                            @else
                                <span class="badge badge-secondary ml-1 align-middle" title="Aún no completa su matrícula en el período filtrado">
                                    <i class="fas fa-exclamation-circle fa-xs"></i> No matriculado
                                </span>
                            @endif
                        @endif
                        <ul class="mb-0 pl-3">
                            <li>DNI: {{ $alumno->dni }}</li>
                            <li>{{ $alumno->email }}</li>
                            @if ($alumno->alumnoB)
                                <li>Número: {{ $alumno->alumnoB->numero ?? '—' }}</li>
                                <li>Referencia: {{ $alumno->alumnoB->numero_referencia ?? '—' }}</li>
                            @elseif ($alumno->telefono)
                                <li>Tel: {{ $alumno->telefono }}</li>
                            @endif
                        </ul>
                    </td>
                    <td>
                        <ul class="mb-0 pl-3">
                            <li>{{ optional($alumno->programa)->nombre ?? optional(optional($alumno->ciclo)->programa)->nombre ?? '—' }}
                                – {{ optional($alumno->ciclo)->nombre ?? '?' }}</li>
                            @if ($alumno->lengua_1)
                                <li>Lengua 1: {{ $alumno->lengua_1 }}</li>
                            @endif
                            @if ($alumno->fecha_nacimiento)
                                <li>Nac.: {{ $alumno->fecha_nacimiento }}</li>
                            @endif
                        </ul>
                    </td>
                    <td>
                        @if ($alumno->alumnoB)
                            <span class="badge badge-success" title="El alumno tiene su ficha técnica (perfil PPD) registrada">✅ Completa</span>
                        @else
                            <span class="badge badge-secondary" title="Falta registrar la ficha técnica (perfil PPD) del alumno">❌ Sin ficha</span>
                        @endif
                    </td>
                    <td>
                        @php $matriculaVoucherPpd = $alumno->alumnoB?->matriculas->first(); @endphp
                        @if ($matriculaVoucherPpd)
                            <div class="small font-weight-bold mb-1">{{ $matriculaVoucherPpd->comprobante ?? '—' }}</div>
                            @if ($matriculaVoucherPpd->voucher_imagen)
                                <a href="{{ asset('vouchers/ppd/'.$matriculaVoucherPpd->voucher_imagen) }}" target="_blank"
                                    class="small d-block mb-1">
                                    <i class="fa fa-receipt fa-xs mr-1"></i>Ver voucher
                                </a>
                            @endif
                            <form action="{{ route('matriculasppd.verificarVoucher', $matriculaVoucherPpd->id) }}"
                                method="POST" class="d-inline js-toggle-verificado">
                                @csrf
                                <label class="mb-1 small" style="cursor:pointer;">
                                    <input type="checkbox" class="js-verificado-checkbox"
                                        {{ $matriculaVoucherPpd->voucher_verificado ? 'checked' : '' }}>
                                    Verificado
                                </label>
                            </form>
                            <form action="{{ route('matriculasppd.enviarFicha', $matriculaVoucherPpd->id) }}"
                                method="POST" class="d-inline js-enviar-ficha"
                                data-nombre="{{ $alumno->apellidos }}, {{ $alumno->name }}"
                                data-reenvio="{{ $matriculaVoucherPpd->ficha_enviada_at ? '1' : '0' }}">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $matriculaVoucherPpd->ficha_enviada_at ? 'btn-outline-primary' : 'btn-primary' }} d-block"
                                    {{ $matriculaVoucherPpd->voucher_verificado ? '' : 'disabled' }}
                                    data-title-enabled="Enviar notificación de matrícula completada al alumno"
                                    data-title-disabled="Marca el voucher como verificado primero"
                                    title="{{ $matriculaVoucherPpd->voucher_verificado ? 'Enviar notificación de matrícula completada al alumno' : 'Marca el voucher como verificado primero' }}">
                                    <i class="fa {{ $matriculaVoucherPpd->ficha_enviada_at ? 'fa-redo' : 'fa-paper-plane' }} fa-xs"></i>
                                    {{ $matriculaVoucherPpd->ficha_enviada_at ? 'Reenviar correo' : 'Confirmar matrícula' }}
                                </button>
                            </form>
                            @if ($matriculaVoucherPpd->ficha_enviada_at)
                                <span class="text-muted small d-block" title="Última vez enviado">
                                    <i class="fa fa-check-double fa-xs"></i> Enviada el {{ $matriculaVoucherPpd->ficha_enviada_at->format('d/m/Y H:i') }}
                                </span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if ($alumno->alumnoB)
                            <a href="{{ route('ppd.show', $alumno->alumnoB->id) }}"
                                class="btn btn-sm btn-primary" title="Ver">
                                <i class="fa fa-eye fa-sm"></i>
                            </a>
                            <a href="{{ route('ppd.edit', $alumno->alumnoB->id) }}"
                                class="btn btn-sm btn-warning" title="Editar">
                                <i class="fa fa-edit fa-sm"></i>
                            </a>
                        @else
                            <a href="{{ route('alumnos.edit', ['alumno' => $alumno->alumno->id ?? 0]) }}"
                                class="btn btn-sm btn-warning" title="Editar usuario">
                                <i class="fa fa-edit fa-sm"></i>
                            </a>
                        @endif
                        @role('super-admin')
                            @php $matriculaPpdActual = $alumno->alumnoB?->matriculas->first(); @endphp
                            @if ($matriculaPpdActual)
                                <form action="{{ route('matriculasppd.quitar', $matriculaPpdActual->id) }}"
                                      method="POST" class="d-inline js-quitar-matricula-ppd"
                                      data-nombre="{{ $alumno->apellidos }}, {{ $alumno->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-warning"
                                        title="Quitar matrícula del período actual (solo super-admin)">
                                        <i class="fa fa-user-times fa-sm"></i>
                                    </button>
                                </form>
                            @endif
                        @endrole
                        <button type="button" class="btn btn-sm btn-danger"
                            onclick="confirmarEliminar('{{ route('adminDestroy', ['id' => $alumno->id]) }}', '{{ addslashes($alumno->apellidos . ', ' . $alumno->name) }}')"
                            title="Eliminar">
                            <i class="fa fa-trash fa-sm"></i>
                        </button>
                    </td>
                </tr>
                @php $n++; @endphp
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No se encontraron alumnos PPD.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if (session('error'))
        <span class="text-danger text-sm">{{ session('error') }}</span>
    @endif
</div>
