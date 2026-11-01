import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../application/calificaciones_providers.dart';
import '../data/calificacion.dart';

/// Pantalla institucional de calificaciones del alumno:
/// - Tab 1: Período actual con parciales, promedios, estados y observaciones docentes.
/// - Tab 2: Historial completo de calificaciones por períodos anteriores cursados.
class CalificacionesScreen extends StatelessWidget {
  const CalificacionesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Calificaciones'),
          actions: const [PukllaAppBarActions()],
          bottom: const TabBar(
            labelColor: AppColors.gold,
            unselectedLabelColor: Color(0xFFE2E8F0),
            indicatorColor: AppColors.gold,
            indicatorWeight: 3.5,
            indicatorSize: TabBarIndicatorSize.tab,
            labelStyle: TextStyle(
              fontWeight: FontWeight.w700,
              fontSize: 14,
            ),
            unselectedLabelStyle: TextStyle(
              color: Color(0xFFE2E8F0),
              fontWeight: FontWeight.w600,
              fontSize: 14,
            ),
            tabs: [
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
// TAB 1: PERÍODO ACTUAL
// ─────────────────────────────────────────────────────────────────────────────

class _PeriodoActualTab extends ConsumerWidget {
  const _PeriodoActualTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final calificaciones = ref.watch(calificacionesProvider);

    return RefreshIndicator(
      onRefresh: () => ref.refresh(calificacionesProvider.future),
      child: calificaciones.when(
        data: (data) => _PeriodoActualContent(periodoActual: data.periodoActual),
        loading: () => const Center(
          child: CircularProgressIndicator(),
        ),
        error: (error, _) => Padding(
          padding: const EdgeInsets.all(16),
          child: ErrorState(
            message: error is ApiException
                ? error.message
                : 'No se pudieron cargar las calificaciones.',
            onRetry: () => ref.invalidate(calificacionesProvider),
          ),
        ),
      ),
    );
  }
}

class _PeriodoActualContent extends StatelessWidget {
  const _PeriodoActualContent({required this.periodoActual});

  final PeriodoActualCalificaciones periodoActual;

  @override
  Widget build(BuildContext context) {
    final cursos = periodoActual.cursos;

    // Métricas del período
    final notasFinales = cursos
        .map((c) => double.tryParse(c.promedio?.calificacionSistema ?? ''))
        .whereType<double>()
        .toList();
    final promedioGeneral = notasFinales.isNotEmpty
        ? (notasFinales.reduce((a, b) => a + b) / notasFinales.length)
        : null;
    final aprobados = notasFinales.where((n) => n >= 11.0).length;
    final desaprobados = notasFinales.where((n) => n < 11.0).length;

    final tieneAvisos = (!periodoActual.p1Visible && periodoActual.fechaP1Visible != null) ||
        (!periodoActual.p2Visible && periodoActual.fechaP2Visible != null);

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
      children: [
        // Cabecera institucional del período
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF1B2A4A).withValues(alpha: 0.15),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.gold.withValues(alpha: 0.20),
                      borderRadius: BorderRadius.circular(10),
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
                          periodoActual.periodoNombre != null
                              ? 'Período ${periodoActual.periodoNombre}'
                              : 'Período en curso',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 2),
                        const Text(
                          'Calificaciones del semestre activo',
                          style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 12.5),
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
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              Wrap(
                spacing: 8,
                runSpacing: 6,
                children: [
                  _InfoPill(
                    icon: Icons.menu_book_rounded,
                    label: '${cursos.length} asignaturas',
                  ),
                  const _InfoPill(
                    icon: Icons.check_circle_outline,
                    label: 'Nota mínima: 11',
                  ),
                ],
              ),
            ],
          ),
        ),

        const SizedBox(height: 16),

        // Métricas de desempeño académico (Stats)
        Row(
          children: [
            Expanded(
              child: _StatCard(
                icon: Icons.collections_bookmark_outlined,
                titulo: 'Cursos',
                valor: cursos.length.toString(),
                color: AppColors.teal,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _StatCard(
                icon: Icons.star_rounded,
                titulo: 'Promedio',
                valor: promedioGeneral != null ? promedioGeneral.toStringAsFixed(1) : '—',
                color: AppColors.gold,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _StatCard(
                icon: Icons.check_rounded,
                titulo: 'Aprobados',
                valor: aprobados.toString(),
                color: const Color(0xFF059669),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _StatCard(
                icon: Icons.close_rounded,
                titulo: 'Desaprob.',
                valor: desaprobados.toString(),
                color: const Color(0xFFDC2626),
              ),
            ),
          ],
        ),

        // Aviso de publicación de notas
        if (tieneAvisos) ...[
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFF93C5FD)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.info_outline, color: Color(0xFF2563EB), size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    [
                      if (!periodoActual.p1Visible && periodoActual.fechaP1Visible != null)
                        'Las notas del Parcial 1 se publican el ${periodoActual.fechaP1Visible}.',
                      if (!periodoActual.p2Visible && periodoActual.fechaP2Visible != null)
                        'Las notas del Parcial 2 y el promedio final se publican el ${periodoActual.fechaP2Visible}.',
                    ].join(' '),
                    style: const TextStyle(
                      fontSize: 12.5,
                      color: Color(0xFF1E3A8A),
                      height: 1.35,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],

        const SizedBox(height: 20),

        // Lista de Cursos
        if (cursos.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.border),
            ),
            child: const Column(
              children: [
                Icon(Icons.inbox_outlined, size: 48, color: AppColors.muted),
                SizedBox(height: 12),
                Text(
                  'No hay cursos registrados para este período.',
                  style: TextStyle(color: AppColors.muted, fontSize: 14),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          )
        else
          for (final curso in cursos) ...[
            _CursoActualCard(curso: curso, periodoActual: periodoActual),
            const SizedBox(height: 14),
          ],
      ],
    );
  }
}

