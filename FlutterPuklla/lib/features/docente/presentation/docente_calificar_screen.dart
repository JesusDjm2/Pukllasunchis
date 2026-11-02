import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../application/docente_providers.dart';
import '../data/docente_calificar_data.dart';
import '../data/docente_repository.dart';

/// Pantalla nativa e interactiva de Calificaciones para Docentes.
/// Soporta cursos FID (con motor reactivo de rúbricas cualitativas 1-5 que calcula
/// en tiempo real la nota vigesimal y el nivel de logro) y cursos PPD.
class DocenteCalificarScreen extends ConsumerStatefulWidget {
  const DocenteCalificarScreen({
    super.key,
    required this.cursoId,
  });

  final int cursoId;

  @override
  ConsumerState<DocenteCalificarScreen> createState() => _DocenteCalificarScreenState();
}

class _DocenteCalificarScreenState extends ConsumerState<DocenteCalificarScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _busquedaController = TextEditingController();

  String _filtroTexto = '';
  String _filtroEstado = 'todos'; // 'todos', 'pendientes', 'completos'
  bool _mostrarInfoCompetencias = false;
  bool _guardando = false;

  // Estado local editable de notas para respuesta instantánea (offline-first reactivo)
  final Map<int, PeriodoRegistro> _notasP1 = {};
  final Map<int, PeriodoRegistro> _notasP2 = {};
  final Map<int, PeriodoRegistro> _notasP3 = {};
  final Map<int, PpdRegistro> _notasPpd = {};
  bool _datosInicializados = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    _busquedaController.dispose();
    super.dispose();
  }

  void _inicializarDatos(DocenteCalificarData data) {
    if (_datosInicializados) return;
    for (final a in data.alumnos) {
      if (a.p1 != null) _notasP1[a.id] = a.p1!;
      if (a.p2 != null) _notasP2[a.id] = a.p2!;
      if (a.p3 != null) _notasP3[a.id] = a.p3!;
      if (a.ppd != null) _notasPpd[a.id] = a.ppd!;
    }
    _datosInicializados = true;
  }

  // Actualiza la valoración cualitativa (1 a 5) de una competencia y recalcula al vuelo
  void _actualizarValoracionFid({
    required int alumnoId,
    required int periodoIndex, // 0: P1, 1: P2, 2: P3
    required int competenciaIndex, // 0, 1, 2
    required int valor, // 1..5
    required int totalCompetencias,
  }) {
    setState(() {
      final mapActual = switch (periodoIndex) {
        0 => _notasP1,
        1 => _notasP2,
        _ => _notasP3,
      };

      final actual = mapActual[alumnoId] ?? const PeriodoRegistro();

      int? v1 = actual.valoracion1;
      int? v2 = actual.valoracion2;
      int? v3 = actual.valoracion3;

      if (competenciaIndex == 0) {
        v1 = (v1 == valor) ? null : valor;
      } else if (competenciaIndex == 1) {
        v2 = (v2 == valor) ? null : valor;
      } else if (competenciaIndex == 2) {
        v3 = (v3 == valor) ? null : valor;
      }

      // Motor de cálculo reactivo
      final promedio = DocenteCalificarData.calcularPromedio([v1, v2, v3], totalCompetencias);
      final vigesimal = DocenteCalificarData.calcularValoracionVigesimal(promedio);
      final nivel = DocenteCalificarData.calcularNivelLogro(vigesimal);

      mapActual[alumnoId] = actual.copyWith(
        valoracion1: v1,
        valoracion2: v2,
        valoracion3: v3,
        valoracionCurso: promedio,
        calificacionSistema: vigesimal,
        calificacionCurso: nivel,
      );
    });
  }

  void _actualizarObservacionesFid({
    required int alumnoId,
    required int periodoIndex,
    required String obs,
  }) {
    final mapActual = switch (periodoIndex) {
      0 => _notasP1,
      1 => _notasP2,
      _ => _notasP3,
    };
    final actual = mapActual[alumnoId] ?? const PeriodoRegistro();
    mapActual[alumnoId] = actual.copyWith(observaciones: obs.trim().isEmpty ? null : obs.trim());
  }

  Future<void> _guardarFid(DocenteCalificarData data, int periodoIndex) async {
    final periodoClave = switch (periodoIndex) {
      0 => 'p1',
      1 => 'p2',
      _ => 'p3',
    };
    final nombrePeriodo = switch (periodoIndex) {
      0 => 'Parcial 1',
      1 => 'Parcial 2',
      _ => 'Desempeño',
    };

    final mapActual = switch (periodoIndex) {
      0 => _notasP1,
      1 => _notasP2,
      _ => _notasP3,
    };

    final payload = <Map<String, dynamic>>[];
    for (final a in data.alumnos) {
      final reg = mapActual[a.id];
      if (reg != null) {
        payload.add(reg.toJson(a.id));
      }
    }

    if (payload.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('No hay notas registradas para guardar en $nombrePeriodo.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _guardando = true);
    try {
      final repo = ref.read(docenteRepositoryProvider);
      await repo.guardarCalificacionesFid(
        cursoId: widget.cursoId,
        periodo: periodoClave,
        alumnos: payload,
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
              const SizedBox(width: 8),
              Expanded(child: Text('Calificaciones de $nombrePeriodo guardadas exitosamente.')),
            ],
          ),
          backgroundColor: const Color(0xFF10B981),
          behavior: SnackBarBehavior.floating,
        ),
      );
      ref.invalidate(docenteCalificacionesProvider(widget.cursoId));
      ref.invalidate(docenteCursosProvider);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al guardar: $e'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) setState(() => _guardando = false);
    }
  }

  Future<void> _guardarPpd(DocenteCalificarData data) async {
    final payload = <Map<String, dynamic>>[];
    for (final a in data.alumnos) {
      final reg = _notasPpd[a.id];
      if (reg != null) {
        payload.add(reg.toJson(id: a.id, ppdId: a.ppdId));
      }
    }

    if (payload.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('No hay calificaciones PPD registradas para guardar.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() => _guardando = true);
    try {
      final repo = ref.read(docenteRepositoryProvider);
      await repo.guardarCalificacionesPpd(
        cursoId: widget.cursoId,
        alumnos: payload,
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
              SizedBox(width: 8),
              Expanded(child: Text('Calificaciones PPD guardadas exitosamente.')),
            ],
          ),
          backgroundColor: Color(0xFF10B981),
          behavior: SnackBarBehavior.floating,
        ),
      );
      ref.invalidate(docenteCalificacionesProvider(widget.cursoId));
      ref.invalidate(docenteCursosProvider);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al guardar PPD: $e'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) setState(() => _guardando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final asyncCalificaciones = ref.watch(docenteCalificacionesProvider(widget.cursoId));

    return Scaffold(
      backgroundColor: isDark ? AppColors.darkBackground : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Registro de Calificaciones'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Recargar datos',
            onPressed: () {
              setState(() => _datosInicializados = false);
              ref.invalidate(docenteCalificacionesProvider(widget.cursoId));
            },
          ),
        ],
      ),
      body: asyncCalificaciones.when(
        loading: () => const Center(
          child: PukllaPreloader.inline(mensaje: 'Cargando nómina y competencias del curso...'),
        ),
        error: (err, _) => Center(
          child: ErrorState(
            message: 'No se pudo cargar la información de calificaciones: $err',
            onRetry: () => ref.refresh(docenteCalificacionesProvider(widget.cursoId)),
          ),
        ),
        data: (data) {
          _inicializarDatos(data);
          final curso = data.curso;
          final totalComps = data.competencias.length;

          return Column(
            children: [
              // Encabezado del Curso
              _CabeceraCurso(
                curso: curso,
                periodo: data.periodo,
                competencias: data.competencias,
                mostrarInfoCompetencias: _mostrarInfoCompetencias,
                onToggleInfo: () => setState(() => _mostrarInfoCompetencias = !_mostrarInfoCompetencias),
              ),

              // Pestañas de Períodos (para FID)
              if (!curso.esPpd) ...[
                Container(
                  color: isDark ? AppColors.darkSurface : Colors.white,
                  child: TabBar(
                    controller: _tabController,
                    labelColor: AppColors.gold,
                    unselectedLabelColor: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                    indicatorColor: AppColors.gold,
                    indicatorWeight: 3,
                    isScrollable: true,
                    tabs: const [
                      Tab(text: 'Parcial 1 (P1)'),
                      Tab(text: 'Parcial 2 (P2)'),
                      Tab(text: 'Desempeño (P3)'),
                      Tab(text: 'Consolidado'),
                    ],
                  ),
                ),
              ],

              // Barra de búsqueda y filtro
              _BarraFiltros(
                controller: _busquedaController,
                filtroEstado: _filtroEstado,
                onBusquedaChanged: (v) => setState(() => _filtroTexto = v.trim().toLowerCase()),
                onEstadoChanged: (st) => setState(() => _filtroEstado = st),
              ),

              // Contenido según FID o PPD
              Expanded(
                child: curso.esPpd
                    ? _VistaCalificacionesPpd(
                        data: data,
                        filtroTexto: _filtroTexto,
                        filtroEstado: _filtroEstado,
                        notasPpd: _notasPpd,
                        onGuardar: () => _guardarPpd(data),
                        guardando: _guardando,
                      )
                    : TabBarView(
                        controller: _tabController,
                        children: [
                          // Tab 0: Parcial 1
                          _ListaAlumnosFid(
                            data: data,
                            periodoIndex: 0,
                            periodoHabilitado: data.periodo.parcial1Habilitado,
                            filtroTexto: _filtroTexto,
                            filtroEstado: _filtroEstado,
                            notasMap: _notasP1,
                            onValoracionChanged: (alumnoId, compIdx, val) {
                              _actualizarValoracionFid(
                                alumnoId: alumnoId,
                                periodoIndex: 0,
                                competenciaIndex: compIdx,
                                valor: val,
                                totalCompetencias: totalComps,
                              );
                            },
                            onObservacionesChanged: (alumnoId, obs) {
                              _actualizarObservacionesFid(alumnoId: alumnoId, periodoIndex: 0, obs: obs);
                            },
                            onGuardar: () => _guardarFid(data, 0),
                            guardando: _guardando,
                          ),

                          // Tab 1: Parcial 2
                          _ListaAlumnosFid(
                            data: data,
                            periodoIndex: 1,
                            periodoHabilitado: data.periodo.parcial2Habilitado,
                            filtroTexto: _filtroTexto,
                            filtroEstado: _filtroEstado,
                            notasMap: _notasP2,
                            onValoracionChanged: (alumnoId, compIdx, val) {
                              _actualizarValoracionFid(
                                alumnoId: alumnoId,
                                periodoIndex: 1,
                                competenciaIndex: compIdx,
                                valor: val,
                                totalCompetencias: totalComps,
                              );
                            },
                            onObservacionesChanged: (alumnoId, obs) {
                              _actualizarObservacionesFid(alumnoId: alumnoId, periodoIndex: 1, obs: obs);
                            },
                            onGuardar: () => _guardarFid(data, 1),
                            guardando: _guardando,
                          ),

                          // Tab 2: Desempeño
                          _ListaAlumnosFid(
                            data: data,
                            periodoIndex: 2,
                            periodoHabilitado: data.periodo.parcial2Habilitado,
                            filtroTexto: _filtroTexto,
                            filtroEstado: _filtroEstado,
                            notasMap: _notasP3,
                            onValoracionChanged: (alumnoId, compIdx, val) {
                              _actualizarValoracionFid(
                                alumnoId: alumnoId,
                                periodoIndex: 2,
                                competenciaIndex: compIdx,
                                valor: val,
                                totalCompetencias: totalComps,
                              );
                            },
                            onObservacionesChanged: (alumnoId, obs) {
                              _actualizarObservacionesFid(alumnoId: alumnoId, periodoIndex: 2, obs: obs);
                            },
                            onGuardar: () => _guardarFid(data, 2),
                            guardando: _guardando,
                          ),

                          // Tab 3: Consolidado Final
                          _ConsolidadoFid(
                            data: data,
                            filtroTexto: _filtroTexto,
                            notasP1: _notasP1,
                            notasP2: _notasP2,
                            notasP3: _notasP3,
                          ),
                        ],
                      ),
              ),
            ],
          );
        },
      ),
    );
  }
}

