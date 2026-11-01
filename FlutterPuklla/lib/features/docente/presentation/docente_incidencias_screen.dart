import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../../../routing/app_drawer.dart';
import '../application/docente_providers.dart';
import '../data/docente_incidencia.dart';

/// Incidencias registradas por el docente sobre sus alumnos
/// (`GET /api/v1/incidencias`), la misma lista que `docentes/incidencias/index`
/// en la web.
class DocenteIncidenciasScreen extends ConsumerWidget {
  const DocenteIncidenciasScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pagina = ref.watch(docenteIncidenciasProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mis incidencias'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: pagina.when(
        data: (data) => data.items.isEmpty
            ? const EmptyState(
                icon: Icons.task_alt_outlined,
                message: 'Sin incidencias registradas.',
              )
            : Column(
                children: [
                  Expanded(
                    child: RefreshIndicator(
                      onRefresh: () async {
                        ref.invalidate(docenteIncidenciasProvider);
                        await ref.read(docenteIncidenciasProvider.future);
                      },
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: data.items.length,
                        itemBuilder: (context, index) => Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: _TarjetaIncidencia(incidencia: data.items[index]),
                        ),
                      ),
                    ),
                  ),
                  _Paginador(pagina: data, ref: ref),
                ],
              ),
        loading: () => const Padding(
          padding: EdgeInsets.symmetric(vertical: 40),
          child: PukllaPreloader.inline(mensaje: 'Cargando incidencias registradas...'),
        ),
        error: (e, _) => Padding(
          padding: const EdgeInsets.all(16),
          child: ErrorState(
            message: 'No se pudieron cargar tus incidencias.',
            onRetry: () => ref.invalidate(docenteIncidenciasProvider),
          ),
        ),
      ),
    );
  }
}

class _TarjetaIncidencia extends StatelessWidget {
  const _TarjetaIncidencia({required this.incidencia});

  final DocenteIncidencia incidencia;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    final badgeBg = incidencia.estaPendiente
        ? (isDark ? const Color(0xFF78350F).withValues(alpha: 0.35) : AppColors.warningBg)
        : (isDark ? const Color(0xFF064E3B).withValues(alpha: 0.35) : AppColors.successBg);

    final badgeTextColor = incidencia.estaPendiente
        ? (isDark ? const Color(0xFFFBBF24) : AppColors.warning)
        : (isDark ? const Color(0xFF34D399) : AppColors.success);

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 16,
                  backgroundColor: AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.12),
                  child: Text(
                    (incidencia.alumnoNombre?.isNotEmpty ?? false) ? incidencia.alumnoNombre![0].toUpperCase() : '?',
                    style: TextStyle(
                      color: isDark ? AppColors.goldHover : AppColors.teal,
                      fontWeight: FontWeight.w700,
                      fontSize: 13,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    incidencia.alumnoNombre ?? 'Alumno',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                  decoration: BoxDecoration(
                    color: badgeBg,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: badgeTextColor.withValues(alpha: 0.35),
                    ),
                  ),
                  child: Text(
                    incidencia.estado,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: badgeTextColor,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              '${incidencia.fecha.toLocal().toString().split(' ').first}'
              '${incidencia.cicloNombre == null ? '' : ' · ${incidencia.cicloNombre}'}',
              style: TextStyle(
                fontSize: 12,
                color: isDark ? AppColors.darkMuted : AppColors.muted,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              incidencia.reporte,
              style: TextStyle(
                color: isDark ? AppColors.darkText : const Color(0xFF334155),
                height: 1.4,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Paginador extends StatelessWidget {
  const _Paginador({required this.pagina, required this.ref});

  final DocenteIncidenciasPage pagina;
  final WidgetRef ref;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          TextButton.icon(
            onPressed: pagina.hayPaginaAnterior
                ? () => ref.read(docenteIncidenciasPageIndexProvider.notifier).state = pagina.currentPage - 1
                : null,
            icon: const Icon(Icons.chevron_left),
            label: const Text('Anteriores'),
          ),
          Text('${pagina.total} incidencias', style: const TextStyle(color: AppColors.muted, fontSize: 12)),
          TextButton.icon(
            onPressed: pagina.hayPaginaSiguiente
                ? () => ref.read(docenteIncidenciasPageIndexProvider.notifier).state = pagina.currentPage + 1
                : null,
            icon: const Icon(Icons.chevron_right),
            label: const Text('Siguientes'),
          ),
        ],
      ),
    );
  }
}