class _CursoActualCard extends StatelessWidget {
  const _CursoActualCard({
    required this.curso,
    required this.periodoActual,
  });

  final CursoConNotas curso;
  final PeriodoActualCalificaciones periodoActual;

  @override
  Widget build(BuildContext context) {
    final finalSistema = curso.promedio?.calificacionSistema;
    final notaFinalNum = double.tryParse(finalSistema ?? '');
    final esAprobado = notaFinalNum != null && notaFinalNum >= 11.0;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
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
                    color: AppColors.teal.withValues(alpha: 0.10),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.menu_book_rounded, color: AppColors.teal, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        curso.cursoNombre,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF0F172A),
                          height: 1.25,
                        ),
                      ),
                      if (curso.cicloNombre != null) ...[
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            'Ciclo ${curso.cicloNombre}',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF475569),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                // Badge de Nota Final
                _FinalGradeBadge(
                  notaStr: finalSistema,
                  esAprobado: esAprobado,
                ),
              ],
            ),
          ),

          const Divider(height: 1, color: Color(0xFFF1F5F9)),

          // Tabla Desglose de Parciales
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            child: Column(
              children: [
                // Cabecera de columnas
                const Row(
                  children: [
                    Expanded(
                      flex: 4,
                      child: Text(
                        'EVALUACIÓN',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF94A3B8),
                          letterSpacing: 0.4,
                        ),
                      ),
                    ),
                    Expanded(
                      flex: 3,
                      child: Text(
                        'LOGRO',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF94A3B8),
                          letterSpacing: 0.4,
                        ),
                      ),
                    ),
                    Expanded(
                      flex: 3,
                      child: Text(
                        'VALORACIÓN',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF94A3B8),
                          letterSpacing: 0.4,
                        ),
                      ),
                    ),
                    SizedBox(
                      width: 50,
                      child: Text(
                        'NOTA',
                        textAlign: TextAlign.end,
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF94A3B8),
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
                ),
                const SizedBox(height: 6),

                // Parcial 2
                _ParcialEvaluationRow(
                  label: 'Parcial 2',
                  nota: curso.parcial2,
                  visible: periodoActual.p2Visible,
                  fechaPublicacion: periodoActual.fechaP2Visible,
                ),
                const SizedBox(height: 6),

                // Promedio final
                _ParcialEvaluationRow(
                  label: 'Promedio final',
                  nota: curso.promedio,
                  visible: periodoActual.p2Visible,
                  fechaPublicacion: periodoActual.fechaP2Visible,
                  isFinal: true,
                ),
              ],
            ),
          ),

          // Observaciones docentes si existen
          if (curso.observaciones != null && curso.observaciones!.trim().isNotEmpty) ...[
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: const BoxDecoration(
                color: Color(0xFFF8FAFC),
                borderRadius: BorderRadius.vertical(bottom: Radius.circular(15)),
                border: Border(top: BorderSide(color: Color(0xFFF1F5F9))),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.comment_outlined, size: 16, color: AppColors.goldHover),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Obs. docente: ${curso.observaciones}',
                      style: const TextStyle(
                        fontSize: 12,
                        fontStyle: FontStyle.italic,
                        color: Color(0xFF475569),
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
    );
  }
}

class _ParcialEvaluationRow extends StatelessWidget {
  const _ParcialEvaluationRow({
    required this.label,
    required this.nota,
    required this.visible,
    this.fechaPublicacion,
    this.isFinal = false,
  });

  final String label;
  final NotaParcial? nota;
  final bool visible;
  final String? fechaPublicacion;
  final bool isFinal;

