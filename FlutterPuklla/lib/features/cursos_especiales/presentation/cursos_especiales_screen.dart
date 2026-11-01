import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../application/cursos_especiales_controller.dart';
import '../application/cursos_especiales_providers.dart';
import '../data/curso_especial.dart';

class CursosEspecialesScreen extends ConsumerStatefulWidget {
  const CursosEspecialesScreen({super.key});

  @override
  ConsumerState<CursosEspecialesScreen> createState() => _CursosEspecialesScreenState();
}

class _CursosEspecialesScreenState extends ConsumerState<CursosEspecialesScreen> {
  final TextEditingController _searchController = TextEditingController();
  int _filtroEstado = 0; // 0: Todos, 1: Inscritos, 2: Por inscribirme

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final catalogo = ref.watch(cursosEspecialesProvider);
    final isDark = PukllaDashboardStyles.isDark(context);

    return Scaffold(
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
      appBar: AppBar(
        title: const Text('Cursos Asincrónicos'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(cursosEspecialesProvider.future),
        child: catalogo.when(
          data: (cursos) => _buildContent(cursos, isDark),
          loading: () => _buildSkeletonLoader(isDark),
          error: (error, _) => Padding(
            padding: const EdgeInsets.all(24),
            child: ErrorState(
              message: error is ApiException ? error.message : 'No se pudo cargar el catálogo de cursos asincrónicos.',
              onRetry: () => ref.invalidate(cursosEspecialesProvider),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSkeletonLoader(bool isDark) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
      children: const [
        PukllaSkeletonCard(height: 150),
        SizedBox(height: 16),
        Row(
          children: [
            Expanded(child: PukllaSkeletonCard(height: 68)),
            SizedBox(width: 8),
            Expanded(child: PukllaSkeletonCard(height: 68)),
            SizedBox(width: 8),
            Expanded(child: PukllaSkeletonCard(height: 68)),
          ],
        ),
        SizedBox(height: 16),
        PukllaSkeletonCard(height: 110),
        SizedBox(height: 12),
        PukllaSkeletonCard(height: 110),
      ],
    );
  }

  Widget _buildContent(List<CursoEspecial> cursos, bool isDark) {
    final inscritosCount = cursos.where((c) => c.inscrito).length;
    final porInscribirmeCount = cursos.length - inscritosCount;

    final query = _searchController.text.trim().toLowerCase();
    final cursosFiltrados = cursos.where((c) {
      if (query.isNotEmpty) {
        final matchesNombre = c.nombre.toLowerCase().contains(query);
        final matchesDesc = c.descripcion?.toLowerCase().contains(query) ?? false;
        if (!matchesNombre && !matchesDesc) return false;
      }
      if (_filtroEstado == 1) return c.inscrito;
      if (_filtroEstado == 2) return !c.inscrito;
      return true;
    }).toList();

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
      children: [
        // ── HERO BANNER SENIOR ──
        _CursosHeroBanner(
          totalCursos: cursos.length,
          inscritosCount: inscritosCount,
          isDark: isDark,
        ),
        const SizedBox(height: 16),

        // ── STATS ROW ──
        Row(
          children: [
            Expanded(
              child: _CursosStatCard(
                icon: Icons.menu_book_rounded,
                titulo: 'Disponibles',
                valor: cursos.length.toString(),
                color: AppColors.teal,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _CursosStatCard(
                icon: Icons.play_circle_outline_rounded,
                titulo: 'Mis Cursos',
                valor: inscritosCount.toString(),
                color: const Color(0xFF10B981),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _CursosStatCard(
                icon: Icons.add_circle_outline_rounded,
                titulo: 'Por Cursar',
                valor: porInscribirmeCount.toString(),
                color: AppColors.goldHover,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // ── BUSCADOR EN VIVO ──
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
                    hintText: 'Buscar en catálogo de cursos asincrónicos...',
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

        // ── FILTROS CHIPS ──
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              _CursosFilterChip(
                label: 'Todos (${cursos.length})',
                isSelected: _filtroEstado == 0,
                onTap: () => setState(() => _filtroEstado = 0),
                isDark: isDark,
              ),
              const SizedBox(width: 8),
              _CursosFilterChip(
                label: 'Inscritos ($inscritosCount)',
                isSelected: _filtroEstado == 1,
                onTap: () => setState(() => _filtroEstado = 1),
                isDark: isDark,
              ),
              const SizedBox(width: 8),
              _CursosFilterChip(
                label: 'Por Inscribirme ($porInscribirmeCount)',
                isSelected: _filtroEstado == 2,
                onTap: () => setState(() => _filtroEstado = 2),
                isDark: isDark,
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // ── LISTADO DE CURSOS ──
        if (cursos.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
            child: Column(
              children: [
                const Icon(Icons.school_outlined, size: 48, color: AppColors.muted),
                const SizedBox(height: 12),
                Text(
                  'No hay cursos asincrónicos disponibles por ahora.',
                  style: TextStyle(
                    color: PukllaDashboardStyles.textPrimary(context),
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 6),
                Text(
                  'Pronto se abrirán nuevas convocatorias para talleres y certificaciones pedagógicas.',
                  style: TextStyle(
                    color: PukllaDashboardStyles.textSecondary(context),
                    fontSize: 12.5,
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
                  'No se encontraron cursos con ese término de búsqueda.',
                  style: TextStyle(
                    color: PukllaDashboardStyles.textPrimary(context),
                    fontSize: 13.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          )
        else
          for (final curso in cursosFiltrados) ...[
            _CursoEspecialSeniorCard(curso: curso),
            const SizedBox(height: 14),
          ],
      ],
    );
  }
}

class _CursosHeroBanner extends StatelessWidget {
  const _CursosHeroBanner({
    required this.totalCursos,
    required this.inscritosCount,
    required this.isDark,
  });

  final int totalCursos;
  final int inscritosCount;
  final bool isDark;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
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
                child: const Icon(Icons.auto_stories_rounded, color: AppColors.gold, size: 22),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Cursos Asincrónicos & Extensión',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.2,
                      ),
                    ),
                    SizedBox(height: 2),
                    Text(
                      'Formación continua y certificaciones',
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
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.verified_outlined, color: Color(0xFF10B981), size: 10),
                    const SizedBox(width: 5),
                    Text(
                      '$inscritosCount Inscritos',
                      style: const TextStyle(
                        color: Color(0xFF10B981),
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Text(
            'Inscríbete en talleres complementarios, programas de especialización y fortalece tus competencias pedagógicas.',
            style: TextStyle(
              fontSize: 12.5,
              color: Color(0xFFE2E8F0),
              height: 1.35,
            ),
          ),
        ],
      ),
    );
  }
}

class _CursosStatCard extends StatelessWidget {
  const _CursosStatCard({
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
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 16, color: color),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  titulo,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: PukllaDashboardStyles.textSecondary(context),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            valor,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w900,
              color: PukllaDashboardStyles.textPrimary(context),
            ),
          ),
        ],
      ),
    );
  }
}

class _CursosFilterChip extends StatelessWidget {
  const _CursosFilterChip({
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
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected
              ? AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.12)
              : (isDark ? AppColors.darkSurface : Colors.white),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected
                ? AppColors.teal
                : (isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0)),
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected
                ? (isDark ? const Color(0xFF5EEAD4) : AppColors.teal)
                : PukllaDashboardStyles.textSecondary(context),
          ),
        ),
      ),
    );
  }
}

class _CursoEspecialSeniorCard extends ConsumerWidget {
  const _CursoEspecialSeniorCard({required this.curso});

  final CursoEspecial curso;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final accentColor = curso.inscrito ? const Color(0xFF10B981) : AppColors.gold;
    final porcentaje = curso.porcentaje ?? 0;

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
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 38,
                            height: 38,
                            decoration: BoxDecoration(
                              color: accentColor.withValues(alpha: isDark ? 0.22 : 0.12),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(
                              curso.inscrito ? Icons.play_circle_outline_rounded : Icons.auto_stories_rounded,
                              size: 20,
                              color: accentColor,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  curso.nombre,
                                  style: TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textPrimary(context),
                                    height: 1.25,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: curso.inscrito
                                        ? const Color(0xFF10B981).withValues(alpha: 0.14)
                                        : AppColors.gold.withValues(alpha: 0.14),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    curso.inscrito ? 'Inscrito · En proceso' : 'Disponible para inscripción',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w700,
                                      color: curso.inscrito
                                          ? (isDark ? const Color(0xFF34D399) : const Color(0xFF047857))
                                          : AppColors.goldHover,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      if (curso.descripcion != null && curso.descripcion!.trim().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        Text(
                          curso.descripcion!,
                          style: TextStyle(
                            fontSize: 13,
                            color: PukllaDashboardStyles.textSecondary(context),
                            height: 1.4,
                          ),
                        ),
                      ],
                      const SizedBox(height: 14),
                      Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
                      const SizedBox(height: 10),
                      if (curso.inscrito) ...[
                        Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        'Progreso del curso',
                                        style: TextStyle(
                                          fontSize: 11.5,
                                          fontWeight: FontWeight.w600,
                                          color: PukllaDashboardStyles.textSecondary(context),
                                        ),
                                      ),
                                      Text(
                                        '$porcentaje%',
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w900,
                                          color: Color(0xFF10B981),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(999),
                                    child: LinearProgressIndicator(
                                      value: porcentaje / 100.0,
                                      minHeight: 6,
                                      backgroundColor: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFE2E8F0),
                                      valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF10B981)),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 16),
                            FilledButton.icon(
                              onPressed: () => context.push('/cursos-especiales/${curso.id}'),
                              style: FilledButton.styleFrom(
                                backgroundColor: AppColors.teal,
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              icon: const Icon(Icons.arrow_forward_rounded, size: 16),
                              label: const Text('Continuar', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
                            ),
                          ],
                        ),
                      ] else ...[
                        Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            FilledButton.icon(
                              onPressed: () async {
                                try {
                                  await ref.read(cursosEspecialesControllerProvider).inscribir(curso.id);
                                } on ApiException catch (e) {
                                  if (context.mounted) {
                                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
                                  }
                                }
                              },
                              style: FilledButton.styleFrom(
                                backgroundColor: AppColors.goldHover,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              icon: const Icon(Icons.add_task_rounded, size: 16),
                              label: const Text('Inscribirme ahora', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5)),
                            ),
                          ],
                        ),
                      ],
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