// -------------------------------------------------------------
// CABECERA DEL CURSO CON COMPETENCIAS
// -------------------------------------------------------------
class _CabeceraCurso extends StatelessWidget {
  const _CabeceraCurso({
    required this.curso,
    required this.periodo,
    required this.competencias,
    required this.mostrarInfoCompetencias,
    required this.onToggleInfo,
  });

  final DocenteCalificarCursoInfo curso;
  final DocentePeriodoInfo periodo;
  final List<DocenteCompetenciaInfo> competencias;
  final bool mostrarInfoCompetencias;
  final VoidCallback onToggleInfo;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        border: Border(bottom: BorderSide(color: PukllaDashboardStyles.borderColor(context))),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  curso.nombre,
                  style: TextStyle(
                    fontSize: 15.5,
                    fontWeight: FontWeight.w800,
                    color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: curso.esPpd
                      ? const Color(0xFF8B5CF6).withValues(alpha: 0.15)
                      : AppColors.teal.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  curso.esPpd ? 'PPD' : 'FID',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: curso.esPpd ? const Color(0xFF8B5CF6) : AppColors.teal,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Wrap(
            spacing: 8,
            children: [
              if (curso.programa != null)
                Text(
                  curso.programa!,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                  ),
                ),
              if (curso.ciclo != null)
                Text(
                  '• Ciclo ${curso.ciclo}',
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                  ),
                ),
              if (periodo.nombre != null)
                Text(
                  '• ${periodo.nombre}',
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppColors.gold,
                  ),
                ),
            ],
          ),
          const SizedBox(height: 8),

          // Botón para desplegar detalle de competencias evaluadas
          PukllaScaleTap(
            onTap: onToggleInfo,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.psychology_outlined, size: 16, color: AppColors.gold),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Competencias asignadas (${competencias.length}): ${competencias.map((c) => c.nombre).join(" · ")}',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w600,
                        color: isDark ? AppColors.darkText : const Color(0xFF334155),
                      ),
                    ),
                  ),
                  Icon(
                    mostrarInfoCompetencias ? Icons.expand_less : Icons.expand_more,
                    size: 18,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                  ),
                ],
              ),
            ),
          ),

          if (mostrarInfoCompetencias) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.teal.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: AppColors.teal.withValues(alpha: 0.20)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  for (int i = 0; i < competencias.length; i++) ...[
                    Text(
                      'C${i + 1}: ${competencias[i].nombre}',
                      style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.tealDark),
                    ),
                    if (competencias[i].descripcion != null && competencias[i].descripcion!.isNotEmpty)
                      Padding(
                        padding: const EdgeInsets.only(top: 2, bottom: 6),
                        child: Text(
                          competencias[i].descripcion!,
                          style: TextStyle(fontSize: 10.5, color: isDark ? AppColors.darkMuted : const Color(0xFF475569)),
                        ),
                      ),
                  ],
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// -------------------------------------------------------------
// BARRA DE BÚSQUEDA Y FILTROS
// -------------------------------------------------------------
class _BarraFiltros extends StatelessWidget {
  const _BarraFiltros({
    required this.controller,
    required this.filtroEstado,
    required this.onBusquedaChanged,
    required this.onEstadoChanged,
  });

