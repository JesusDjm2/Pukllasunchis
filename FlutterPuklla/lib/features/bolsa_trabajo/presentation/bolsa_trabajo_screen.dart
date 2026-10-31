import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../routing/app_drawer.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';
import '../application/bolsa_trabajo_providers.dart';
import '../data/bolsa_trabajo_oferta.dart';

class BolsaTrabajoScreen extends ConsumerWidget {
  const BolsaTrabajoScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final ofertasAsync = ref.watch(bolsaTrabajoProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Bolsa de trabajo'),
        actions: const [NotificationBellAction()],
      ),
      drawer: const AppDrawer(),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(bolsaTrabajoProvider.future),
        child: ofertasAsync.when(
          data: (ofertas) {
            if (ofertas.isEmpty) {
              return const EmptyState(
                icon: Icons.work_outline,
                message: 'No hay ofertas laborales disponibles por ahora. Vuelve pronto para ver convocatorias y prácticas docentes.',
              );
            }

            return ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
              itemCount: ofertas.length,
              itemBuilder: (context, index) {
                final oferta = ofertas[index];
                return _OfertaCard(oferta: oferta);
              },
            );
          },
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: ErrorState(
                message: 'No se pudo cargar la bolsa de trabajo.',
                onRetry: () => ref.refresh(bolsaTrabajoProvider),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _OfertaCard extends StatelessWidget {
  const _OfertaCard({required this.oferta});

  final BolsaTrabajoOferta oferta;

  @override
  Widget build(BuildContext context) {
    final fecha = oferta.fechaPublicacion;
    final fechaTexto = fecha != null
        ? '${fecha.day.toString().padLeft(2, '0')}/${fecha.month.toString().padLeft(2, '0')}/${fecha.year}'
        : 'Reciente';

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.06),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
        border: Border.all(color: Colors.black.withValues(alpha: 0.04)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (oferta.imagenUrl != null && oferta.imagenUrl!.isNotEmpty)
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(18)),
              child: Image.network(
                oferta.imagenUrl!,
                height: 160,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => const SizedBox.shrink(),
              ),
            ),
          Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: oferta.vigente
                            ? const Color(0xFF10B981).withValues(alpha: 0.12)
                            : AppColors.gold.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        oferta.vigente ? 'Convocatoria Activa' : 'Institucional',
                        style: TextStyle(
                          color: oferta.vigente ? const Color(0xFF047857) : AppColors.goldHover,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    const Spacer(),
                    Icon(Icons.calendar_today_outlined, size: 13, color: Colors.grey.shade500),
                    const SizedBox(width: 4),
                    Text(
                      fechaTexto,
                      style: TextStyle(fontSize: 12, color: Colors.grey.shade600, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  oferta.nombre,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF1E293B),
                    height: 1.3,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  oferta.resumenLimpio,
                  style: TextStyle(
                    fontSize: 13.5,
                    color: Colors.grey.shade700,
                    height: 1.45,
                  ),
                ),
                if (oferta.telefonoPublicador != null && oferta.telefonoPublicador!.isNotEmpty) ...[
                  const SizedBox(height: 14),
                  InkWell(
                    onTap: () {
                      final uri = Uri.parse('tel:${oferta.telefonoPublicador}');
                      launchUrl(uri);
                    },
                    borderRadius: BorderRadius.circular(10),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.phone_outlined, size: 16, color: AppColors.teal),
                          const SizedBox(width: 6),
                          Text(
                            'Contacto: ${oferta.telefonoPublicador}',
                            style: const TextStyle(
                              fontSize: 12.5,
                              fontWeight: FontWeight.w600,
                              color: AppColors.teal,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
