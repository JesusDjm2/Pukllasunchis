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

  void _actualizarNotaPpd({
    required int alumnoId,
    required int compIdx,
    required String campo,
    required int? valor,
    required int totalCompetencias,
  }) {
    setState(() {
      final actual = _notasPpd[alumnoId] ?? const PpdRegistro();
      _notasPpd[alumnoId] = actual.conNota(
        compIdx: compIdx,
        campo: campo,
        valor: valor,
        totalCompetencias: totalCompetencias,
      );
    });
  }

  void _actualizarObservacionesPpd({
    required int alumnoId,
    required String obs,
  }) {
    final actual = _notasPpd[alumnoId] ?? const PpdRegistro();
    _notasPpd[alumnoId] = actual.copyWith(
      observaciones: obs.trim().isEmpty ? null : obs.trim(),
    );
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
                        totalCompetencias: totalComps,
                        onNotaChanged: (alumnoId, compIdx, campo, valor) {
                          _actualizarNotaPpd(
                            alumnoId: alumnoId,
                            compIdx: compIdx,
                            campo: campo,
                            valor: valor,
                            totalCompetencias: totalComps,
                          );
                        },
                        onObservacionesChanged: (alumnoId, obs) {
                          _actualizarObservacionesPpd(alumnoId: alumnoId, obs: obs);
                        },
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
  late TextEditingController _obsController;
  final FocusNode _obsFocusNode = FocusNode();

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
    _obsFocusNode.dispose();
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

          // Observaciones Pedagógicas (Siempre visibles y editables directamente)
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: (reg.observaciones != null && reg.observaciones!.trim().isNotEmpty)
                    ? AppColors.gold.withValues(alpha: isDark ? 0.45 : 0.35)
                    : PukllaDashboardStyles.borderColor(context),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.edit_note_rounded, size: 16, color: AppColors.gold),
                    const SizedBox(width: 6),
                    Text(
                      'Observación Pedagógica',
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                        color: isDark ? AppColors.darkText : const Color(0xFF334155),
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
                    const SizedBox(width: 8),
                    PukllaScaleTap(
                      onTap: () => _obsFocusNode.requestFocus(),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppColors.gold.withValues(alpha: isDark ? 0.25 : 0.15),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.edit, size: 11, color: AppColors.gold),
                            const SizedBox(width: 4),
                            Text(
                              (reg.observaciones != null && reg.observaciones!.trim().isNotEmpty)
                                  ? 'Editar observación'
                                  : 'Escribir observación',
                              style: const TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                                color: AppColors.gold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                TextField(
                  controller: _obsController,
                  focusNode: _obsFocusNode,
                  onChanged: widget.onObservacionesChanged,
                  maxLines: 2,
                  style: TextStyle(fontSize: 12, color: isDark ? AppColors.darkText : const Color(0xFF0F172A)),
                  decoration: InputDecoration(
                    hintText: 'Escriba una observación o recomendación pedagógica...',
                    hintStyle: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    filled: true,
                    fillColor: isDark ? AppColors.darkSurface : Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide(color: PukllaDashboardStyles.borderColor(context)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide(color: PukllaDashboardStyles.borderColor(context)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: AppColors.gold, width: 1.2),
                    ),
                  ),
                ),
              ],
            ),
          ),
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
// VISTA DE CALIFICACIONES PPD (Tarjetas Ergonómicas + Matriz Completa)
// -------------------------------------------------------------
class _VistaCalificacionesPpd extends StatefulWidget {
  const _VistaCalificacionesPpd({
    required this.data,
    required this.filtroTexto,
    required this.filtroEstado,
    required this.notasPpd,
    required this.totalCompetencias,
    required this.onNotaChanged,
    required this.onObservacionesChanged,
    required this.onGuardar,
    required this.guardando,
  });