  final TextEditingController controller;
  final String filtroEstado;
  final ValueChanged<String> onBusquedaChanged;
  final ValueChanged<String> onEstadoChanged;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      color: isDark ? AppColors.darkSurface : Colors.white,
      child: Row(
        children: [
          Expanded(
            child: Container(
              height: 38,
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(8),
              ),
              child: TextField(
                controller: controller,
                onChanged: onBusquedaChanged,
                style: TextStyle(fontSize: 12.5, color: isDark ? AppColors.darkText : const Color(0xFF0F172A)),
                decoration: const InputDecoration(
                  hintText: 'Buscar por nombre o DNI...',
                  hintStyle: TextStyle(fontSize: 11.5, color: Color(0xFF94A3B8)),
                  prefixIcon: Icon(Icons.search, size: 16, color: AppColors.gold),
                  border: InputBorder.none,
                  contentPadding: EdgeInsets.symmetric(vertical: 9),
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'todos', label: Text('Todos', style: TextStyle(fontSize: 11))),
              ButtonSegment(value: 'pendientes', label: Text('Pendientes', style: TextStyle(fontSize: 11))),
            ],
            selected: {filtroEstado},
            onSelectionChanged: (set) => onEstadoChanged(set.first),
            style: ButtonStyle(
              visualDensity: VisualDensity.compact,
              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              padding: WidgetStateProperty.all(const EdgeInsets.symmetric(horizontal: 8)),
            ),
          ),
        ],
      ),
    );
  }
}

