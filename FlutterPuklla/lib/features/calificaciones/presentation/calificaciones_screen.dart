import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../application/calificaciones_providers.dart';
import '../data/calificacion.dart';

/// Pantalla institucional de calificaciones del alumno a nivel Staff / Senior:
/// - Tab 1: Período actual con KPI de desempeño, medidor de promedio ponderado,
///   buscador en vivo, filtros de estado, desglose por parciales y observaciones docentes.
/// - Tab 2: Historial completo de semestres anteriores con navegación horizontal rápida,
///   estadísticas acumuladas de créditos y cálculo de promedios por acta.
/// - Soporte de Dark Mode (Modo Oscuro) integral con tokens de contraste WCAG AA.
class CalificacionesScreen extends StatelessWidget {
  const CalificacionesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return DefaultTabController(
      length: 2,
      child: Scaffold(
        backgroundColor: PukllaDashboardStyles.backgroundColor(context),
        appBar: AppBar(
          title: const Text(
            'Calificaciones',
            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18),
          ),
          actions: const [PukllaAppBarActions()],
          elevation: 0,
          bottom: TabBar(
            labelColor: AppColors.gold,
            unselectedLabelColor: isDark ? AppColors.darkMuted : const Color(0xFFCBD5E1),
            indicatorColor: AppColors.gold,
            indicatorWeight: 3.5,
            indicatorSize: TabBarIndicatorSize.tab,
            labelStyle: const TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 13.5,
              letterSpacing: 0.1,
            ),
            unselectedLabelStyle: const TextStyle(
              fontWeight: FontWeight.w600,
              fontSize: 13,
            ),
            tabs: const [
              Tab(
                icon: Icon(Icons.school_outlined, size: 20),
                text: 'Período actual',
              ),
              Tab(
                icon: Icon(Icons.history_edu_outlined, size: 20),
                text: 'Períodos anteriores',
              ),
            ],
          ),
        ),
        endDrawer: const AppDrawer(),
        bottomNavigationBar: const PukllaBottomNavBar(),
        body: const TabBarView(
          children: [
            _PeriodoActualTab(),
            _PeriodosAnterioresTab(),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// TAB 1: PERÍODO ACTUAL (CON KPI, BUSCADOR Y FILTROS)
// ─────────────────────────────────────────────────────────────────────────────

class _PeriodoActualTab extends ConsumerStatefulWidget {
  const _PeriodoActualTab();

  @override
  ConsumerState<_PeriodoActualTab> createState() => _PeriodoActualTabState();
}

class _PeriodoActualTabState extends ConsumerState<_PeriodoActualTab> {
  final TextEditingController _searchController = TextEditingController();
  int _filtroEstado = 0; // 0: Todos, 1: Aprobados, 2: En curso / Pendientes

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final calificaciones = ref.watch(calificacionesProvider);
    final isDark = PukllaDashboardStyles.isDark(context);

    return RefreshIndicator(
      onRefresh: () => ref.refresh(calificacionesProvider.future),
      child: calificaciones.when(
        data: (data) => _buildContent(data.periodoActual, isDark),
        loading: () => _buildSkeletonLoader(isDark),
        error: (error, _) => Padding(
          padding: const EdgeInsets.all(20),
          child: ErrorState(
            message: error is ApiException
                ? error.message
                : 'No se pudieron cargar las calificaciones del período actual.',
            onRetry: () => ref.invalidate(calificacionesProvider),
          ),
        ),
      ),
    );
  }

  Widget _buildSkeletonLoader(bool isDark) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
      children: const [
        PukllaSkeletonCard(height: 160),
        SizedBox(height: 16),
        Row(
          children: [
            Expanded(child: PukllaSkeletonCard(height: 72)),
            SizedBox(width: 8),
            Expanded(child: PukllaSkeletonCard(height: 72)),
            SizedBox(width: 8),
            Expanded(child: PukllaSkeletonCard(height: 72)),
            SizedBox(width: 8),
            Expanded(child: PukllaSkeletonCard(height: 72)),
          ],
        ),
        SizedBox(height: 16),
        PukllaSkeletonCard(height: 180),
        SizedBox(height: 12),
        PukllaSkeletonCard(height: 180),
      ],
    );
  }