  final DocenteCalificarData data;
  final String filtroTexto;
  final String filtroEstado;
  final Map<int, PpdRegistro> notasPpd;
  final int totalCompetencias;
  final void Function(int alumnoId, int compIdx, String campo, int? valor) onNotaChanged;
  final void Function(int alumnoId, String obs) onObservacionesChanged;
  final VoidCallback onGuardar;
  final bool guardando;

  @override
  State<_VistaCalificacionesPpd> createState() => _VistaCalificacionesPpdState();
}

class _VistaCalificacionesPpdState extends State<_VistaCalificacionesPpd> {
  bool _mostrarTabla = false;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    final alumnosFiltrados = widget.data.alumnos.where((a) {
      if (widget.filtroTexto.isNotEmpty) {
        final nom = a.nombreCompleto.toLowerCase();
        final dni = a.dni?.toLowerCase() ?? '';
        if (!nom.contains(widget.filtroTexto) && !dni.contains(widget.filtroTexto)) return false;
      }
      return true;
    }).toList();

    int calificadosCount = 0;
    for (final a in widget.data.alumnos) {
      if (widget.notasPpd[a.id]?.calificacionSistema != null) calificadosCount++;
    }

    return Column(
      children: [
        // Selector de Modo de Visualización (Tarjetas vs Tabla Matriz)
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            color: isDark ? AppColors.darkSurface : const Color(0xFFF8FAFC),
            border: Border(bottom: BorderSide(color: PukllaDashboardStyles.borderColor(context))),
          ),
          child: Row(
            children: [
              Text(
                'Visualización:',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                ),
              ),
              const SizedBox(width: 10),
              _BotonModoVista(
                icono: Icons.view_agenda_rounded,
                etiqueta: 'Tarjetas',
                activo: !_mostrarTabla,
                onTap: () => setState(() => _mostrarTabla = false),
              ),
              const SizedBox(width: 8),
              _BotonModoVista(
                icono: Icons.table_chart_rounded,
                etiqueta: 'Tabla Matriz',
                activo: _mostrarTabla,
                onTap: () => setState(() => _mostrarTabla = true),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF7E22CE).withValues(alpha: isDark ? 0.25 : 0.12),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  '${widget.totalCompetencias} ${widget.totalCompetencias == 1 ? "competencia" : "competencias"}',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF7E22CE),
                  ),
                ),
              ),
            ],
          ),
        ),

        // Cuerpo: Tarjetas o Tabla
        Expanded(
          child: alumnosFiltrados.isEmpty
              ? const Center(
                  child: Padding(
                    padding: EdgeInsets.all(24),
                    child: Text('No hay estudiantes que coincidan con la búsqueda.'),
                  ),
                )
              : _mostrarTabla
                  ? _TablaMatrizPpd(
                      alumnos: alumnosFiltrados,
                      competencias: widget.data.competencias,
                      totalCompetencias: widget.totalCompetencias,
                      notasPpd: widget.notasPpd,
                      onNotaChanged: widget.onNotaChanged,
                      onObservacionesChanged: widget.onObservacionesChanged,
                    )
                  : ListView.separated(
                      padding: const EdgeInsets.fromLTRB(14, 12, 14, 24),
                      itemCount: alumnosFiltrados.length,
                      separatorBuilder: (_, __) => const SizedBox(height: 12),
                      itemBuilder: (context, idx) {
                        final a = alumnosFiltrados[idx];
                        final reg = widget.notasPpd[a.id] ?? const PpdRegistro();

                        return _TarjetaAlumnoPpd(
                          alumno: a,
                          registro: reg,
                          competencias: widget.data.competencias,
                          totalCompetencias: widget.totalCompetencias,
                          onNotaChanged: (compIdx, campo, val) =>
                              widget.onNotaChanged(a.id, compIdx, campo, val),
                          onObservacionesChanged: (obs) =>
                              widget.onObservacionesChanged(a.id, obs),
                        );
                      },
                    ),
        ),

        // Barra inferior de guardado
        _BarraInferiorGuardar(
          calificadosCount: calificadosCount,
          totalAlumnos: widget.data.alumnos.length,
          guardando: widget.guardando,
          habilitado: true,
          onGuardar: widget.onGuardar,
        ),
      ],
    );
  }
}