// -------------------------------------------------------------
// LISTADO INTERACTIVO DE ALUMNOS FID (POR PERÍODO)
// -------------------------------------------------------------
class _ListaAlumnosFid extends StatelessWidget {
  const _ListaAlumnosFid({
    required this.data,
    required this.periodoIndex,
    required this.periodoHabilitado,
    required this.filtroTexto,
    required this.filtroEstado,
    required this.notasMap,
    required this.onValoracionChanged,
    required this.onObservacionesChanged,
    required this.onGuardar,
    required this.guardando,
  });

  final DocenteCalificarData data;
  final int periodoIndex;
  final bool periodoHabilitado;
  final String filtroTexto;
  final String filtroEstado;
  final Map<int, PeriodoRegistro> notasMap;
  final void Function(int alumnoId, int compIdx, int valor) onValoracionChanged;
  final void Function(int alumnoId, String obs) onObservacionesChanged;
  final VoidCallback onGuardar;
  final bool guardando;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final totalComps = data.competencias.length;

    final alumnosFiltrados = data.alumnos.where((a) {
      if (filtroTexto.isNotEmpty) {
        final nom = a.nombreCompleto.toLowerCase();
        final dni = a.dni?.toLowerCase() ?? '';
        if (!nom.contains(filtroTexto) && !dni.contains(filtroTexto)) return false;
      }
      if (filtroEstado == 'pendientes') {
        final reg = notasMap[a.id];
        final tieneNota = reg != null && reg.calificacionSistema != null;
        if (tieneNota) return false;
      }
      return true;
    }).toList();