  Widget _buildContent(PeriodoActualCalificaciones periodoActual, bool isDark) {
    final cursos = periodoActual.cursos;

    // Métricas del período actual ponderadas por créditos
    double sumaPonderada = 0;
    int totalCreditosConNota = 0;
    for (final c in cursos) {
      if (c.tieneNota) {
        final cred = (c.creditos != null && c.creditos! > 0) ? c.creditos! : 3;
        sumaPonderada += c.notaVigenteNum! * cred;
        totalCreditosConNota += cred;
      }
    }
    final promedioGeneral = totalCreditosConNota > 0
        ? (sumaPonderada / totalCreditosConNota)
        : null;

    final aprobados = cursos.where((c) => c.esAprobado).length;
    final desaprobados = cursos.where((c) => c.esDesaprobado).length;
    final pendientes = cursos.where((c) => !c.tieneNota).length;

    final tieneAvisos = (!periodoActual.p1Visible && periodoActual.fechaP1Visible != null) ||
        (!periodoActual.p2Visible && periodoActual.fechaP2Visible != null);

    // Filtrado por buscador y chips
    final query = _searchController.text.trim().toLowerCase();
    final cursosFiltrados = cursos.where((c) {
      if (query.isNotEmpty && !c.cursoNombre.toLowerCase().contains(query)) {
        return false;
      }
      if (_filtroEstado == 1) {
        return c.esAprobado;
      }
      if (_filtroEstado == 2) {
        return !c.tieneNota;
      }
      if (_filtroEstado == 3) {
        return c.esDesaprobado;
      }
      return true;
    }).toList();

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
      children: [
        // ── HERO BANNER DEL PERÍODO ACTUAL ──
        _PeriodoActualHeroCard(
          periodoNombre: periodoActual.periodoNombre,
          promedioGeneral: promedioGeneral,
          cursosCount: cursos.length,
          isDark: isDark,
        ),
        const SizedBox(height: 16),

        // ── KPIs / STATS RÁPIDOS ──
        Row(
          children: [
            Expanded(
              child: _StatCard(
                icon: Icons.menu_book_rounded,
                titulo: 'Cursos',
                valor: cursos.length.toString(),
                color: AppColors.teal,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _StatCard(
                icon: Icons.star_rounded,
                titulo: 'Ponderado',
                valor: promedioGeneral != null ? promedioGeneral.toStringAsFixed(1) : '—',
                color: AppColors.goldHover,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _StatCard(
                icon: Icons.check_circle_rounded,
                titulo: 'Aprobados',
                valor: aprobados.toString(),
                color: const Color(0xFF10B981),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _StatCard(
                icon: Icons.pending_actions_rounded,
                titulo: 'Pendientes',
                valor: pendientes.toString(),
                color: const Color(0xFFF59E0B),
              ),
            ),
          ],
        ),

        // ── AVISO DE FECHAS DE PUBLICACIÓN OFICIAL ──
        if (tieneAvisos) ...[
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF172554) : const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: isDark ? const Color(0xFF1E40AF) : const Color(0xFF93C5FD),
              ),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.info_outline_rounded, color: Color(0xFF3B82F6), size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    [
                      if (!periodoActual.p1Visible && periodoActual.fechaP1Visible != null)
                        'Las notas del Parcial 1 se publican el ${periodoActual.fechaP1Visible}.',
                      if (!periodoActual.p2Visible && periodoActual.fechaP2Visible != null)
                        'Las notas del Parcial 2 y el promedio final se publican el ${periodoActual.fechaP2Visible}.',
                    ].join(' '),
                    style: TextStyle(
                      fontSize: 12.5,
                      color: isDark ? const Color(0xFFBFDBFE) : const Color(0xFF1E3A8A),
                      height: 1.35,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],

        const SizedBox(height: 18),

        // ── BUSCADOR EN VIVO Y CHIPS DE FILTRO ──
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
          child: Row(
            children: [
              Icon(Icons.search_rounded, size: 20, color: PukllaDashboardStyles.textSecondary(context)),
              const SizedBox(width: 10),
              Expanded(
                child: TextField(
                  controller: _searchController,
                  onChanged: (_) => setState(() {}),
                  style: TextStyle(
                    fontSize: 13.5,
                    color: PukllaDashboardStyles.textPrimary(context),
                  ),
                  decoration: InputDecoration(
                    hintText: 'Buscar por nombre de curso...',
                    hintStyle: TextStyle(
                      fontSize: 13,
                      color: PukllaDashboardStyles.textSecondary(context),
                    ),
                    border: InputBorder.none,
                    isDense: true,
                    contentPadding: const EdgeInsets.symmetric(vertical: 6),
                  ),
                ),
              ),
              if (_searchController.text.isNotEmpty)
                GestureDetector(
                  onTap: () {
                    _searchController.clear();
                    setState(() {});
                  },
                  child: Icon(Icons.close_rounded, size: 18, color: PukllaDashboardStyles.textSecondary(context)),
                ),
            ],
          ),
        ),
        const SizedBox(height: 10),

        // Chips de estado
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _FilterChip(
                label: 'Todos (${cursos.length})',
                isSelected: _filtroEstado == 0,
                onTap: () => setState(() => _filtroEstado = 0),
                isDark: isDark,
              ),
              const SizedBox(width: 8),
              _FilterChip(
                label: 'Aprobados ($aprobados)',
                isSelected: _filtroEstado == 1,
                onTap: () => setState(() => _filtroEstado = 1),
                isDark: isDark,
              ),
              const SizedBox(width: 8),
              _FilterChip(
                label: 'Pendientes ($pendientes)',
                isSelected: _filtroEstado == 2,
                onTap: () => setState(() => _filtroEstado = 2),
                isDark: isDark,
              ),
              if (desaprobados > 0) ...[
                const SizedBox(width: 8),
                _FilterChip(
                  label: 'Desaprobados ($desaprobados)',
                  isSelected: _filtroEstado == 3,
                  onTap: () => setState(() => _filtroEstado = 3),
                  isDark: isDark,
                ),
              ],
            ],
          ),
        ),