class _BotonModoVista extends StatelessWidget {
  const _BotonModoVista({
    required this.icono,
    required this.etiqueta,
    required this.activo,
    required this.onTap,
  });

  final IconData icono;
  final String etiqueta;
  final bool activo;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: activo
              ? const Color(0xFF7E22CE)
              : (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFE2E8F0)),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icono,
              size: 14,
              color: activo ? Colors.white : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
            ),
            const SizedBox(width: 5),
            Text(
              etiqueta,
              style: TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w700,
                color: activo ? Colors.white : (isDark ? AppColors.darkMuted : const Color(0xFF475569)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TarjetaAlumnoPpd extends StatelessWidget {
  const _TarjetaAlumnoPpd({
    required this.alumno,
    required this.registro,
    required this.competencias,
    required this.totalCompetencias,
    required this.onNotaChanged,
    required this.onObservacionesChanged,
  });

  final DocenteAlumnoCalificacion alumno;
  final PpdRegistro registro;
  final List<DocenteCompetenciaInfo> competencias;
  final int totalCompetencias;
  final void Function(int compIdx, String campo, int? val) onNotaChanged;
  final ValueChanged<String> onObservacionesChanged;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final a = alumno;
    final reg = registro;

    final tieneNota = reg.calificacionSistema != null;
    final nota = reg.calificacionSistema ?? 0;
    final esAprobado = nota >= 11;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: tieneNota
              ? (esAprobado ? const Color(0xFF10B981).withValues(alpha: 0.35) : AppColors.danger.withValues(alpha: 0.35))
              : PukllaDashboardStyles.borderColor(context),
          width: tieneNota ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Cabecera Alumno + Badges de Resultado
          Row(
            children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: const Color(0xFF7E22CE).withValues(alpha: 0.15),
                child: Text(
                  a.nombreCompleto.isNotEmpty ? a.nombreCompleto[0].toUpperCase() : '?',
                  style: const TextStyle(fontWeight: FontWeight.w800, color: Color(0xFF7E22CE), fontSize: 13),
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
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                      ),
                    ),
                    Row(
                      children: [
                        if (a.dni != null)
                          Text('DNI: ${a.dni}', style: TextStyle(fontSize: 11, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B))),
                        if (a.esInhabilitado) ...[
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                            decoration: BoxDecoration(color: AppColors.danger.withValues(alpha: 0.15), borderRadius: BorderRadius.circular(4)),
                            child: const Text('Inhabilitado', style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: AppColors.danger)),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),

              // Badges Sistema / Curso / Desempeño
              Wrap(
                spacing: 5,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 4),
                    decoration: BoxDecoration(
                      color: tieneNota
                          ? (esAprobado ? const Color(0xFF10B981).withValues(alpha: 0.15) : AppColors.danger.withValues(alpha: 0.15))
                          : (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9)),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: tieneNota
                            ? (esAprobado ? const Color(0xFF10B981) : AppColors.danger)
                            : PukllaDashboardStyles.borderColor(context),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          tieneNota ? '$nota / 20' : '- / 20',
                          style: TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w900,
                            color: tieneNota
                                ? (esAprobado ? const Color(0xFF10B981) : AppColors.danger)
                                : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                          ),
                        ),
                        Text(
                          reg.calificacionCurso != null ? 'Curso: ${reg.calificacionCurso!.toStringAsFixed(2)}' : 'Sin calif.',
                          style: TextStyle(
                            fontSize: 9.5,
                            fontWeight: FontWeight.w700,
                            color: tieneNota
                                ? (esAprobado ? const Color(0xFF10B981) : AppColors.danger)
                                : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),

          const SizedBox(height: 12),
          Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
          const SizedBox(height: 10),

          // Secciones de Competencias
          for (int cIdx = 0; cIdx < totalCompetencias; cIdx++) ...[
            _CompetenciaCardPpd(
              compIndex: cIdx,
              competenciaNombre: cIdx < competencias.length ? competencias[cIdx].nombre : 'Competencia ${cIdx + 1}',
              participacion: reg.getParticipacion(cIdx),
              actividad: reg.getActividad(cIdx),
              promedioProceso: reg.getPromedioProceso(cIdx),
              autoevaluacion: reg.getAutoevaluacion(cIdx),
              evaluacion: reg.getEvaluacion(cIdx),
              promedioFinal: reg.getPromedioFinal(cIdx),
              valoracion: reg.getValoracion(cIdx),
              onNotaChanged: (campo, val) => onNotaChanged(cIdx, campo, val),
            ),
            const SizedBox(height: 10),
          ],

          // Observaciones (Siempre visible y editable directamente)
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: (reg.observaciones != null && reg.observaciones!.trim().isNotEmpty)
                    ? AppColors.gold.withValues(alpha: isDark ? 0.45 : 0.35)
                    : PukllaDashboardStyles.borderColor(context),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(Icons.edit_note_rounded, size: 16, color: AppColors.gold),
                    const SizedBox(width: 6),
                    Text(
                      'Observación Pedagógica (PPD)',
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                        color: isDark ? AppColors.darkText : const Color(0xFF334155),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                _PpdObservacionInput(
                  observacionInicial: reg.observaciones,
                  onChanged: onObservacionesChanged,
                  hint: 'Observaciones para el estudiante (opcional)...',
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _CompetenciaCardPpd extends StatelessWidget {
  const _CompetenciaCardPpd({
    required this.compIndex,
    required this.competenciaNombre,
    required this.participacion,
    required this.actividad,
    required this.promedioProceso,
    required this.autoevaluacion,
    required this.evaluacion,
    required this.promedioFinal,
    required this.valoracion,
    required this.onNotaChanged,
  });

  final int compIndex;
  final String competenciaNombre;
  final int? participacion;
  final int? actividad;
  final int? promedioProceso;
  final int? autoevaluacion;
  final int? evaluacion;
  final int? promedioFinal;
  final int? valoracion;
  final void Function(String campo, int? val) onNotaChanged;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
      ),
      padding: const EdgeInsets.all(10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Título de la Competencia + Valoración Chip
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  competenciaNombre,
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w800,
                    color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: isDark ? 0.25 : 0.15),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(
                    color: const Color(0xFF10B981).withValues(alpha: isDark ? 0.5 : 0.3),
                  ),
                ),
                child: Text(
                  valoracion != null ? 'Valoración: $valoracion' : 'Valoración: -',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF10B981),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),

          // Bloque 1: Productos de Proceso 40% (Naranja / Ámbar)
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFE5973A).withValues(alpha: isDark ? 0.15 : 0.08),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFFE5973A).withValues(alpha: isDark ? 0.35 : 0.25)),
            ),
            child: Row(
              children: [
                const SizedBox(
                  width: 90,
                  child: Text(
                    'Proceso 40%',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFFD97706)),
                  ),
                ),
                _InputItem(
                  etiqueta: 'Part.',
                  valor: participacion,
                  onChanged: (v) => onNotaChanged('participacion', v),
                ),
                const SizedBox(width: 8),
                _InputItem(
                  etiqueta: 'Activ.',
                  valor: actividad,
                  onChanged: (v) => onNotaChanged('actividad', v),
                ),
                const Spacer(),
                _ChipPromedio(
                  etiqueta: 'Prom.',
                  valor: promedioProceso,
                  color: const Color(0xFFD97706),
                ),
              ],
            ),
          ),
          const SizedBox(height: 6),

          // Bloque 2: Producto Final 60% (Durazno / Peach)
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFFFD39F).withValues(alpha: isDark ? 0.20 : 0.18),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFFF59E0B).withValues(alpha: isDark ? 0.40 : 0.30)),
            ),
            child: Row(
              children: [
                const SizedBox(
                  width: 90,
                  child: Text(
                    'Final 60%',
                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFFB45309)),
                  ),
                ),
                _InputItem(
                  etiqueta: 'Auto. 40%',
                  valor: autoevaluacion,
                  onChanged: (v) => onNotaChanged('autoevaluacion', v),
                ),
                const SizedBox(width: 8),
                _InputItem(
                  etiqueta: 'Eval. 60%',
                  valor: evaluacion,
                  onChanged: (v) => onNotaChanged('evaluacion', v),
                ),
                const Spacer(),
                _ChipPromedio(
                  etiqueta: 'Prom.',
                  valor: promedioFinal,
                  color: const Color(0xFFB45309),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _InputItem extends StatelessWidget {
  const _InputItem({
    required this.etiqueta,
    required this.valor,
    required this.onChanged,
  });

  final String etiqueta;
  final int? valor;
  final ValueChanged<int?> onChanged;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Text(
          etiqueta,
          style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
        ),
        const SizedBox(height: 2),
        _PpdNotaInput(valorInicial: valor, onChanged: onChanged),
      ],
    );
  }
}