    // Contadores para la barra inferior
    int calificadosCount = 0;
    for (final a in data.alumnos) {
      final reg = notasMap[a.id];
      if (reg != null && reg.calificacionSistema != null) calificadosCount++;
    }

    return Column(
      children: [
        // Aviso si el período está cerrado
        if (!periodoHabilitado)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            color: const Color(0xFFFEF3C7),
            child: const Row(
              children: [
                Icon(Icons.info_outline, size: 16, color: Color(0xFFD97706)),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Este período se encuentra cerrado en el calendario académico institucional.',
                    style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: Color(0xFF92400E)),
                  ),
                ),
              ],
            ),
          ),

        // Lista de tarjetas de alumnos
        Expanded(
          child: alumnosFiltrados.isEmpty
              ? Center(
                  child: Text(
                    'No se encontraron alumnos para este criterio.',
                    style: TextStyle(color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                  ),
                )
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(14, 12, 14, 80),
                  itemCount: alumnosFiltrados.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (context, idx) {
                    final a = alumnosFiltrados[idx];
                    final reg = notasMap[a.id] ?? const PeriodoRegistro();

                    return _TarjetaAlumnoFid(
                      alumno: a,
                      registro: reg,
                      totalCompetencias: totalComps,
                      competencias: data.competencias,
                      periodoHabilitado: periodoHabilitado,
                      onValoracionChanged: (compIdx, val) => onValoracionChanged(a.id, compIdx, val),
                      onObservacionesChanged: (obs) => onObservacionesChanged(a.id, obs),
                    );
                  },
                ),
        ),

        // Barra inferior fija con botón de Guardar
        _BarraInferiorGuardar(
          calificadosCount: calificadosCount,
          totalAlumnos: data.alumnos.length,
          guardando: guardando,
          habilitado: periodoHabilitado,
          onGuardar: onGuardar,
        ),
      ],
    );
  }
}

// -------------------------------------------------------------
// TARJETA INTERACTIVA DE UN ALUMNO FID
// -------------------------------------------------------------
class _TarjetaAlumnoFid extends StatefulWidget {
  const _TarjetaAlumnoFid({
    required this.alumno,
    required this.registro,
    required this.totalCompetencias,
    required this.competencias,
    required this.periodoHabilitado,
    required this.onValoracionChanged,
    required this.onObservacionesChanged,
  });

  final DocenteAlumnoCalificacion alumno;
  final PeriodoRegistro registro;
  final int totalCompetencias;
  final List<DocenteCompetenciaInfo> competencias;
  final bool periodoHabilitado;
  final void Function(int compIdx, int valor) onValoracionChanged;
  final ValueChanged<String> onObservacionesChanged;

  @override
  State<_TarjetaAlumnoFid> createState() => _TarjetaAlumnoFidState();
}

class _TarjetaAlumnoFidState extends State<_TarjetaAlumnoFid> {
  bool _mostrarObs = false;
  late TextEditingController _obsController;

  @override
  void initState() {
    super.initState();
    _obsController = TextEditingController(text: widget.registro.observaciones ?? '');
  }