        const SizedBox(height: 16),

        // ── LISTADO DE CURSOS ──
        if (cursos.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
            child: Column(
              children: [
                const Icon(Icons.inbox_outlined, size: 48, color: AppColors.muted),
                const SizedBox(height: 12),
                Text(
                  'No hay cursos registrados para este período.',
                  style: TextStyle(
                    color: PukllaDashboardStyles.textSecondary(context),
                    fontSize: 14,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          )
        else if (cursosFiltrados.isEmpty)
          Container(
            padding: const EdgeInsets.all(28),
            decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
            child: Column(
              children: [
                const Icon(Icons.search_off_rounded, size: 42, color: AppColors.muted),
                const SizedBox(height: 10),
                Text(
                  'No se encontraron asignaturas con ese filtro.',
                  style: TextStyle(
                    color: PukllaDashboardStyles.textSecondary(context),
                    fontSize: 13.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          )
        else
          for (final curso in cursosFiltrados) ...[
            _CursoActualCard(curso: curso, periodoActual: periodoActual),
            const SizedBox(height: 14),
          ],
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// HERO CARD INSTITUCIONAL DEL PERÍODO ACTUAL
// ─────────────────────────────────────────────────────────────────────────────

class _PeriodoActualHeroCard extends StatelessWidget {
  const _PeriodoActualHeroCard({
    required this.periodoNombre,
    required this.promedioGeneral,
    required this.cursosCount,
    required this.isDark,
  });

  final String? periodoNombre;
  final double? promedioGeneral;
  final int cursosCount;
  final bool isDark;

  String _desempenoTexto(double prom) {
    if (prom >= 17.0) return 'Desempeño Destacado';
    if (prom >= 14.0) return 'Desempeño Logrado';
    if (prom >= 11.0) return 'En Proceso';
    return 'En Riesgo Académico';
  }

  Color _desempenoColor(double prom) {
    if (prom >= 17.0) return AppColors.gold;
    if (prom >= 14.0) return const Color(0xFF34D399);
    if (prom >= 11.0) return const Color(0xFF60A5FA);
    return const Color(0xFFF87171);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: isDark
              ? const [Color(0xFF0F1E36), Color(0xFF1B2A4A)]
              : const [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.15),
            blurRadius: 14,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(9),
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: 0.20),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.gold.withValues(alpha: 0.5)),
                ),
                child: const Icon(Icons.school_rounded, color: AppColors.gold, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      periodoNombre != null ? 'Período $periodoNombre' : 'Período en curso',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 17.5,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.2,
                      ),
                    ),
                    const SizedBox(height: 2),
                    const Text(
                      'Semestre lectivo en vigencia',
                      style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFF10B981)),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.fiber_manual_record, color: Color(0xFF10B981), size: 9),
                    SizedBox(width: 5),
                    Text(
                      'Activo',
                      style: TextStyle(
                        color: Color(0xFF10B981),
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Medidor de promedio y desempeño
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.white.withValues(alpha: 0.12)),
            ),
            child: Row(
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'PROMEDIO PONDERADO',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF94A3B8),
                        letterSpacing: 0.4,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      promedioGeneral != null ? promedioGeneral!.toStringAsFixed(1) : '—',
                      style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                        color: promedioGeneral != null
                            ? _desempenoColor(promedioGeneral!)
                            : Colors.white,
                      ),
                    ),
                  ],
                ),
                const Spacer(),
                if (promedioGeneral != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: _desempenoColor(promedioGeneral!).withValues(alpha: 0.18),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: _desempenoColor(promedioGeneral!).withValues(alpha: 0.4),
                      ),
                    ),
                    child: Text(
                      _desempenoTexto(promedioGeneral!),
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                        color: _desempenoColor(promedioGeneral!),
                      ),
                    ),
                  )
                else
                  const Text(
                    'Notas en proceso',
                    style: TextStyle(
                      fontSize: 12,
                      color: Color(0xFF94A3B8),
                      fontStyle: FontStyle.italic,
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

// ─────────────────────────────────────────────────────────────────────────────
// TARJETA DE CURSO DEL PERÍODO ACTUAL (CON DESGLOSE Y MODO OSCURO)
// ─────────────────────────────────────────────────────────────────────────────

class _CursoActualCard extends StatelessWidget {
  const _CursoActualCard({
    required this.curso,
    required this.periodoActual,
  });

  final CursoConNotas curso;
  final PeriodoActualCalificaciones periodoActual;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final notaTexto = curso.notaVigenteTexto;
    final esAprobado = curso.esAprobado;
    final tieneNota = curso.tieneNota;
    final etiquetaNota = curso.etiquetaNotaVigente;

    // Color del borde de acento izquierdo según estado
    final accentColor = !tieneNota
        ? AppColors.teal
        : (esAprobado ? const Color(0xFF10B981) : const Color(0xFFEF4444));

    return Container(
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Barra vertical lateral de estado
              Container(width: 5, color: accentColor),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Cabecera del Curso
                    Padding(
                      padding: const EdgeInsets.all(14),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 38,
                            height: 38,
                            decoration: BoxDecoration(
                              color: accentColor.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(Icons.menu_book_rounded, color: accentColor, size: 20),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  curso.cursoNombre,
                                  style: TextStyle(
                                    fontSize: 14.5,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textPrimary(context),
                                    height: 1.25,
                                  ),
                                ),
                                const SizedBox(height: 5),
                                Wrap(
                                  spacing: 6,
                                  runSpacing: 4,
                                  children: [
                                    if (curso.cicloNombre != null)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
                                          borderRadius: BorderRadius.circular(6),
                                          border: Border.all(
                                            color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                                          ),
                                        ),
                                        child: Text(
                                          'Ciclo ${curso.cicloNombre}',
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w700,
                                            color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
                                          ),
                                        ),
                                      ),
                                    if (curso.programaNombre != null)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: AppColors.gold.withValues(alpha: 0.12),
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          curso.programaNombre!,
                                          style: const TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w700,
                                            color: AppColors.goldHover,
                                          ),
                                        ),
                                      ),
                                    if (curso.creditos != null && curso.creditos! > 0)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
                                          borderRadius: BorderRadius.circular(6),
                                          border: Border.all(
                                            color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                                          ),
                                        ),
                                        child: Text(
                                          '${curso.creditos} créd.',
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w700,
                                            color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
                                          ),
                                        ),
                                      ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          // Badge de Nota Final / Vigente
                          _FinalGradeBadge(
                            notaStr: notaTexto,
                            esAprobado: esAprobado,
                            etiqueta: etiquetaNota,
                            isDark: isDark,
                          ),
                        ],
                      ),
                    ),

                    Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),

                    // Tabla Desglose de Parciales
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      child: Column(
                        children: [
                          Row(
                            children: [
                              Expanded(
                                flex: 4,
                                child: Text(
                                  'EVALUACIÓN',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textSecondary(context),
                                    letterSpacing: 0.4,
                                  ),
                                ),
                              ),
                              Expanded(
                                flex: 3,
                                child: Text(
                                  'LOGRO',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textSecondary(context),
                                    letterSpacing: 0.4,
                                  ),
                                ),
                              ),
                              Expanded(
                                flex: 3,
                                child: Text(
                                  'VALORACIÓN',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textSecondary(context),
                                    letterSpacing: 0.4,
                                  ),
                                ),
                              ),
                              SizedBox(
                                width: 52,
                                child: Text(
                                  'NOTA',
                                  textAlign: TextAlign.end,
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textSecondary(context),
                                    letterSpacing: 0.4,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),

                          // Parcial 1
                          _ParcialEvaluationRow(
                            label: 'Parcial 1',
                            nota: curso.parcial1,
                            visible: periodoActual.p1Visible,
                            fechaPublicacion: periodoActual.fechaP1Visible,
                            isDark: isDark,
                          ),
                          const SizedBox(height: 6),

                          // Parcial 2
                          _ParcialEvaluationRow(
                            label: 'Parcial 2',
                            nota: curso.parcial2,
                            visible: periodoActual.p2Visible,
                            fechaPublicacion: periodoActual.fechaP2Visible,
                            isDark: isDark,
                          ),
                          const SizedBox(height: 6),

                          // Promedio final
                          _ParcialEvaluationRow(
                            label: 'Promedio final',
                            nota: curso.promedio,
                            visible: periodoActual.p2Visible,
                            fechaPublicacion: periodoActual.fechaP2Visible,
                            isFinal: true,
                            isDark: isDark,
                          ),
                        ],
                      ),
                    ),

                    // Observaciones docentes si existen
                    if (curso.observaciones != null && curso.observaciones!.trim().isNotEmpty) ...[
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        decoration: BoxDecoration(
                          color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                          borderRadius: const BorderRadius.vertical(bottom: Radius.circular(15)),
                          border: Border(
                            top: BorderSide(color: PukllaDashboardStyles.borderColor(context)),
                          ),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(Icons.comment_outlined, size: 16, color: AppColors.goldHover),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'Obs. docente: ${curso.observaciones}',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontStyle: FontStyle.italic,
                                  color: PukllaDashboardStyles.textSecondary(context),
                                  height: 1.3,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// FILA DE EVALUACIÓN PARCIAL
// ─────────────────────────────────────────────────────────────────────────────

class _ParcialEvaluationRow extends StatelessWidget {
  const _ParcialEvaluationRow({
    required this.label,
    required this.nota,
    required this.visible,
    this.fechaPublicacion,
    this.isFinal = false,
    required this.isDark,
  });

  final String label;
  final NotaParcial? nota;
  final bool visible;
  final String? fechaPublicacion;
  final bool isFinal;
  final bool isDark;

  @override
  Widget build(BuildContext context) {
    final sistema = nota?.calificacionSistema;
    final notaNum = double.tryParse(sistema ?? '');
    final esAprobado = notaNum != null && notaNum >= 11.0;

    return Container(
      padding: EdgeInsets.symmetric(horizontal: isFinal ? 8 : 0, vertical: isFinal ? 6 : 4),
      decoration: BoxDecoration(
        color: isFinal
            ? (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC))
            : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
        border: isFinal ? Border.all(color: PukllaDashboardStyles.borderColor(context)) : null,
      ),
      child: Row(
        children: [
          // Columna 1: Nombre de la evaluación
          Expanded(
            flex: 4,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: isFinal ? FontWeight.w800 : FontWeight.w600,
                color: isFinal
                    ? PukllaDashboardStyles.textPrimary(context)
                    : (isDark ? Colors.white70 : const Color(0xFF334155)),
              ),
            ),
          ),

          // Si aún no es visible por calendario
          if (!visible)
            Expanded(
              flex: 8,
              child: Text(
                fechaPublicacion != null ? 'Desde el $fechaPublicacion' : 'En espera',
                style: const TextStyle(
                  fontSize: 11.5,
                  fontStyle: FontStyle.italic,
                  color: Color(0xFF94A3B8),
                ),
              ),
            )
          else if (nota == null)
            const Expanded(
              flex: 8,
              child: Text(
                'Sin datos aún',
                style: TextStyle(
                  fontSize: 11.5,
                  fontStyle: FontStyle.italic,
                  color: Color(0xFF94A3B8),
                ),
              ),
            )
          else ...[
            // Columna 2: Logro
            Expanded(
              flex: 3,
              child: Text(
                nota!.calificacionCurso ?? '—',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
                ),
              ),
            ),
            // Columna 3: Valoración
            Expanded(
              flex: 3,
              child: Text(
                nota!.valoracionCurso ?? '—',
                style: TextStyle(
                  fontSize: 12,
                  color: PukllaDashboardStyles.textSecondary(context),
                ),
              ),
            ),
            // Columna 4: Nota del sistema
            SizedBox(
              width: 52,
              child: Align(
                alignment: Alignment.centerRight,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                  decoration: BoxDecoration(
                    color: notaNum == null
                        ? (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9))
                        : (esAprobado
                            ? (isDark ? const Color(0xFF064E3B) : const Color(0xFFECFDF5))
                            : (isDark ? const Color(0xFF4C0519) : const Color(0xFFFEF2F2))),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: notaNum == null
                          ? PukllaDashboardStyles.borderColor(context)
                          : (esAprobado
                              ? (isDark ? const Color(0xFF059669) : const Color(0xFFA7F3D0))
                              : (isDark ? const Color(0xFF9F1239) : const Color(0xFFFECACA))),
                    ),
                  ),
                  child: Text(
                    sistema ?? '—',
                    style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w900,
                      color: notaNum == null
                          ? PukllaDashboardStyles.textSecondary(context)
                          : (esAprobado
                              ? (isDark ? const Color(0xFF34D399) : const Color(0xFF059669))
                              : (isDark ? const Color(0xFFF87171) : const Color(0xFFDC2626))),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// BADGE DE NOTA FINAL
// ─────────────────────────────────────────────────────────────────────────────

class _FinalGradeBadge extends StatelessWidget {
  const _FinalGradeBadge({
    required this.notaStr,
    required this.esAprobado,
    this.etiqueta = 'Final',
    required this.isDark,
  });

  final String? notaStr;
  final bool esAprobado;
  final String etiqueta;
  final bool isDark;

  @override
  Widget build(BuildContext context) {
    if (notaStr == null || notaStr!.trim().isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
        ),
        child: Column(
          children: [
            Text(
              '—',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: PukllaDashboardStyles.textSecondary(context),
              ),
            ),
            const Text(
              'Pendiente',
              style: TextStyle(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: Color(0xFF94A3B8),
              ),
            ),
          ],
        ),
      );
    }

    final color = esAprobado
        ? (isDark ? const Color(0xFF34D399) : const Color(0xFF059669))
        : (isDark ? const Color(0xFFF87171) : const Color(0xFFDC2626));
    final bgColor = esAprobado
        ? (isDark ? const Color(0xFF064E3B) : const Color(0xFFECFDF5))
        : (isDark ? const Color(0xFF4C0519) : const Color(0xFFFEF2F2));
    final borderColor = esAprobado
        ? (isDark ? const Color(0xFF059669) : const Color(0xFFA7F3D0))
        : (isDark ? const Color(0xFF9F1239) : const Color(0xFFFECACA));

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: borderColor),
      ),
      child: Column(
        children: [
          Text(
            notaStr!,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w900,
              color: color,
            ),
          ),
          Text(
            etiqueta,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w700,
              color: color.withValues(alpha: 0.8),
            ),
          ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// TAB 2: PERÍODOS ANTERIORES (CON SELECTOR, RESUMEN Y MODO OSCURO)
// ─────────────────────────────────────────────────────────────────────────────

class _PeriodosAnterioresTab extends ConsumerStatefulWidget {
  const _PeriodosAnterioresTab();

  @override
  ConsumerState<_PeriodosAnterioresTab> createState() => _PeriodosAnterioresTabState();
}

class _PeriodosAnterioresTabState extends ConsumerState<_PeriodosAnterioresTab> {
  int _selectedPeriodoIndex = 0;
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final calificaciones = ref.watch(calificacionesProvider);
    final isDark = PukllaDashboardStyles.isDark(context);

    return RefreshIndicator(
      onRefresh: () => ref.refresh(calificacionesProvider.future),
      child: calificaciones.when(
        data: (data) {
          // Más recientes primero
          final periodos = data.anteriores.reversed.toList();

          if (periodos.isEmpty) {
            return ListView(
              padding: const EdgeInsets.all(32),
              children: [
                const SizedBox(height: 60),
                Center(
                  child: Container(
                    width: 80,
                    height: 80,
                    decoration: BoxDecoration(
                      color: AppColors.gold.withValues(alpha: 0.12),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.history_edu_rounded,
                      size: 40,
                      color: AppColors.gold,
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                Text(
                  'Sin períodos anteriores',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: PukllaDashboardStyles.textPrimary(context),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Actualmente te encuentras cursando tu primer período o aún no se registran actas cerradas de semestres anteriores.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13.5,
                    color: PukllaDashboardStyles.textSecondary(context),
                    height: 1.4,
                  ),
                ),
              ],
            );
          }

          final safeIndex = _selectedPeriodoIndex.clamp(0, periodos.length - 1);
          final grupoActual = periodos[safeIndex];

          // Cálculo de métricas ponderadas por créditos del período seleccionado
          double sumaPonderada = 0;
          int totalCreditosConNota = 0;
          final notas = <double>[];
          for (final c in grupoActual.calificaciones) {
            final n = double.tryParse(c.calificacionSistema ?? '');
            if (n != null) {
              notas.add(n);
              final cred = (c.creditos != null && c.creditos! > 0) ? c.creditos! : 3;
              sumaPonderada += n * cred;
              totalCreditosConNota += cred;
            }
          }
          final promedioGrupo = totalCreditosConNota > 0
              ? (sumaPonderada / totalCreditosConNota)
              : null;
          final aprobadas = notas.where((n) => n >= 11.0).length;
          final creditosTotal = grupoActual.calificaciones
              .map((c) => c.creditos ?? 0)
              .fold<int>(0, (prev, elem) => prev + elem);

          // Filtrar por texto
          final query = _searchController.text.trim().toLowerCase();
          final calificacionesFiltradas = grupoActual.calificaciones.where((c) {
            if (query.isEmpty) return true;
            return (c.cursoNombre ?? '').toLowerCase().contains(query);
          }).toList();

          return ListView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
            children: [
              // Selector horizontal de períodos
              SizedBox(
                height: 42,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: periodos.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 8),
                  itemBuilder: (context, index) {
                    final item = periodos[index];
                    final isSelected = index == safeIndex;

                    return ChoiceChip(
                      selected: isSelected,
                      showCheckmark: false,
                      avatar: Icon(
                        Icons.calendar_month_outlined,
                        size: 16,
                        color: isSelected ? Colors.white : AppColors.teal,
                      ),
                      label: Text(
                        item.periodoNombre,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                          color: isSelected
                              ? Colors.white
                              : (isDark ? AppColors.darkText : const Color(0xFF1E293B)),
                        ),
                      ),
                      backgroundColor: isDark ? AppColors.darkSurface : Colors.white,
                      selectedColor: AppColors.teal,
                      side: BorderSide(
                        color: isSelected ? AppColors.teal : PukllaDashboardStyles.borderColor(context),
                        width: isSelected ? 1.5 : 1,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                      onSelected: (selected) {
                        if (selected) {
                          setState(() {
                            _selectedPeriodoIndex = index;
                            _searchController.clear();
                          });
                        }
                      },
                    );
                  },
                ),
              ),

              const SizedBox(height: 16),

              // Banner de resumen del período seleccionado
              Container(
                padding: const EdgeInsets.all(16),
                decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: AppColors.gold.withValues(alpha: 0.16),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(
                            Icons.auto_stories_rounded,
                            color: AppColors.goldHover,
                            size: 22,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Historial Semestre ${grupoActual.periodoNombre}',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                  color: PukllaDashboardStyles.textPrimary(context),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${grupoActual.calificaciones.length} ${grupoActual.calificaciones.length == 1 ? "curso cursado" : "cursos cursados"}'
                                '${creditosTotal > 0 ? " · $creditosTotal créditos" : ""}',
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: PukllaDashboardStyles.textSecondary(context),
                                ),
                              ),
                            ],
                          ),
                        ),
                        if (promedioGrupo != null)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: promedioGrupo >= 11.0
                                  ? (isDark ? const Color(0xFF064E3B) : const Color(0xFFECFDF5))
                                  : (isDark ? const Color(0xFF4C0519) : const Color(0xFFFEF2F2)),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: promedioGrupo >= 11.0
                                    ? (isDark ? const Color(0xFF059669) : const Color(0xFFA7F3D0))
                                    : (isDark ? const Color(0xFF9F1239) : const Color(0xFFFECACA)),
                              ),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  promedioGrupo.toStringAsFixed(1),
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w900,
                                    color: promedioGrupo >= 11.0
                                        ? (isDark ? const Color(0xFF34D399) : const Color(0xFF059669))
                                        : (isDark ? const Color(0xFFF87171) : const Color(0xFFDC2626)),
                                  ),
                                ),
                                Text(
                                  'Promedio',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w700,
                                    color: promedioGrupo >= 11.0
                                        ? (isDark ? const Color(0xFF34D399) : const Color(0xFF059669))
                                        : (isDark ? const Color(0xFFF87171) : const Color(0xFFDC2626)),
                                  ),
                                ),
                              ],
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
                    const SizedBox(height: 10),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Aprobadas: $aprobadas de ${grupoActual.calificaciones.length}',
                          style: TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: PukllaDashboardStyles.textSecondary(context),
                          ),
                        ),
                        Text(
                          'Mínima aprobatoria: 11',
                          style: TextStyle(
                            fontSize: 12,
                            color: PukllaDashboardStyles.textSecondary(context),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 14),

              // Buscador de cursos históricos
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
                child: Row(
                  children: [
                    Icon(Icons.search_rounded, size: 18, color: PukllaDashboardStyles.textSecondary(context)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _searchController,
                        onChanged: (_) => setState(() {}),
                        style: TextStyle(
                          fontSize: 13,
                          color: PukllaDashboardStyles.textPrimary(context),
                        ),
                        decoration: InputDecoration(
                          hintText: 'Filtrar curso de este período...',
                          hintStyle: TextStyle(
                            fontSize: 12.5,
                            color: PukllaDashboardStyles.textSecondary(context),
                          ),
                          border: InputBorder.none,
                          isDense: true,
                          contentPadding: const EdgeInsets.symmetric(vertical: 6),
                        ),
                      ),
                    ),
                    if (_searchController.text.isNotEmpty)
                      GestureDetector(
                        onTap: () {
                          _searchController.clear();
                          setState(() {});
                        },
                        child: Icon(Icons.close_rounded, size: 16, color: PukllaDashboardStyles.textSecondary(context)),
                      ),
                  ],
                ),
              ),

              const SizedBox(height: 14),

              // Lista de asignaturas del período seleccionado
              if (calificacionesFiltradas.isEmpty)
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
                  child: Column(
                    children: [
                      const Icon(Icons.search_off_rounded, size: 36, color: AppColors.muted),
                      const SizedBox(height: 8),
                      Text(
                        'No hay cursos que coincidan con la búsqueda.',
                        style: TextStyle(
                          fontSize: 13,
                          color: PukllaDashboardStyles.textSecondary(context),
                        ),
                      ),
                    ],
                  ),
                )
              else
                for (final cal in calificacionesFiltradas) ...[
                  _CalificacionAnteriorCard(calificacion: cal, isDark: isDark),
                  const SizedBox(height: 12),
                ],
            ],
          );
        },
        loading: () => ListView(
          padding: const EdgeInsets.all(16),
          children: const [
            PukllaSkeletonCard(height: 50),
            SizedBox(height: 16),
            PukllaSkeletonCard(height: 110),
            SizedBox(height: 14),
            PukllaSkeletonCard(height: 100),
            SizedBox(height: 12),
            PukllaSkeletonCard(height: 100),
          ],
        ),
        error: (error, _) => Padding(
          padding: const EdgeInsets.all(16),
          child: ErrorState(
            message: error is ApiException
                ? error.message
                : 'No se pudieron cargar las calificaciones anteriores.',
            onRetry: () => ref.invalidate(calificacionesProvider),
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// TARJETA DE CURSO EN HISTÓRICO DE ANTERIORES
// ─────────────────────────────────────────────────────────────────────────────

class _CalificacionAnteriorCard extends StatelessWidget {
  const _CalificacionAnteriorCard({
    required this.calificacion,
    required this.isDark,
  });

  final Calificacion calificacion;
  final bool isDark;

  @override
  Widget build(BuildContext context) {
    final sistema = calificacion.calificacionSistema;
    final notaNum = double.tryParse(sistema ?? '');
    final esAprobado = notaNum != null && notaNum >= 11.0;

    final accentColor = notaNum == null
        ? AppColors.teal
        : (esAprobado ? const Color(0xFF10B981) : const Color(0xFFEF4444));

    final subInfo = [
      if (calificacion.programaNombre != null && calificacion.programaNombre!.isNotEmpty)
        calificacion.programaNombre!,
      if (calificacion.cicloNombre != null && calificacion.cicloNombre!.isNotEmpty)
        'Ciclo ${calificacion.cicloNombre}',
      if (calificacion.creditos != null && calificacion.creditos! > 0)
        '${calificacion.creditos} créd.',
      if (calificacion.horas != null && calificacion.horas! > 0)
        '${calificacion.horas} hrs.',
    ].join(' · ');

    return Container(
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(width: 5, color: accentColor),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 36,
                            height: 36,
                            decoration: BoxDecoration(
                              color: accentColor.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(Icons.history_rounded, color: accentColor, size: 20),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  calificacion.cursoNombre ?? 'Curso #${calificacion.cursoId}',
                                  style: TextStyle(
                                    fontSize: 14.5,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textPrimary(context),
                                    height: 1.25,
                                  ),
                                ),
                                if (subInfo.isNotEmpty) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    subInfo,
                                    style: TextStyle(
                                      fontSize: 11.5,
                                      fontWeight: FontWeight.w500,
                                      color: PukllaDashboardStyles.textSecondary(context),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          // Nota final
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                            decoration: BoxDecoration(
                              color: notaNum == null
                                  ? (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9))
                                  : (esAprobado
                                      ? (isDark ? const Color(0xFF064E3B) : const Color(0xFFECFDF5))
                                      : (isDark ? const Color(0xFF4C0519) : const Color(0xFFFEF2F2))),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: notaNum == null
                                    ? PukllaDashboardStyles.borderColor(context)
                                    : (esAprobado
                                        ? (isDark ? const Color(0xFF059669) : const Color(0xFFA7F3D0))
                                        : (isDark ? const Color(0xFF9F1239) : const Color(0xFFFECACA))),
                              ),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  sistema ?? '—',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w900,
                                    color: notaNum == null
                                        ? PukllaDashboardStyles.textSecondary(context)
                                        : (esAprobado
                                            ? (isDark ? const Color(0xFF34D399) : const Color(0xFF059669))
                                            : (isDark ? const Color(0xFFF87171) : const Color(0xFFDC2626))),
                                  ),
                                ),
                                Text(
                                  esAprobado ? 'Aprobado' : (notaNum == null ? 'Sin nota' : 'Desaprob.'),
                                  style: TextStyle(
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w700,
                                    color: notaNum == null
                                        ? PukllaDashboardStyles.textSecondary(context)
                                        : (esAprobado
                                            ? (isDark ? const Color(0xFF34D399) : const Color(0xFF059669))
                                            : (isDark ? const Color(0xFFF87171) : const Color(0xFFDC2626))),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 12),
                      Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
                      const SizedBox(height: 10),

                      // Métricas secundarias: Logro y Valoración
                      Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'NIVEL DE LOGRO',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textSecondary(context),
                                    letterSpacing: 0.3,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  calificacion.calificacionCurso ?? '—',
                                  style: TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w700,
                                    color: isDark ? AppColors.darkText : const Color(0xFF334155),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'VALORACIÓN',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textSecondary(context),
                                    letterSpacing: 0.3,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  calificacion.valoracionCurso ?? '—',
                                  style: TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w700,
                                    color: isDark ? AppColors.darkText : const Color(0xFF334155),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// COMPONENTES AUXILIARES ESTILIZADOS
// ─────────────────────────────────────────────────────────────────────────────

class _FilterChip extends StatelessWidget {
  const _FilterChip({
    required this.label,
    required this.isSelected,
    required this.onTap,
    required this.isDark,
  });

  final String label;
  final bool isSelected;
  final VoidCallback onTap;
  final bool isDark;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected
              ? AppColors.teal
              : (isDark ? AppColors.darkSurface : Colors.white),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppColors.teal : PukllaDashboardStyles.borderColor(context),
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected
                ? Colors.white
                : (isDark ? AppColors.darkMuted : const Color(0xFF475569)),
          ),
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  const _StatCard({
    required this.icon,
    required this.titulo,
    required this.valor,
    required this.color,
  });

  final IconData icon;
  final String titulo;
  final String valor;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 4),
          Text(
            valor,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w900,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            titulo,
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.w600,
              color: PukllaDashboardStyles.textSecondary(context),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