class _ChipPromedio extends StatelessWidget {
  const _ChipPromedio({
    required this.etiqueta,
    required this.valor,
    required this.color,
  });

  final String etiqueta;
  final int? valor;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: color.withValues(alpha: 0.4)),
      ),
      child: Column(
        children: [
          Text(etiqueta, style: TextStyle(fontSize: 8.5, fontWeight: FontWeight.w700, color: color)),
          Text(
            valor != null ? '$valor' : '-',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: color),
          ),
        ],
      ),
    );
  }
}

// -------------------------------------------------------------
// TABLA MATRIZ COMPLETA DE CALIFICACIONES PPD
// -------------------------------------------------------------
class _TablaMatrizPpd extends StatelessWidget {
  const _TablaMatrizPpd({
    required this.alumnos,
    required this.competencias,
    required this.totalCompetencias,
    required this.notasPpd,
    required this.onNotaChanged,
    required this.onObservacionesChanged,
  });

  final List<DocenteAlumnoCalificacion> alumnos;
  final List<DocenteCompetenciaInfo> competencias;
  final int totalCompetencias;
  final Map<int, PpdRegistro> notasPpd;
  final void Function(int alumnoId, int compIdx, String campo, int? valor) onNotaChanged;
  final void Function(int alumnoId, String obs) onObservacionesChanged;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: SingleChildScrollView(
        scrollDirection: Axis.vertical,
        padding: const EdgeInsets.fromLTRB(14, 12, 14, 80),
        child: Container(
          decoration: BoxDecoration(
            color: isDark ? AppColors.darkSurface : Colors.white,
            border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
            borderRadius: BorderRadius.circular(10),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.05),
                blurRadius: 10,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Nivel 1 de Cabecera
              _construirNivel1(isDark),
              // Nivel 2 de Cabecera (Nombre de Competencias)
              _construirNivel2(isDark),
              // Nivel 3 de Cabecera (Sub-columnas)
              _construirNivel3(isDark),

              // Filas de Alumnos
              for (int i = 0; i < alumnos.length; i++) ...[
                _construirFilaAlumno(alumnos[i], i, isDark),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _construirNivel1(bool isDark) {
    return Container(
      decoration: const BoxDecoration(
        color: Color(0xFF1E293B),
        borderRadius: BorderRadius.vertical(top: Radius.circular(9)),
      ),
      child: Row(
        children: [
          _celdaCabecera('#', 40, Colors.white),
          _celdaCabecera('Alumno', 190, Colors.white),
          for (int c = 0; c < totalCompetencias; c++) ...[
            _celdaCabecera('Productos de Proceso 40%', 156, const Color(0xFFE5973A)),
            _celdaCabecera('Producto Final 60%', 156, const Color(0xFFFFD39F), textColor: const Color(0xFF78350F)),
            _celdaCabecera('Promedios Generales', 156, const Color(0xFF10B981)),
          ],
          _celdaCabecera('Nivel', 65, const Color(0xFFF59E0B)),
          _celdaCabecera('Curso', 75, const Color(0xFFF59E0B)),
          _celdaCabecera('Sistema', 75, const Color(0xFFF59E0B)),
          _celdaCabecera('Observaciones', 250, Colors.white),
        ],
      ),
    );
  }

  Widget _construirNivel2(bool isDark) {
    return Container(
      color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
      child: Row(
        children: [
          _celdaCabecera('', 40, isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
          _celdaCabecera('', 190, isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
          for (int c = 0; c < totalCompetencias; c++) ...[
            _celdaCabecera(
              c < competencias.length ? competencias[c].nombre : 'Competencia ${c + 1}',
              468,
              isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
              textColor: isDark ? Colors.white : const Color(0xFF0F172A),
            ),
          ],
          _celdaCabecera('', 65, isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
          _celdaCabecera('', 75, isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
          _celdaCabecera('', 75, isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
          _celdaCabecera('', 250, isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
        ],
      ),
    );
  }

  Widget _construirNivel3(bool isDark) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
        border: Border(bottom: BorderSide(color: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1))),
      ),
      child: Row(
        children: [
          _celdaCabecera('#', 40, Colors.transparent),
          _celdaCabecera('Apellidos y Nombres', 190, Colors.transparent),
          for (int c = 0; c < totalCompetencias; c++) ...[
            // Proceso
            _celdaSubCol('Part.', 52),
            _celdaSubCol('Activ.', 52),
            _celdaSubCol('Prom.', 52, esResaltado: true),
            // Final
            _celdaSubCol('Auto.', 52),
            _celdaSubCol('Eval.', 52),
            _celdaSubCol('Prom.', 52, esResaltado: true),
            // Promedios
            _celdaSubCol('Proc.', 52),
            _celdaSubCol('Fin.', 52),
            _celdaSubCol('Valor.', 52, esResaltado: true),
          ],
          _celdaSubCol('Nivel', 65),
          _celdaSubCol('Curso', 75),
          _celdaSubCol('Sistema', 75),
          _celdaSubCol('Texto Observación', 250),
        ],
      ),
    );
  }

  Widget _construirFilaAlumno(DocenteAlumnoCalificacion a, int idx, bool isDark) {
    final reg = notasPpd[a.id] ?? const PpdRegistro();
    final tieneNota = reg.calificacionSistema != null;
    final nota = reg.calificacionSistema ?? 0;
    final esAprobado = nota >= 11;
    final esPar = idx % 2 == 0;

    final filaBg = isDark
        ? (esPar ? AppColors.darkSurface : AppColors.darkSurfaceElevated)
        : (esPar ? Colors.white : const Color(0xFFF8FAFC));

    return Container(
      decoration: BoxDecoration(
        color: filaBg,
        border: Border(bottom: BorderSide(color: isDark ? AppColors.darkBorder.withValues(alpha: 0.4) : const Color(0xFFE2E8F0))),
      ),
      child: Row(
        children: [
          // #
          Container(
            width: 40,
            alignment: Alignment.center,
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Text(
              '${idx + 1}',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
            ),
          ),

          // Alumno
          Container(
            width: 190,
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  a.nombreCompleto,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                  ),
                ),
                if (a.dni != null)
                  Text(
                    'DNI: ${a.dni}',
                    style: TextStyle(fontSize: 10, color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8)),
                  ),
              ],
            ),
          ),

          // Competencias
          for (int c = 0; c < totalCompetencias; c++) ...[
            // Proceso
            _celdaInput(52, reg.getParticipacion(c), (v) => onNotaChanged(a.id, c, 'participacion', v)),
            _celdaInput(52, reg.getActividad(c), (v) => onNotaChanged(a.id, c, 'actividad', v)),
            _celdaChip(52, reg.getPromedioProceso(c), const Color(0xFFD97706)),

            // Final
            _celdaInput(52, reg.getAutoevaluacion(c), (v) => onNotaChanged(a.id, c, 'autoevaluacion', v)),
            _celdaInput(52, reg.getEvaluacion(c), (v) => onNotaChanged(a.id, c, 'evaluacion', v)),
            _celdaChip(52, reg.getPromedioFinal(c), const Color(0xFFB45309)),

            // Promedios
            _celdaChip(52, reg.getPromedioProceso(c), isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
            _celdaChip(52, reg.getPromedioFinal(c), isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
            _celdaChip(52, reg.getValoracion(c), const Color(0xFF10B981), esNegrita: true),
          ],

          // Nivel
          Container(
            width: 65,
            alignment: Alignment.center,
            child: Text(
              reg.nivelDesempeno ?? '-',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: isDark ? AppColors.darkText : const Color(0xFF0F172A)),
            ),
          ),

          // Calif. Curso
          Container(
            width: 75,
            alignment: Alignment.center,
            child: Text(
              reg.calificacionCurso != null ? reg.calificacionCurso!.toStringAsFixed(2) : '-',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: isDark ? AppColors.darkText : const Color(0xFF0F172A)),
            ),
          ),

          // Calif. Sistema
          Container(
            width: 75,
            alignment: Alignment.center,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
              decoration: BoxDecoration(
                color: tieneNota
                    ? (esAprobado ? const Color(0xFF10B981).withValues(alpha: 0.15) : AppColors.danger.withValues(alpha: 0.15))
                    : Colors.transparent,
                borderRadius: BorderRadius.circular(6),
              ),
              child: Text(
                tieneNota ? '$nota' : '-',
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w900,
                  color: tieneNota
                      ? (esAprobado ? const Color(0xFF10B981) : AppColors.danger)
                      : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                ),
              ),
            ),
          ),

          // Observaciones (Siempre visible y editable en la celda)
          Container(
            width: 250,
            padding: const EdgeInsets.all(6),
            child: _PpdObservacionInput(
              observacionInicial: reg.observaciones,
              onChanged: (obs) => onObservacionesChanged(a.id, obs),
              maxLines: 2,
              hint: 'Observaciones...',
            ),
          ),
        ],
      ),
    );
  }

  Widget _celdaCabecera(String titulo, double width, Color bg, {Color? textColor}) {
    return Container(
      width: width,
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
      color: bg,
      alignment: Alignment.center,
      child: Text(
        titulo,
        textAlign: TextAlign.center,
        style: TextStyle(
          fontSize: 11.5,
          fontWeight: FontWeight.w800,
          color: textColor ?? (bg == Colors.white ? const Color(0xFF0F172A) : Colors.white),
        ),
      ),
    );
  }

  Widget _celdaSubCol(String titulo, double width, {bool esResaltado = false}) {
    return Container(
      width: width,
      padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 2),
      alignment: Alignment.center,
      child: Text(
        titulo,
        textAlign: TextAlign.center,
        style: TextStyle(
          fontSize: 10,
          fontWeight: esResaltado ? FontWeight.w900 : FontWeight.w600,
          color: esResaltado ? const Color(0xFF7E22CE) : const Color(0xFF475569),
        ),
      ),
    );
  }

  Widget _celdaInput(double width, int? valor, ValueChanged<int?> onChanged) {
    return Container(
      width: width,
      alignment: Alignment.center,
      padding: const EdgeInsets.symmetric(horizontal: 3, vertical: 4),
      child: _PpdNotaInput(
        valorInicial: valor,
        onChanged: onChanged,
        width: 44,
        height: 32,
      ),
    );
  }

  Widget _celdaChip(double width, int? valor, Color color, {bool esNegrita = false}) {
    return Container(
      width: width,
      alignment: Alignment.center,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.12),
          borderRadius: BorderRadius.circular(4),
        ),
        child: Text(
          valor != null ? '$valor' : '-',
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: esNegrita ? FontWeight.w900 : FontWeight.w700,
            color: color,
          ),
        ),
      ),
    );
  }
}

