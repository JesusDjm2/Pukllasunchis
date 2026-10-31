import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
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
      appBar: AppBar(title: const Text('Mis incidencias')),
      drawer: const AppDrawer(),
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
        loading: () => const Center(child: CircularProgressIndicator()),
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
                  backgroundColor: AppColors.teal.withValues(alpha: 0.1),
                  child: Text(
                    (incidencia.alumnoNombre?.isNotEmpty ?? false) ? incidencia.alumnoNombre![0].toUpperCase() : '?',
                    style: const TextStyle(color: AppColors.teal, fontWeight: FontWeight.w700, fontSize: 13),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    incidencia.alumnoNombre ?? 'Alumno',
                    style: const TextStyle(fontWeight: FontWeight.w700),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                  decoration: BoxDecoration(
                    color: incidencia.estaPendiente ? AppColors.warningBg : AppColors.successBg,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    incidencia.estado,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: incidencia.estaPendiente ? AppColors.warning : AppColors.success,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              '${incidencia.fecha.toLocal().toString().split(' ').first}'
              '${incidencia.cicloNombre == null ? '' : ' · ${incidencia.cicloNombre}'}',
              style: const TextStyle(fontSize: 12, color: AppColors.muted),
            ),
            const SizedBox(height: 8),
            Text(incidencia.reporte),
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
