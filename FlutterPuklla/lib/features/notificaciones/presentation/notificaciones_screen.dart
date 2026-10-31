import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../routing/app_drawer.dart';
import '../application/notificaciones_controller.dart';
import '../application/notificaciones_providers.dart';
import '../data/notificacion.dart';
import '../data/notificaciones_page.dart';

class NotificacionesScreen extends ConsumerWidget {
  const NotificacionesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pagina = ref.watch(notificacionesProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notificaciones'),
        actions: [
          TextButton(
            onPressed: () => ref.read(notificacionesControllerProvider).marcarTodasLeidas(),
            child: const Text('Marcar todas', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
      drawer: const AppDrawer(),
      body: pagina.when(
        data: (data) => data.items.isEmpty
            ? const EmptyState(icon: Icons.notifications_none, message: 'No tienes notificaciones todavía.')
            : Column(
                children: [
                  Expanded(
                    child: RefreshIndicator(
                      onRefresh: () => ref.refresh(notificacionesProvider.future),
                      child: ListView(
                        padding: const EdgeInsets.all(16),
                        children: _agrupadasPorFecha(data.items, context),
                      ),
                    ),
                  ),
                  _PaginadorBar(pagina: data),
                ],
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Padding(
          padding: const EdgeInsets.all(16),
          child: ErrorState(
            message: error is ApiException ? error.message : 'No se pudieron cargar las notificaciones.',
            onRetry: () => ref.refresh(notificacionesProvider),
          ),
        ),
      ),
    );
  }
}

List<Widget> _agrupadasPorFecha(List<Notificacion> items, BuildContext context) {
  final widgets = <Widget>[];
  String? grupoActual;

  for (final notificacion in items) {
    final grupo = _grupoParaFecha(notificacion.createdAt);
    if (grupo != grupoActual) {
      grupoActual = grupo;
      widgets.add(
        Padding(
          padding: const EdgeInsets.only(top: 16, bottom: 8),
          child: Text(
            grupo,
            style: Theme.of(context)
                .textTheme
                .titleSmall
                ?.copyWith(color: AppColors.muted, fontWeight: FontWeight.w700),
          ),
        ),
      );
    }
    widgets.add(_NotificacionTile(notificacion: notificacion));
  }

  return widgets;
}

String _grupoParaFecha(DateTime fecha) {
  final ahora = DateTime.now();
  final hoy = DateTime(ahora.year, ahora.month, ahora.day);
  final dia = DateTime(fecha.year, fecha.month, fecha.day);
  final diferenciaDias = hoy.difference(dia).inDays;

  if (diferenciaDias == 0) return 'Hoy';
  if (diferenciaDias == 1) return 'Ayer';
  if (diferenciaDias <= 7) return 'Esta semana';
  return 'Anteriores';
}

class _NotificacionTile extends ConsumerWidget {
  const _NotificacionTile({required this.notificacion});

  final Notificacion notificacion;

  IconData get _icono {
    switch (notificacion.tipo) {
      case 'CalificacionRegistrada':
        return Icons.grade_outlined;
      case 'ComunicadoPublicado':
        return Icons.campaign_outlined;
      case 'MatriculaVoucherVerificado':
        return Icons.verified_outlined;
      default:
        return Icons.notifications_outlined;
    }
  }

  String get _destino {
    switch (notificacion.tipo) {
      case 'CalificacionRegistrada':
        return '/calificaciones';
      case 'ComunicadoPublicado':
        return '/comunicados';
      case 'MatriculaVoucherVerificado':
        return '/home';
      default:
        return '/home';
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      color: notificacion.leida ? null : AppColors.gold.withValues(alpha: 0.05),
      child: ListTile(
        leading: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: (notificacion.leida ? AppColors.muted : AppColors.gold).withValues(alpha: 0.14),
            shape: BoxShape.circle,
          ),
          child: Icon(_icono, size: 19, color: notificacion.leida ? AppColors.muted : AppColors.gold),
        ),
        title: Text(
          notificacion.mensaje,
          style: TextStyle(fontWeight: notificacion.leida ? FontWeight.normal : FontWeight.w700),
        ),
        subtitle: Text(_formatFechaHora(notificacion.createdAt)),
        trailing: notificacion.leida
            ? null
            : const CircleAvatar(radius: 4, backgroundColor: AppColors.gold),
        onTap: () async {
          if (!notificacion.leida) {
            await ref.read(notificacionesControllerProvider).marcarLeida(notificacion.id);
          }
          if (context.mounted) context.push(_destino);
        },
      ),
    );
  }
}

class _PaginadorBar extends ConsumerWidget {
  const _PaginadorBar({required this.pagina});

  final NotificacionesPage pagina;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    if (pagina.lastPage <= 1) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          TextButton(
            onPressed: pagina.hayPaginaAnterior
                ? () => ref.read(notificacionesPageIndexProvider.notifier).state--
                : null,
            child: const Text('Anterior'),
          ),
          Text('Página ${pagina.currentPage} de ${pagina.lastPage}'),
          TextButton(
            onPressed: pagina.hayPaginaSiguiente
                ? () => ref.read(notificacionesPageIndexProvider.notifier).state++
                : null,
            child: const Text('Siguiente'),
          ),
        ],
      ),
    );
  }
}

String _formatFechaHora(DateTime fecha) {
  final dia = fecha.day.toString().padLeft(2, '0');
  final mes = fecha.month.toString().padLeft(2, '0');
  final hora = fecha.hour.toString().padLeft(2, '0');
  final minuto = fecha.minute.toString().padLeft(2, '0');
  return '$dia/$mes · $hora:$minuto';
}
