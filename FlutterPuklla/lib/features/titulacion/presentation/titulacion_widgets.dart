import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../data/titulacion_data.dart';

/// Abre en el navegador una página de la web (formularios de titulación,
/// descargas, WhatsApp). Los formularios llevan archivos y se envían allá.
Future<void> abrirEnlaceTitulacion(BuildContext context, String url) async {
  final messenger = ScaffoldMessenger.of(context);
  final uri = Uri.tryParse(url);
  final ok = uri != null && await launchUrl(uri, mode: LaunchMode.externalApplication);
  if (!ok) {
    messenger.showSnackBar(const SnackBar(
      content: Text('No se pudo abrir el enlace.'),
      behavior: SnackBarBehavior.floating,
    ));
  }
}

/// Abre una solicitud de titulación: el formulario nativo si la app lo tiene
/// (PTI, carta, originalidad); si no, la página en el navegador.
Future<void> abrirSolicitudTitulacion(BuildContext context, {String? formularioApp, required String url}) async {
  if (formularioApp != null) {
    await context.push('/titulacion/solicitud/$formularioApp');
  } else {
    await abrirEnlaceTitulacion(context, url);
  }
}

/// Color del estado con el mismo criterio que el badge de la web
/// (AvanceTitulacion::badge).
Color colorEstadoTitulacion(String bootstrap) {
  return switch (bootstrap) {
    'success' => const Color(0xFF16A34A),
    'info' => const Color(0xFF0891B2),
    'primary' => const Color(0xFF2563EB),
    'warning' => const Color(0xFFD97706),
    _ => const Color(0xFF64748B),
  };
}

/// Tarjeta "Mi proceso de titulación" del dashboard: estado, avance y lo que
/// toca hacer ahora. Lleva a la pantalla completa.
class TitulacionResumenCard extends StatelessWidget {
  const TitulacionResumenCard({super.key, required this.data});

  final TitulacionData data;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final estado = data.estado;
    final color = estado != null ? colorEstadoTitulacion(estado.color) : AppColors.gold;
    final accion = data.acciones.where((a) => !a.lista).firstOrNull;
    final vigencia = data.vigenciaPti;

    final String titulo;
    final String detalle;
    if (data.tieneProceso) {
      titulo = data.siguientePaso != null ? 'Siguiente paso: ${data.siguientePaso!.label}' : '¡Proceso completo!';
      detalle = data.siguientePaso?.falta ?? 'Ya tienes tu Título de Licenciado(a).';
    } else {
      titulo = 'Ya puedes iniciar tu titulación';
      detalle = data.solicitudPti?.mensaje ?? 'Tu primer paso es inscribir tu Plan de Trabajo de Investigación (PTI).';
    }

