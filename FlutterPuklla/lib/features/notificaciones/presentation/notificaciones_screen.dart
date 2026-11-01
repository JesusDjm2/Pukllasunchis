import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../application/notificaciones_controller.dart';
import '../application/notificaciones_providers.dart';
import '../data/notificacion.dart';
import '../data/notificaciones_page.dart';

class NotificacionesScreen extends ConsumerStatefulWidget {
  const NotificacionesScreen({super.key});

  @override
  ConsumerState<NotificacionesScreen> createState() => _NotificacionesScreenState();
}

class _NotificacionesScreenState extends ConsumerState<NotificacionesScreen> {
  int _filtroSeleccionado = 0; // 0: Todas, 1: Solo no leídas

  @override
  Widget build(BuildContext context) {
    final pagina = ref.watch(notificacionesProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notificaciones'),
        actions: [
          TextButton.icon(
            onPressed: () async {
              await ref.read(notificacionesControllerProvider).marcarTodasLeidas();
              ref.invalidate(notificacionesProvider);
            },
            icon: const Icon(Icons.done_all, size: 16, color: Colors.white),
            label: const Text('Marcar todas', style: TextStyle(color: Colors.white, fontSize: 12)),
          ),
          Builder(
            builder: (ctx) => IconButton(
              icon: const Icon(Icons.menu_rounded),
              tooltip: 'Menú principal',
              onPressed: () => Scaffold.of(ctx).openEndDrawer(),
            ),
          ),
          const SizedBox(width: 4),
        ],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: pagina.when(
        data: (data) {
          // Orden estrictamente cronológico (más recientes primero)
          final itemsOrdenados = List<Notificacion>.from(data.items)
            ..sort((a, b) => b.createdAt.compareTo(a.createdAt));

          final itemsFiltrados = _filtroSeleccionado == 1
              ? itemsOrdenados.where((n) => !n.leida).toList()
              : itemsOrdenados;

          final noLeidasCount = itemsOrdenados.where((n) => !n.leida).length;

          return Column(
            children: [
              // Barra de filtros senior (Todas vs No leídas)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                decoration: const BoxDecoration(
                  color: Colors.white,
                  border: Border(bottom: BorderSide(color: Color(0xFFE2E8F0))),
                ),
                child: Row(
                  children: [
                    _FiltroChip(
                      label: 'Todas (${itemsOrdenados.length})',
                      isSelected: _filtroSeleccionado == 0,
                      onTap: () => setState(() => _filtroSeleccionado = 0),
                    ),
                    const SizedBox(width: 8),
                    _FiltroChip(
                      label: 'No leídas ($noLeidasCount)',
                      isSelected: _filtroSeleccionado == 1,
                      badgeCount: noLeidasCount,
                      onTap: () => setState(() => _filtroSeleccionado = 1),
                    ),
                  ],
                ),
              ),

              // Lista de notificaciones
              Expanded(
                child: itemsFiltrados.isEmpty
                    ? Center(
                        child: EmptyState(
                          icon: _filtroSeleccionado == 1 ? Icons.mark_email_read_outlined : Icons.notifications_none,
                          message: _filtroSeleccionado == 1
                              ? '¡Estás al día! No tienes notificaciones pendientes.'
                              : 'No tienes notificaciones registradas todavía.',
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () => ref.refresh(notificacionesProvider.future),
                        child: ListView(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                          children: _agrupadasPorFecha(itemsFiltrados, context),
                        ),
                      ),
              ),
              _PaginadorBar(pagina: data),
            ],
          );
        },
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

class _FiltroChip extends StatelessWidget {
  const _FiltroChip({
    required this.label,
    required this.isSelected,
    required this.onTap,
    this.badgeCount = 0,
  });

  final String label;
  final bool isSelected;
  final VoidCallback onTap;
  final int badgeCount;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.gold.withValues(alpha: 0.16) : const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? AppColors.goldHover : Colors.transparent,
            width: 1.2,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12.5,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected ? AppColors.goldHover : const Color(0xFF475569),
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
          padding: const EdgeInsets.only(top: 14, bottom: 8),
          child: Row(
            children: [
              Text(
                grupo,
                style: const TextStyle(
                  color: Color(0xFF64748B),
                  fontWeight: FontWeight.w800,
                  fontSize: 12.5,
                  letterSpacing: 0.3,
                ),
              ),
              const SizedBox(width: 8),
              const Expanded(child: Divider(color: Color(0xFFE2E8F0), thickness: 1)),
            ],
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
      case 'TaqeInvitacionGrupo':
      case 'TaqeMiembroAcepto':
      case 'TaqeMiembroSalio':
      case 'TaqeIntegranteRetirado':
        return Icons.science_outlined;
      case 'TutoriaCitaCreada':
      case 'TutoriaCitaActualizada':
        return Icons.support_agent_outlined;
      default:
        return Icons.notifications_outlined;
    }
  }

  Color get _colorTipo {
    switch (notificacion.tipo) {
      case 'CalificacionRegistrada':
        return const Color(0xFF10B981);
      case 'ComunicadoPublicado':
        return AppColors.goldHover;
      case 'MatriculaVoucherVerificado':
        return const Color(0xFF0284C7);
      case 'TaqeInvitacionGrupo':
      case 'TaqeMiembroAcepto':
        return const Color(0xFF059669);
      case 'TutoriaCitaCreada':
        return const Color(0xFF7C3AED);
      default:
        return AppColors.teal;
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
      case 'TaqeInvitacionGrupo':
      case 'TaqeMiembroAcepto':
      case 'TaqeMiembroSalio':
      case 'TaqeIntegranteRetirado':
        return '/taqe';
      case 'TutoriaCitaCreada':
      case 'TutoriaCitaActualizada':
        return '/tutoria';
      default:
        return '/home';
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final color = _colorTipo;

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: notificacion.leida ? Colors.white : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: notificacion.leida ? const Color(0xFFE2E8F0) : color.withValues(alpha: 0.35),
          width: notificacion.leida ? 1 : 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: notificacion.leida ? 0.02 : 0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: () async {
            if (!notificacion.leida) {
              await ref.read(notificacionesControllerProvider).marcarLeida(notificacion.id);
            }
            if (context.mounted) {
              context.push(_destino);
            }
          },
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Ícono del tipo de notificación
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: (notificacion.leida ? const Color(0xFF64748B) : color).withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    _icono,
                    size: 20,
                    color: notificacion.leida ? const Color(0xFF64748B) : color,
                  ),
                ),
                const SizedBox(width: 12),

                // Contenido y fecha
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              notificacion.mensaje,
                              style: TextStyle(
                                fontSize: 13.5,
                                height: 1.35,
                                fontWeight: notificacion.leida ? FontWeight.w500 : FontWeight.w700,
                                color: notificacion.leida ? const Color(0xFF334155) : const Color(0xFF0F172A),
                              ),
                            ),
                          ),
                          if (!notificacion.leida) ...[
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.gold.withValues(alpha: 0.18),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'NUEVA',
                                style: TextStyle(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.goldHover,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          Icon(
                            Icons.access_time_rounded,
                            size: 12.5,
                            color: const Color(0xFF94A3B8),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            _formatFechaHora(notificacion.createdAt),
                            style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                          ),
                          const Spacer(),
                          Text(
                            notificacion.leida ? 'Leída' : 'Toca para abrir',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: notificacion.leida ? const Color(0xFF94A3B8) : AppColors.goldHover,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
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

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          TextButton.icon(
            onPressed: pagina.hayPaginaAnterior
                ? () => ref.read(notificacionesPageIndexProvider.notifier).state--
                : null,
            icon: const Icon(Icons.chevron_left, size: 18),
            label: const Text('Anterior'),
          ),
          Text(
            'Página ${pagina.currentPage} de ${pagina.lastPage}',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
          ),
          TextButton.icon(
            onPressed: pagina.hayPaginaSiguiente
                ? () => ref.read(notificacionesPageIndexProvider.notifier).state++
                : null,
            label: const Text('Siguiente'),
            icon: const Icon(Icons.chevron_right, size: 18),
          ),
        ],
      ),
    );
  }
}

String _formatFechaHora(DateTime fecha) {
  final dia = fecha.day.toString().padLeft(2, '0');
  final mes = fecha.month.toString().padLeft(2, '0');
  final anio = fecha.year;
  final hora = fecha.hour.toString().padLeft(2, '0');
  final minuto = fecha.minute.toString().padLeft(2, '0');
  return '$dia/$mes/$anio · $hora:$minuto';
}
