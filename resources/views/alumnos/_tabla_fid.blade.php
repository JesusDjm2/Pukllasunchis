<div class="table-responsive" id="fid-tabla-responsive">
    <table class="table table-hover" style="font-size: 14px">
        <thead class="thead-dark">
            <tr>
                <th scope="col">N°</th>
                <th scope="col">Nombre</th>
                <th scope="col">Detalles académicos</th>
                <th scope="col">Foto</th>
                <th scope="col">Voucher</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @php $grupoActual = null; @endphp
            @php $numeroRegistro = 1; @endphp
            @forelse ($alumnos as $alumno)
                @php
                    $grupo =
                        ($alumno->programa->nombre ?? 'Sin programa') .
                        ' — Ciclo ' .
                        ($alumno->ciclo->nombre ?? '?');
                @endphp
                @if ($grupo !== $grupoActual)
                    @php
                        $grupoActual = $grupo;
                        $kGrupo =
                            (string) ($alumno->programa_id ?? '0') .
                            '|' .
                            (string) ($alumno->ciclo_id ?? '0');
                        $nGrupo = $conteoGrupoListado[$kGrupo] ?? 0;
                    @endphp
                    <tr class="table-active">
                        <td colspan="6" class="py-2">
                            <strong>{{ $grupo }}</strong>
                            <span class="badge badge-secondary ml-2">{{ $nGrupo }}
                                {{ $nGrupo === 1 ? 'alumno' : 'alumnos' }}</span>
                            @php
                                $nTotalCiclo = optional($totalesPorCicloId->get($alumno->ciclo_id))
                                    ->total;
                            @endphp
                            @if ($nTotalCiclo !== null && (int) $nGrupo !== (int) $nTotalCiclo)
                                <span class="text-muted small ml-2"
                                    title="Total FID en este ciclo (sin filtros de búsqueda)">·
                                    {{ $nTotalCiclo }} en ciclo (total)</span>
                            @endif
                        </td>
                    </tr>
                @endif
                <tr>
                    <td class="text-muted font-weight-bold">{{ $numeroRegistro }}</td>
                    <td><strong>{{ $alumno->apellidos }}, {{ $alumno->nombres }}</strong>
                        @if (($alumno->user->beca ?? 0) == 1)
                            <span class="badge badge-success ml-1 align-middle" title="Estudiante becado">
                                Beca
                            </span>
                        @endif
                        @if ($alumno->user && $alumno->user->hasRole('inhabilitado') && ($alumno->user->perfil ?? '') === 'Deuda')
                            <span class="badge badge-warning ml-1 align-middle"
                                title="Usuario inhabilitado por deuda">Deuda</span>
                        @endif
                        @if ($periodoFiltroId ?? null)
                            @if ($alumno->matriculas->isNotEmpty())
                                <span class="badge badge-success ml-1 align-middle" title="Matriculado en el período filtrado">
                                    <i class="fas fa-check-circle fa-xs"></i> Matriculado
                                </span>
                            @else
                                <span class="badge badge-secondary ml-1 align-middle" title="Aún no completa su matrícula en el período filtrado">
                                    <i class="fas fa-exclamation-circle fa-xs"></i> No matriculado
                                </span>
                            @endif
                        @endif
                        <ul>
                            <li> Trabajas:
                                @if ($alumno->trabajas === 1 || $alumno->trabajas === '1')
                                    Sí
                                @elseif ($alumno->trabajas === 0 || $alumno->trabajas === '0')
                                    No
                                @else
                                    {{ $alumno->trabajas }}
                                @endif
                            </li>
                            <li>Donde trabajas: {{ $alumno->donde_trabajas ?? 'NULL' }}</li>
                            <li>DNI: {{ $alumno->dni }}</li>
                        </ul>
                    </td>
                    <td>
                        <ul>
                            <li>{{ $alumno->programa->nombre }} - {{ $alumno->ciclo->nombre }}</li>
                            <li>{{ $alumno->email }}</li>
                            <li>Teléfono: {{ $alumno->numero }}</li>
                            <li>Referencia: {{ $alumno->numero_referencia }}</li>
                            <li>Fecha de nacimiento:
                                @php $fechaNacFmt = $alumno->fechaNacimientoResueltaFormateada(); @endphp
                                @if ($fechaNacFmt !== '')
                                    {{ $fechaNacFmt }}
                                    @if (!is_null($alumno->edad))
                                        <span class="font-weight-bold">({{ $alumno->edad }} años)</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </li>
                        </ul>
                    </td>
                    <td>
                        @php
                            $fotoAlumno = $alumno->user?->foto
                                ? asset('img/estudiantes/' . $alumno->user->foto)
                                : null;
                            $nombreCompleto = trim($alumno->apellidos . ', ' . $alumno->nombres);
                        @endphp
                        @if ($fotoAlumno)
                            <img src="{{ $fotoAlumno }}"
                                class="alumno-avatar-thumb js-open-photo-modal"
                                data-photo-src="{{ $fotoAlumno }}"
                                data-photo-name="{{ $nombreCompleto }}" loading="lazy"
                                decoding="async">
                        @else
                            <span class="alumno-avatar-empty" title="Sin foto">
                                <i class="fa fa-user"></i>
                            </span>
                        @endif
                    </td>
                    <td>
                        @php $matriculaVoucher = $alumno->matriculas->first(); @endphp
                        @if ($matriculaVoucher)
                            <div class="small font-weight-bold mb-1">{{ $matriculaVoucher->comprobante ?? '—' }}</div>
                            <form action="{{ route('matriculas.verificarVoucher', $matriculaVoucher->id) }}"
                                method="POST" class="d-inline js-toggle-verificado">
                                @csrf
                                <label class="mb-1 small" style="cursor:pointer;">
                                    <input type="checkbox" class="js-verificado-checkbox"
                                        {{ $matriculaVoucher->voucher_verificado ? 'checked' : '' }}>
                                    Verificado
                                </label>
                            </form>
                            <form action="{{ route('matriculas.enviarFicha', $matriculaVoucher->id) }}"
                                method="POST" class="d-inline js-enviar-ficha"
                                data-nombre="{{ $alumno->apellidos }}, {{ $alumno->nombres }}"
                                data-reenvio="{{ $matriculaVoucher->ficha_enviada_at ? '1' : '0' }}">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $matriculaVoucher->ficha_enviada_at ? 'btn-outline-primary' : 'btn-primary' }} d-block"
                                    {{ $matriculaVoucher->voucher_verificado ? '' : 'disabled' }}
                                    data-title-enabled="Enviar ficha de matrícula al alumno"
                                    data-title-disabled="Marca el voucher como verificado primero"
                                    title="{{ $matriculaVoucher->voucher_verificado ? 'Enviar ficha de matrícula al alumno' : 'Marca el voucher como verificado primero' }}">
                                    <i class="fa {{ $matriculaVoucher->ficha_enviada_at ? 'fa-redo' : 'fa-paper-plane' }} fa-xs"></i>
                                    {{ $matriculaVoucher->ficha_enviada_at ? 'Reenviar correo' : 'Enviar ficha' }}
                                </button>
                            </form>
                            @if ($matriculaVoucher->ficha_enviada_at)
                                <span class="text-muted small d-block" title="Última vez enviado">
                                    <i class="fa fa-check-double fa-xs"></i> Enviada el {{ $matriculaVoucher->ficha_enviada_at->format('d/m/Y H:i') }}
                                </span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @php
                            $carnetBaseParams = array_merge(
                                ['alumno' => $alumno->id],
                                array_filter(
                                    request()->only([
                                        'search',
                                        'search_page',
                                        'with_user',
                                        'programa_id',
                                        'ciclo_id',
                                    ]),
                                ),
                            );
                            $tieneFotoCarnet = !empty($alumno->user?->foto);
                        @endphp
                        <a href="{{ route('alumnos.edit', ['alumno' => $alumno->id]) }}"
                            class="btn btn-primary btn-sm" title="Editar">
                            <i class="fa fa-edit fa-sm"></i>
                        </a> |
                        <a href="{{ route('alumnos.show', ['alumno' => $alumno->id]) }}"
                            class="btn btn-info btn-sm" title="Ver registro completo">
                            <i class="fa fa-eye fa-sm"></i>
                        </a> |
                        @if ($tieneFotoCarnet)
                            <div class="btn-group" role="group">
                                <button type="button"
                                    class="btn btn-secondary btn-sm dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    title="Carnets">
                                    <i class="fa fa-id-card fa-sm"></i> Carnets
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item"
                                        href="{{ route('admin.alumnos.carnet', $carnetBaseParams) }}"
                                        target="_blank" rel="noopener noreferrer">
                                        <i class="fa fa-eye text-primary mr-2"></i> Ver ambos
                                    </a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.alumnos.carnet', array_merge($carnetBaseParams, ['auto_download' => 1, 'tipo' => 'estudiante'])) }}"
                                        target="carnetDownloadFrame" rel="noopener noreferrer">
                                        <i class="fa fa-download text-primary mr-2"></i> Descargar carnet
                                    </a>
                                    <a class="dropdown-item"
                                        href="{{ route('admin.alumnos.carnet', array_merge($carnetBaseParams, ['auto_download' => 1, 'tipo' => 'biblioteca'])) }}"
                                        target="carnetDownloadFrame" rel="noopener noreferrer">
                                        <i class="fa fa-download text-info mr-2"></i> Descargar biblioteca
                                    </a>
                                </div>
                            </div> |
                        @else
                            <button type="button" class="btn btn-secondary btn-sm" disabled
                                title="Para generar carnet, el registro debe tener foto">
                                <i class="fa fa-id-card fa-sm"></i> Carnets
                            </button> |
                        @endif
                        @if (!$alumno->user)
                            <a class="btn btn-success btn-sm relacionar-usuario"
                                data-alumno-id="{{ $alumno->id }}" title="Relacionar con Usuario">
                                <i class="fa fa-user fa-sm"></i>
                            </a>|
                        @endif
                        @php $matriculaActual = $alumno->matriculas->first(); @endphp
                        @role('super-admin')
                            @if($matriculaActual)
                                <form action="{{ route('matriculas.quitar', $matriculaActual->id) }}"
                                      method="POST" class="d-inline js-quitar-matricula"
                                      data-nombre="{{ $alumno->apellidos }}, {{ $alumno->nombres }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-warning btn-sm"
                                        title="Quitar matrícula del período actual (solo super-admin)">
                                        <i class="fa fa-user-times fa-sm"></i>
                                    </button>
                                </form> |
                            @endif
                        @endrole
                        <form id="deleteForm"
                            action="{{ route('alumnos.destroy', ['alumno' => $alumno->id]) }}"
                            method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#confirmDeleteModal" title="Eliminar">
                                <i class="fa fa-trash fa-sm"></i>
                            </button>
                        </form>

                        <!-- Modal de confirmación de eliminación -->
                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                            aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar
                                            Eliminación</h5>
                                        <button type="button" class="close" data-dismiss="modal"
                                            aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        ¿Estás seguro de que quieres eliminar este registro?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary"
                                            data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="document.getElementById('deleteForm').submit()">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @php $numeroRegistro++; @endphp
            @empty
                <tr>
                    <td colspan="6" class="text-center">No se encontraron alumnos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if (session('error'))
        <span class="text-danger text-sm">
            {{ session('error') }}
        </span>
    @endif
</div>