    return PukllaScaleTap(
      onTap: () => context.push('/titulacion'),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: PukllaDashboardStyles.cardDecoration(
          context,
          radius: 18,
          customBorder: color.withValues(alpha: isDark ? 0.45 : 0.30),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(9),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: isDark ? 0.22 : 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(Icons.workspace_premium_rounded, color: color, size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Mi proceso de titulación',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 14.5,
                          color: PukllaDashboardStyles.textPrimary(context),
                        ),
                      ),
                      if (data.promocion != null)
                        Text(
                          'Promoción ${data.promocion}',
                          style: TextStyle(fontSize: 11.5, color: PukllaDashboardStyles.textSecondary(context)),
                        ),
                    ],
                  ),
                ),
                if (estado != null) EstadoTitulacionChip(estado: estado),
              ],
            ),
            if (data.tieneProceso) ...[
              const SizedBox(height: 14),
              ClipRRect(
                borderRadius: BorderRadius.circular(6),
                child: LinearProgressIndicator(
                  value: data.progreso,
                  minHeight: 7,
                  backgroundColor: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                  valueColor: AlwaysStoppedAnimation(color),
                ),
              ),
              const SizedBox(height: 6),
              Row(
                children: data.etapas
                    .map((e) => Expanded(
                          child: Text(
                            switch (e.clave) { 'pti' => 'PTI', 'ti' => 'TI · Bachiller', _ => 'Tesis · Título' },
                            textAlign: e.clave == 'pti'
                                ? TextAlign.left
                                : (e.clave == 'tesis' ? TextAlign.right : TextAlign.center),
                            style: TextStyle(
                              fontSize: 10.5,
                              fontWeight: e.estado == 'en_proceso' ? FontWeight.w800 : FontWeight.w500,
                              color: e.estado == 'pendiente'
                                  ? PukllaDashboardStyles.textSecondary(context)
                                  : PukllaDashboardStyles.textPrimary(context),
                            ),
                          ),
                        ))
                    .toList(),
              ),
            ],
            const SizedBox(height: 12),
            Text(
              titulo,
              style: TextStyle(
                fontWeight: FontWeight.w700,
                fontSize: 13,
                color: PukllaDashboardStyles.textPrimary(context),
              ),
            ),
            const SizedBox(height: 3),
            Text(
              detalle,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(fontSize: 12, height: 1.35, color: PukllaDashboardStyles.textSecondary(context)),
            ),
            if (vigencia != null && (vigencia.vencido || vigencia.urgente)) ...[
              const SizedBox(height: 10),
              VigenciaPtiAviso(vigencia: vigencia, compacto: true),
            ],
            if (accion != null && accion.observada) ...[
              const SizedBox(height: 10),
              Row(
                children: [
                  const Icon(Icons.error_outline_rounded, size: 16, color: Color(0xFFD97706)),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      accion.mensaje,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFFB45309)),
                    ),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 10),
            Align(
              alignment: Alignment.centerRight,
              child: Text(
                'Ver mi proceso →',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: color),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class EstadoTitulacionChip extends StatelessWidget {
  const EstadoTitulacionChip({super.key, required this.estado});

  final TitulacionEstado estado;

  @override
  Widget build(BuildContext context) {
    final color = colorEstadoTitulacion(estado.color);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.14),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withValues(alpha: 0.35)),
      ),
      child: Text(
        estado.nombre,
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: color),
      ),
    );
  }
}

/// Vigencia del PTI (2 años): urgente desde 4 meses antes, como en la web.
class VigenciaPtiAviso extends StatelessWidget {
  const VigenciaPtiAviso({super.key, required this.vigencia, this.compacto = false});

  final TitulacionVigencia vigencia;
  final bool compacto;

  @override
  Widget build(BuildContext context) {
    final fecha = vigencia.venceEl;
    final fechaTxt = fecha != null ? '${fecha.day.toString().padLeft(2, '0')}/${fecha.month.toString().padLeft(2, '0')}/${fecha.year}' : '—';
    final (Color color, IconData icono, String texto) = vigencia.vencido
        ? (const Color(0xFFDC2626), Icons.event_busy_rounded, 'Tu PTI venció el $fechaTxt${vigencia.conExtension ? ' (con extensión)' : ''}. Comunícate con el Área de Investigación.')
        : vigencia.urgente
            ? (const Color(0xFFD97706), Icons.timer_outlined, 'Tu PTI vence el $fechaTxt: te quedan ${vigencia.mesesRestantes} ${vigencia.mesesRestantes == 1 ? 'mes' : 'meses'}.')
            : (const Color(0xFF16A34A), Icons.event_available_rounded, 'Tu PTI está vigente hasta el $fechaTxt${vigencia.conExtension ? ' (con extensión)' : ''}.');

    return Container(
      padding: EdgeInsets.all(compacto ? 10 : 12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.30)),
      ),
      child: Row(
        children: [
          Icon(icono, size: compacto ? 16 : 20, color: color),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              texto,
              style: TextStyle(fontSize: compacto ? 11.5 : 12.5, fontWeight: FontWeight.w600, color: color, height: 1.3),
            ),
          ),
        ],
      ),
    );
  }
}