  @override
  void didUpdateWidget(covariant _TarjetaAlumnoFid oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.registro.observaciones != widget.registro.observaciones &&
        _obsController.text != (widget.registro.observaciones ?? '')) {
      _obsController.text = widget.registro.observaciones ?? '';
    }
  }

  @override
  void dispose() {
    _obsController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final a = widget.alumno;
    final reg = widget.registro;

    final tieneNota = reg.calificacionSistema != null;
    final nota = reg.calificacionSistema ?? 0;
    final esAprobado = nota >= 11;

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: tieneNota
              ? (esAprobado ? AppColors.teal.withValues(alpha: 0.35) : AppColors.danger.withValues(alpha: 0.35))
              : PukllaDashboardStyles.borderColor(context),
          width: tieneNota ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Fila del alumno: Avatar + Nombre + DNI + Badge Nota
          Row(
            children: [
              CircleAvatar(
                radius: 17,
                backgroundColor: AppColors.teal.withValues(alpha: 0.15),
                child: Text(
                  a.nombreCompleto.isNotEmpty ? a.nombreCompleto[0].toUpperCase() : '?',
                  style: const TextStyle(fontWeight: FontWeight.w800, color: AppColors.teal, fontSize: 13),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      a.nombreCompleto,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                      ),
                    ),
                    if (a.dni != null && a.dni!.isNotEmpty)
                      Text(
                        'DNI: ${a.dni}',
                        style: TextStyle(fontSize: 11, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                      ),
                  ],
                ),
              ),

              // Badge Resultado (Vigesimal + Nivel)
              if (tieneNota)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: esAprobado
                        ? const Color(0xFF10B981).withValues(alpha: 0.15)
                        : AppColors.danger.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: esAprobado ? const Color(0xFF10B981) : AppColors.danger,
                      width: 1,
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        '$nota / 20',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w900,
                          color: esAprobado ? const Color(0xFF10B981) : AppColors.danger,
                        ),
                      ),
                      if (reg.calificacionCurso != null)
                        Text(
                          reg.calificacionCurso!,
                          style: TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w700,
                            color: esAprobado ? const Color(0xFF10B981) : AppColors.danger,
                          ),
                        ),
                    ],
                  ),
                ),
            ],
          ),

          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 8),

          // Selector de Rúbricas Cualitativas (1 al 5) para cada competencia
          for (int cIdx = 0; cIdx < widget.totalCompetencias; cIdx++) ...[
            _FilaCompetenciaSelector(
              competenciaIndex: cIdx,
              competenciaNombre: cIdx < widget.competencias.length ? widget.competencias[cIdx].nombre : 'Comp. ${cIdx + 1}',
              valorSeleccionado: switch (cIdx) {
                0 => reg.valoracion1,
                1 => reg.valoracion2,
                _ => reg.valoracion3,
              },
              periodoHabilitado: widget.periodoHabilitado,
              onSelect: (val) => widget.onValoracionChanged(cIdx, val),
            ),
            const SizedBox(height: 6),
          ],

          // Botón y campo de observaciones
          Row(
            children: [
              PukllaScaleTap(
                onTap: () => setState(() => _mostrarObs = !_mostrarObs),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      _mostrarObs ? Icons.comment_rounded : Icons.chat_bubble_outline_rounded,
                      size: 14,
                      color: (reg.observaciones != null && reg.observaciones!.isNotEmpty)
                          ? AppColors.gold
                          : (isDark ? AppColors.darkMuted : const Color(0xFF94A3B8)),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      (reg.observaciones != null && reg.observaciones!.isNotEmpty)
                          ? 'Editar observación'
                          : 'Agregar observación',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: (reg.observaciones != null && reg.observaciones!.isNotEmpty)
                            ? AppColors.gold
                            : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                      ),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              if (reg.valoracionCurso != null)
                Text(
                  'Prom: ${reg.valoracionCurso!.toStringAsFixed(2)}',
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                  ),
                ),
            ],
          ),

          if (_mostrarObs) ...[
            const SizedBox(height: 8),
            TextField(
              controller: _obsController,
              onChanged: widget.onObservacionesChanged,
              maxLines: 2,
              style: TextStyle(fontSize: 12, color: isDark ? AppColors.darkText : const Color(0xFF0F172A)),
              decoration: InputDecoration(
                hintText: 'Escriba una observación o recomendación pedagógica...',
                hintStyle: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                filled: true,
                fillColor: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(8),
                  borderSide: BorderSide(color: PukllaDashboardStyles.borderColor(context)),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// -------------------------------------------------------------
// FILA SELECTORA DE VALORACIÓN CUALITATIVA (1 A 5)
// -------------------------------------------------------------
class _FilaCompetenciaSelector extends StatelessWidget {
  const _FilaCompetenciaSelector({
    required this.competenciaIndex,
    required this.competenciaNombre,
    required this.valorSeleccionado,
    required this.periodoHabilitado,
    required this.onSelect,
  });

  final int competenciaIndex;
  final String competenciaNombre;
  final int? valorSeleccionado;
  final bool periodoHabilitado;
  final ValueChanged<int> onSelect;

  static const _etiquetas = {
    1: 'Previo',
    2: 'Inicio',
    3: 'Proceso',
    4: 'Logrado',
    5: 'Destacado',
  };

  static const _colores = {
    1: Color(0xFFEF4444),
    2: Color(0xFFF59E0B),
    3: Color(0xFF3B82F6),
    4: Color(0xFF0D9488),
    5: Color(0xFFD97706),
  };

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'C${competenciaIndex + 1} - $competenciaNombre',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
          ),
        ),
        const SizedBox(height: 4),
        Row(
          children: [
            for (int v = 1; v <= 5; v++) ...[
              Expanded(
                child: Padding(
                  padding: EdgeInsets.only(right: v < 5 ? 4 : 0),
                  child: PukllaScaleTap(
                    onTap: periodoHabilitado ? () => onSelect(v) : () {},
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 6),
                      decoration: BoxDecoration(
                        color: valorSeleccionado == v
                            ? (_colores[v] ?? AppColors.teal)
                            : (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9)),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(
                          color: valorSeleccionado == v
                              ? (_colores[v] ?? AppColors.teal)
                              : PukllaDashboardStyles.borderColor(context),
                        ),
                      ),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            '$v',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w900,
                              color: valorSeleccionado == v
                                  ? Colors.white
                                  : (isDark ? AppColors.darkText : const Color(0xFF334155)),
                            ),
                          ),
                          Text(
                            _etiquetas[v] ?? '',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 8.5,
                              fontWeight: FontWeight.w700,
                              color: valorSeleccionado == v
                                  ? Colors.white.withValues(alpha: 0.9)
                                  : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ],
        ),
      ],
    );
  }
}