// -------------------------------------------------------------
// INPUTS REUTILIZABLES PARA CALIFICACIONES PPD
// -------------------------------------------------------------
class _PpdNotaInput extends StatefulWidget {
  const _PpdNotaInput({
    required this.valorInicial,
    required this.onChanged,
    this.width = 46,
    this.height = 34,
  });

  final int? valorInicial;
  final ValueChanged<int?> onChanged;
  final double width;
  final double height;

  @override
  State<_PpdNotaInput> createState() => _PpdNotaInputState();
}

class _PpdNotaInputState extends State<_PpdNotaInput> {
  late TextEditingController _controller;

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController(text: widget.valorInicial != null ? '${widget.valorInicial}' : '');
  }

  @override
  void didUpdateWidget(covariant _PpdNotaInput oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.valorInicial != widget.valorInicial) {
      final nuevoTexto = widget.valorInicial != null ? '${widget.valorInicial}' : '';
      if (_controller.text != nuevoTexto) {
        _controller.text = nuevoTexto;
      }
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return SizedBox(
      width: widget.width,
      height: widget.height,
      child: TextField(
        controller: _controller,
        textAlign: TextAlign.center,
        keyboardType: TextInputType.number,
        style: TextStyle(
          fontSize: 12.5,
          fontWeight: FontWeight.w800,
          color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
        ),
        decoration: InputDecoration(
          contentPadding: EdgeInsets.zero,
          filled: true,
          fillColor: isDark ? AppColors.darkSurface : Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(6),
            borderSide: BorderSide(color: PukllaDashboardStyles.borderColor(context)),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(6),
            borderSide: BorderSide(color: PukllaDashboardStyles.borderColor(context)),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(6),
            borderSide: const BorderSide(color: Color(0xFF7E22CE), width: 1.5),
          ),
        ),
        onChanged: (val) {
          final trimmed = val.trim();
          if (trimmed.isEmpty) {
            widget.onChanged(null);
            return;
          }
          final n = int.tryParse(trimmed);
          if (n != null && n >= 0 && n <= 20) {
            widget.onChanged(n);
          }
        },
      ),
    );
  }
}