  @override
  Widget build(BuildContext context) {
    final sistema = nota?.calificacionSistema;
    final notaNum = double.tryParse(sistema ?? '');
    final esAprobado = notaNum != null && notaNum >= 11.0;

    return Container(
      padding: EdgeInsets.symmetric(horizontal: isFinal ? 8 : 0, vertical: isFinal ? 6 : 4),
      decoration: BoxDecoration(
        color: isFinal ? const Color(0xFFF8FAFC) : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
        border: isFinal ? Border.all(color: const Color(0xFFE2E8F0)) : null,
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
                fontWeight: isFinal ? FontWeight.w700 : FontWeight.w500,
                color: isFinal ? const Color(0xFF0F172A) : const Color(0xFF334155),
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
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Color(0xFF475569),
                ),
              ),
            ),
            // Columna 3: Valoración
            Expanded(
              flex: 3,
              child: Text(
                nota!.valoracionCurso ?? '—',
                style: const TextStyle(
                  fontSize: 12,
                  color: Color(0xFF64748B),
                ),
              ),
            ),
            // Columna 4: Nota del sistema
            SizedBox(
              width: 50,
              child: Align(
                alignment: Alignment.centerRight,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                  decoration: BoxDecoration(
                    color: notaNum == null
                        ? const Color(0xFFF1F5F9)
                        : (esAprobado ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2)),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: notaNum == null
                          ? const Color(0xFFE2E8F0)
                          : (esAprobado ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA)),
                    ),
                  ),
                  child: Text(
                    sistema ?? '—',
                    style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w800,
                      color: notaNum == null
                          ? const Color(0xFF64748B)
                          : (esAprobado ? const Color(0xFF059669) : const Color(0xFFDC2626)),
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

class _FinalGradeBadge extends StatelessWidget {
  const _FinalGradeBadge({
    required this.notaStr,
    required this.esAprobado,
  });

  final String? notaStr;
  final bool esAprobado;

  @override
  Widget build(BuildContext context) {
    if (notaStr == null || notaStr!.trim().isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: const Color(0xFFE2E8F0)),
        ),
        child: const Column(
          children: [
            Text(
              '—',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w800,
                color: Color(0xFF64748B),
              ),
            ),
            Text(
              'En curso',
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

    final color = esAprobado ? const Color(0xFF059669) : const Color(0xFFDC2626);
    final bgColor = esAprobado ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2);
    final borderColor = esAprobado ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA);

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
              fontWeight: FontWeight.w800,
              color: color,
            ),
          ),
          Text(
            'Final',
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
// TAB 2: PERÍODOS ANTERIORES
// ─────────────────────────────────────────────────────────────────────────────

class _PeriodosAnterioresTab extends ConsumerStatefulWidget {
  const _PeriodosAnterioresTab();

  @override
  ConsumerState<_PeriodosAnterioresTab> createState() => _PeriodosAnterioresTabState();
}

class _PeriodosAnterioresTabState extends ConsumerState<_PeriodosAnterioresTab> {
  int _selectedPeriodoIndex = 0;

  @override
  Widget build(BuildContext context) {
    final calificaciones = ref.watch(calificacionesProvider);

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
                const Text(
                  'Sin períodos anteriores',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Actualmente te encuentras cursando tu primer período o aún no se registran actas cerradas de semestres anteriores.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13.5,
                    color: Color(0xFF64748B),
                    height: 1.4,
                  ),
                ),
              ],
            );
          }

          final safeIndex = _selectedPeriodoIndex.clamp(0, periodos.length - 1);
          final grupoActual = periodos[safeIndex];

          // Cálculo de promedio del período seleccionado
          final notas = grupoActual.calificaciones
              .map((c) => double.tryParse(c.calificacionSistema ?? ''))
              .whereType<double>()
              .toList();
          final promedioGrupo = notas.isNotEmpty
              ? (notas.reduce((a, b) => a + b) / notas.length)
              : null;
          final aprobadas = notas.where((n) => n >= 11.0).length;

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
                          color: isSelected ? Colors.white : const Color(0xFF1E293B),
                        ),
                      ),
                      backgroundColor: Colors.white,
                      selectedColor: AppColors.teal,
                      side: BorderSide(
                        color: isSelected ? AppColors.teal : const Color(0xFFCBD5E1),
                        width: isSelected ? 1.5 : 1,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                      onSelected: (selected) {
                        if (selected) {
                          setState(() => _selectedPeriodoIndex = index);
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
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0F172A).withValues(alpha: 0.04),
                      blurRadius: 10,
                      offset: const Offset(0, 3),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEF3C7),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(
                            Icons.auto_stories_rounded,
                            color: Color(0xFFD97706),
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
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${grupoActual.calificaciones.length} ${grupoActual.calificaciones.length == 1 ? "curso cursado" : "cursos cursados"}',
                                style: const TextStyle(
                                  fontSize: 12.5,
                                  color: Color(0xFF64748B),
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
                                  ? const Color(0xFFECFDF5)
                                  : const Color(0xFFFEF2F2),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: promedioGrupo >= 11.0
                                    ? const Color(0xFFA7F3D0)
                                    : const Color(0xFFFECACA),
                              ),
                            ),
                            child: Column(
                              children: [
                                Text(
                                  promedioGrupo.toStringAsFixed(1),
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w800,
                                    color: promedioGrupo >= 11.0
                                        ? const Color(0xFF059669)
                                        : const Color(0xFFDC2626),
                                  ),
                                ),
                                Text(
                                  'Promedio',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w700,
                                    color: promedioGrupo >= 11.0
                                        ? const Color(0xFF059669)
                                        : const Color(0xFFDC2626),
                                  ),
                                ),
                              ],
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    const Divider(height: 1, color: Color(0xFFF1F5F9)),
                    const SizedBox(height: 10),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Aprobadas: $aprobadas de ${grupoActual.calificaciones.length}',
                          style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF475569),
                          ),
                        ),
                        const Text(
                          'Mínima aprobatoria: 11',
                          style: TextStyle(
                            fontSize: 12,
                            color: Color(0xFF94A3B8),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 16),

              // Lista de asignaturas del período seleccionado
              for (final cal in grupoActual.calificaciones) ...[
                _CalificacionAnteriorCard(calificacion: cal),
                const SizedBox(height: 12),
              ],
            ],
          );
        },
        loading: () => const Center(
          child: CircularProgressIndicator(),
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

class _CalificacionAnteriorCard extends StatelessWidget {
  const _CalificacionAnteriorCard({required this.calificacion});

  final Calificacion calificacion;

  @override
  Widget build(BuildContext context) {
    final sistema = calificacion.calificacionSistema;
    final notaNum = double.tryParse(sistema ?? '');
    final esAprobado = notaNum != null && notaNum >= 11.0;

    final subInfo = [
      if (calificacion.programaNombre != null && calificacion.programaNombre!.isNotEmpty)
        calificacion.programaNombre!,
      if (calificacion.cicloNombre != null && calificacion.cicloNombre!.isNotEmpty)
        'Ciclo ${calificacion.cicloNombre}',
      if (calificacion.creditos != null && calificacion.creditos! > 0)
        '${calificacion.creditos} créd.',
    ].join(' · ');

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
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
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.history_rounded, color: AppColors.teal, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        calificacion.cursoNombre ?? 'Curso #${calificacion.cursoId}',
                        style: const TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF0F172A),
                          height: 1.25,
                        ),
                      ),
                      if (subInfo.isNotEmpty) ...[
                        const SizedBox(height: 3),
                        Text(
                          subInfo,
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: Color(0xFF64748B),
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
                        ? const Color(0xFFF1F5F9)
                        : (esAprobado ? const Color(0xFFECFDF5) : const Color(0xFFFEF2F2)),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: notaNum == null
                          ? const Color(0xFFE2E8F0)
                          : (esAprobado ? const Color(0xFFA7F3D0) : const Color(0xFFFECACA)),
                    ),
                  ),
                  child: Column(
                    children: [
                      Text(
                        sistema ?? '—',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                          color: notaNum == null
                              ? const Color(0xFF64748B)
                              : (esAprobado ? const Color(0xFF059669) : const Color(0xFFDC2626)),
                        ),
                      ),
                      Text(
                        esAprobado ? 'Aprobado' : (notaNum == null ? 'Sin nota' : 'Desaprob.'),
                        style: TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.w700,
                          color: notaNum == null
                              ? const Color(0xFF64748B)
                              : (esAprobado ? const Color(0xFF059669) : const Color(0xFFDC2626)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            const SizedBox(height: 12),
            const Divider(height: 1, color: Color(0xFFF1F5F9)),
            const SizedBox(height: 10),

            // Métricas secundarias: Logro y Valoración
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'NIVEL DE LOGRO',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF94A3B8),
                          letterSpacing: 0.3,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        calificacion.calificacionCurso ?? '—',
                        style: const TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF334155),
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'VALORACIÓN',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF94A3B8),
                          letterSpacing: 0.3,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        calificacion.valoracionCurso ?? '—',
                        style: const TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF334155),
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
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// COMPONENTES AUXILIARES
// ─────────────────────────────────────────────────────────────────────────────

class _InfoPill extends StatelessWidget {
  const _InfoPill({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: const Color(0xFFCBD5E1), size: 14),
          const SizedBox(width: 6),
          Text(
            label,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 11.5,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
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
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 4),
          Text(
            valor,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w800,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            titulo,
            style: const TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.w600,
              color: Color(0xFF64748B),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