// -------------------------------------------------------------
// VISTA CONSOLIDADA DE NOTAS FID (P1, P2, P3 Y NOTA FINAL)
// -------------------------------------------------------------
class _ConsolidadoFid extends StatelessWidget {
  const _ConsolidadoFid({
    required this.data,
    required this.filtroTexto,
    required this.notasP1,
    required this.notasP2,
    required this.notasP3,
  });

  final DocenteCalificarData data;
  final String filtroTexto;
  final Map<int, PeriodoRegistro> notasP1;
  final Map<int, PeriodoRegistro> notasP2;
  final Map<int, PeriodoRegistro> notasP3;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    final alumnosFiltrados = data.alumnos.where((a) {
      if (filtroTexto.isEmpty) return true;
      final nom = a.nombreCompleto.toLowerCase();
      final dni = a.dni?.toLowerCase() ?? '';
      return nom.contains(filtroTexto) || dni.contains(filtroTexto);
    }).toList();

    return ListView.separated(
      padding: const EdgeInsets.all(14),
      itemCount: alumnosFiltrados.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, idx) {
        final a = alumnosFiltrados[idx];
        final p1 = notasP1[a.id]?.calificacionSistema;
        final p2 = notasP2[a.id]?.calificacionSistema;
        final p3 = notasP3[a.id]?.calificacionSistema;

        // Cálculo de promedio general si tiene notas
        final notasValidas = [p1, p2, p3].where((n) => n != null).cast<int>().toList();
        final promedio = notasValidas.isNotEmpty
            ? (notasValidas.fold<int>(0, (prev, n) => prev + n) / notasValidas.length).round()
            : null;

        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: isDark ? AppColors.darkSurface : Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      a.nombreCompleto,
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w700,
                        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                      ),
                    ),
                    if (a.dni != null)
                      Text(
                        'DNI: ${a.dni}',
                        style: TextStyle(fontSize: 11, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                      ),
                  ],
                ),
              ),

              // Badges P1, P2, P3
              _BadgeFase(etiqueta: 'P1', nota: p1),
              const SizedBox(width: 6),
              _BadgeFase(etiqueta: 'P2', nota: p2),
              const SizedBox(width: 6),
              _BadgeFase(etiqueta: 'P3', nota: p3),
              const SizedBox(width: 8),

              // Nota Final
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                decoration: BoxDecoration(
                  color: promedio != null
                      ? (promedio >= 11
                          ? const Color(0xFF10B981).withValues(alpha: 0.15)
                          : AppColors.danger.withValues(alpha: 0.15))
                      : (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9)),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  promedio != null ? '$promedio' : '-',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w900,
                    color: promedio != null
                        ? (promedio >= 11 ? const Color(0xFF10B981) : AppColors.danger)
                        : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _BadgeFase extends StatelessWidget {
  const _BadgeFase({required this.etiqueta, required this.nota});

  final String etiqueta;
  final int? nota;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      width: 32,
      padding: const EdgeInsets.symmetric(vertical: 4),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
      ),
      child: Column(
        children: [
          Text(etiqueta, style: const TextStyle(fontSize: 8, fontWeight: FontWeight.w700, color: Color(0xFF94A3B8))),
          Text(
            nota != null ? '$nota' : '-',
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              color: isDark ? AppColors.darkText : const Color(0xFF334155),
            ),
          ),
        ],
      ),
    );
  }
}

// -------------------------------------------------------------
// VISTA DE CALIFICACIONES PPD
// -------------------------------------------------------------
class _VistaCalificacionesPpd extends StatelessWidget {
  const _VistaCalificacionesPpd({
    required this.data,
    required this.filtroTexto,
    required this.filtroEstado,
    required this.notasPpd,
    required this.onGuardar,
    required this.guardando,
  });