class _PpdObservacionInput extends StatefulWidget {
  const _PpdObservacionInput({
    required this.observacionInicial,
    required this.onChanged,
    this.maxLines = 2,
    this.hint = 'Observaciones (opcional)...',
  });

  final String? observacionInicial;
  final ValueChanged<String> onChanged;
  final int maxLines;
  final String hint;

  @override
  State<_PpdObservacionInput> createState() => _PpdObservacionInputState();
}

class _PpdObservacionInputState extends State<_PpdObservacionInput> {
  late TextEditingController _controller;
  final FocusNode _focusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController(text: widget.observacionInicial ?? '');
  }

  @override
  void didUpdateWidget(covariant _PpdObservacionInput oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.observacionInicial != widget.observacionInicial &&
        _controller.text != (widget.observacionInicial ?? '')) {
      _controller.text = widget.observacionInicial ?? '';
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return TextField(
      controller: _controller,
      focusNode: _focusNode,
      onChanged: widget.onChanged,
      maxLines: widget.maxLines,
      style: TextStyle(
        fontSize: 12,
        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
      ),
      decoration: InputDecoration(
        hintText: widget.hint,
        hintStyle: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        filled: true,
        fillColor: isDark ? AppColors.darkSurface : Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: PukllaDashboardStyles.borderColor(context)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: PukllaDashboardStyles.borderColor(context)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFF7E22CE), width: 1.2),
        ),
      ),
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