  final DocenteCalificarData data;
  final String filtroTexto;
  final String filtroEstado;
  final Map<int, PpdRegistro> notasPpd;
  final VoidCallback onGuardar;
  final bool guardando;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    final alumnosFiltrados = data.alumnos.where((a) {
      if (filtroTexto.isNotEmpty) {
        final nom = a.nombreCompleto.toLowerCase();
        final dni = a.dni?.toLowerCase() ?? '';
        if (!nom.contains(filtroTexto) && !dni.contains(filtroTexto)) return false;
      }
      return true;
    }).toList();

    int calificadosCount = 0;
    for (final a in data.alumnos) {
      if (notasPpd[a.id]?.calificacionSistema != null) calificadosCount++;
    }

    return Column(
      children: [
        Expanded(
          child: ListView.separated(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 80),
            itemCount: alumnosFiltrados.length,
            separatorBuilder: (_, __) => const SizedBox(height: 10),
            itemBuilder: (context, idx) {
              final a = alumnosFiltrados[idx];
              final reg = notasPpd[a.id] ?? const PpdRegistro();

              final tieneNota = reg.calificacionSistema != null;
              final nota = reg.calificacionSistema ?? 0;
              final esAprobado = nota >= 11;

              return Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: isDark ? AppColors.darkSurface : Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 16,
                          backgroundColor: const Color(0xFF8B5CF6).withValues(alpha: 0.15),
                          child: Text(
                            a.nombreCompleto.isNotEmpty ? a.nombreCompleto[0].toUpperCase() : '?',
                            style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF8B5CF6), fontSize: 12),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                a.nombreCompleto,
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                                ),
                              ),
                              if (a.dni != null)
                                Text('DNI: ${a.dni}', style: TextStyle(fontSize: 11, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B))),
                            ],
                          ),
                        ),
                        if (tieneNota)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: esAprobado
                                  ? const Color(0xFF10B981).withValues(alpha: 0.15)
                                  : AppColors.danger.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              '$nota / 20',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w900,
                                color: esAprobado ? const Color(0xFF10B981) : AppColors.danger,
                              ),
                            ),
                          ),
                      ],
                    ),
                    if (reg.observaciones != null && reg.observaciones!.isNotEmpty) ...[
                      const SizedBox(height: 6),
                      Text(
                        'Obs: ${reg.observaciones}',
                        style: TextStyle(fontSize: 11, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                      ),
                    ],
                  ],
                ),
              );
            },
          ),
        ),

        _BarraInferiorGuardar(
          calificadosCount: calificadosCount,
          totalAlumnos: data.alumnos.length,
          guardando: guardando,
          habilitado: true,
          onGuardar: onGuardar,
        ),
      ],
    );
  }
}

// -------------------------------------------------------------
// BARRA INFERIOR CON CONTADOR Y BOTÓN DE GUARDADO
// -------------------------------------------------------------
class _BarraInferiorGuardar extends StatelessWidget {
  const _BarraInferiorGuardar({
    required this.calificadosCount,
    required this.totalAlumnos,
    required this.guardando,
    required this.habilitado,
    required this.onGuardar,
  });

  final int calificadosCount;
  final int totalAlumnos;
  final bool guardando;
  final bool habilitado;
  final VoidCallback onGuardar;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        border: Border(top: BorderSide(color: PukllaDashboardStyles.borderColor(context))),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.05),
            blurRadius: 10,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'Avance del Curso',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF94A3B8)),
                ),
                Text(
                  '$calificadosCount de $totalAlumnos calificados',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                  ),
                ),
              ],
            ),
            const SizedBox(width: 16),
            Expanded(
              child: PukllaScaleTap(
                onTap: (guardando || !habilitado) ? () {} : onGuardar,
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  decoration: BoxDecoration(
                    gradient: habilitado
                        ? const LinearGradient(colors: [AppColors.gold, Color(0xFFD97706)])
                        : null,
                    color: !habilitado ? const Color(0xFFCBD5E1) : null,
                    borderRadius: BorderRadius.circular(10),
                    boxShadow: habilitado
                        ? [
                            BoxShadow(
                              color: AppColors.gold.withValues(alpha: 0.30),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ]
                        : null,
                  ),
                  child: Center(
                    child: guardando
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.save_rounded, size: 18, color: Colors.white),
                              SizedBox(width: 6),
                              Text(
                                'Guardar Calificaciones',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                  letterSpacing: 0.2,
                                ),
                              ),
                            ],
                          ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
