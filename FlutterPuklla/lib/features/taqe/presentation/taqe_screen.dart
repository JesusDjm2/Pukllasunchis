import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/error_state.dart';
import '../../../routing/app_drawer.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';
import '../application/taqe_providers.dart';
import '../data/taqe_data.dart';
import '../data/taqe_repository.dart';

class TaqeScreen extends ConsumerStatefulWidget {
  const TaqeScreen({super.key});

  @override
  ConsumerState<TaqeScreen> createState() => _TaqeScreenState();
}

class _TaqeScreenState extends ConsumerState<TaqeScreen> {
  Future<void> _responderInvitacion(int grupoId, String respuesta) async {
    try {
      final repo = ref.read(taqeRepositoryProvider);
      final msg = await repo.responderInvitacion(grupoId: grupoId, respuesta: respuesta);

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: respuesta == 'aceptado' ? const Color(0xFF10B981) : AppColors.teal,
          behavior: SnackBarBehavior.floating,
        ),
      );

      ref.invalidate(taqeDataProvider);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al responder: ${e.toString()}'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  void _mostrarDialogoAyuda(TaqeGrupo grupo) {
    String motivo = 'otro';
    final mensajeController = TextEditingController();
    bool enviando = false;

    showDialog(
      context: context,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.gold.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.help_outline, color: AppColors.goldHover, size: 22),
                  ),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Text(
                      'Pedir ayuda / Asesoría',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                    ),
                  ),
                ],
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Grupo: ${grupo.nombre}',
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.tealDark),
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Motivo de la solicitud:',
                      style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5),
                    ),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      value: motivo,
                      isExpanded: true,
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      items: const [
                        DropdownMenuItem(
                          value: 'otro',
                          child: Text('Orientación metodológica / Asesoría', style: TextStyle(fontSize: 13)),
                        ),
                        DropdownMenuItem(
                          value: 'cambiar_organizador',
                          child: Text('Cambiar organizador del grupo', style: TextStyle(fontSize: 13)),
                        ),
                        DropdownMenuItem(
                          value: 'disolver_grupo',
                          child: Text('Disolver el grupo', style: TextStyle(fontSize: 13)),
                        ),
                      ],
                      onChanged: (val) {
                        if (val != null) setDialogState(() => motivo = val);
                      },
                    ),
                    const SizedBox(height: 14),
                    const Text(
                      'Detalles de tu consulta:',
                      style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: mensajeController,
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: 'Cuéntanos brevemente qué dificultad o duda tienen...',
                        hintStyle: TextStyle(fontSize: 12.5, color: Colors.grey.shade400),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: enviando ? null : () => Navigator.pop(dialogCtx),
                  child: const Text('Cancelar'),
                ),
                FilledButton(
                  onPressed: enviando
                      ? null
                      : () async {
                          setDialogState(() => enviando = true);
                          try {
                            final repo = ref.read(taqeRepositoryProvider);
                            final msg = await repo.solicitarAyuda(
                              grupoId: grupo.id,
                              motivo: motivo,
                              mensaje: mensajeController.text.trim(),
                            );
                            if (dialogCtx.mounted) Navigator.pop(dialogCtx);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(msg),
                                  backgroundColor: const Color(0xFF10B981),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          } catch (err) {
                            setDialogState(() => enviando = false);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('Error: ${err.toString()}'),
                                  backgroundColor: AppColors.danger,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          }
                        },
                  style: FilledButton.styleFrom(backgroundColor: AppColors.teal),
                  child: enviando
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Enviar solicitud'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _mostrarDetalleProyecto(TaqeProyecto proyecto) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.fromLTRB(22, 16, 22, 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.teal.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.psychology_outlined, color: AppColors.teal, size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          proyecto.ciclo ?? 'Proyecto Integrador',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Color(0xFF64748B)),
                        ),
                        Text(
                          proyecto.nombre,
                          style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800, color: AppColors.tealDark),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              if (proyecto.producto != null) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFFBEB),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFFDE68A)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.inventory_2_outlined, color: Color(0xFFD97706), size: 20),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Producto esperado: ${proyecto.producto}',
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF92400E)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              if (proyecto.descripcion != null && proyecto.descripcion!.isNotEmpty) ...[
                const SizedBox(height: 16),
                const Text('Descripción y Orientaciones:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5)),
                const SizedBox(height: 6),
                Text(
                  proyecto.descripcion!,
                  style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.45),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final taqeAsync = ref.watch(taqeDataProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text("Taq'e · Proyectos"),
        actions: const [NotificationBellAction()],
      ),
      drawer: const AppDrawer(),
      body: taqeAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => ErrorState(
          message: "No se pudo cargar el espacio Taq'e.",
          onRetry: () => ref.refresh(taqeDataProvider),
        ),
        data: (data) {
          final finalizados = data.misGrupos.where((g) => g.entregado).length;
          final totalEvidencias = data.misGrupos.fold(0, (acc, g) => acc + g.evidencias.length);

          return RefreshIndicator(
            onRefresh: () => ref.refresh(taqeDataProvider.future),
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              children: [
                // Hero Banner
                _HeroTaqeCard(
                  gruposCount: data.misGrupos.length,
                  evidenciasCount: totalEvidencias,
                  finalizadosCount: finalizados,
                ),
                const SizedBox(height: 18),

                // Invitaciones Pendientes
                if (data.invitacionesPendientes.isNotEmpty) ...[
                  ...data.invitacionesPendientes.map(
                    (inv) => _InvitacionCard(
                      invitacion: inv,
                      onAceptar: () => _responderInvitacion(inv.id, 'aceptado'),
                      onRechazar: () => _responderInvitacion(inv.id, 'rechazado'),
                    ),
                  ),
                  const SizedBox(height: 16),
                ],

                // Proyecto Integrador del Ciclo (Igual a la web)
                if (data.proyecto != null) ...[
                  _ProyectoIntegradorCard(
                    proyecto: data.proyecto!,
                    onVerDetalle: () => _mostrarDetalleProyecto(data.proyecto!),
                  ),
                  const SizedBox(height: 20),
                ],

                // Sección Mis Grupos de Investigación
                Row(
                  children: [
                    const Icon(Icons.groups_outlined, color: AppColors.teal, size: 22),
                    const SizedBox(width: 8),
                    const Text(
                      'Mis grupos de investigación',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: AppColors.tealDark,
                      ),
                    ),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        '${data.misGrupos.length}',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF64748B)),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                if (data.misGrupos.isEmpty)
                  _EmptyGruposCard()
                else
                  ...data.misGrupos.map(
                    (grupo) => _GrupoCard(
                      grupo: grupo,
                      onPedirAyuda: () => _mostrarDialogoAyuda(grupo),
                    ),
                  ),

                const SizedBox(height: 24),

                // Investigadores de Apoyo
                if (data.investigadores.isNotEmpty) ...[
                  _InvestigadoresSection(investigadores: data.investigadores),
                  const SizedBox(height: 32),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}

/// Hero Banner con resumen institucional
class _HeroTaqeCard extends StatelessWidget {
  const _HeroTaqeCard({
    required this.gruposCount,
    required this.evidenciasCount,
    required this.finalizadosCount,
  });

  final int gruposCount;
  final int evidenciasCount;
  final int finalizadosCount;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1E293B), Color(0xFF33445A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.16),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: 0.22),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.science_outlined, color: AppColors.gold, size: 24),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      "Espacio Taq'e",
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17.5,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.2,
                      ),
                    ),
                    Text(
                      'Ferias de Investigación & Proyectos',
                      style: TextStyle(
                        color: Color(0xFF94A3B8),
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Text(
            'Forma tu equipo, registra las evidencias de campo y presenta tu trabajo integrador final.',
            style: TextStyle(color: Color(0xFFE2E8F0), fontSize: 13, height: 1.4),
          ),
          if (gruposCount > 0) ...[
            const SizedBox(height: 14),
            Wrap(
              spacing: 8,
              runSpacing: 6,
              children: [
                _StatChip(icon: Icons.groups, label: '$gruposCount ${gruposCount == 1 ? "grupo" : "grupos"}'),
                _StatChip(icon: Icons.imagesearch_roller_outlined, label: '$evidenciasCount evidencias'),
                _StatChip(
                  icon: Icons.flag_circle_outlined,
                  label: '$finalizadosCount finalizados',
                  isSuccess: finalizadosCount > 0,
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({
    required this.icon,
    required this.label,
    this.isSuccess = false,
  });

  final IconData icon;
  final String label;
  final bool isSuccess;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: isSuccess
            ? const Color(0xFF10B981).withValues(alpha: 0.22)
            : Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(
          color: isSuccess ? const Color(0xFF10B981).withValues(alpha: 0.4) : Colors.white.withValues(alpha: 0.18),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: isSuccess ? const Color(0xFF34D399) : Colors.white),
          const SizedBox(width: 5),
          Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w700,
              color: isSuccess ? const Color(0xFF34D399) : Colors.white,
            ),
          ),
        ],
      ),
    );
  }
}

/// Tarjeta de invitación pendiente
class _InvitacionCard extends StatelessWidget {
  const _InvitacionCard({
    required this.invitacion,
    required this.onAceptar,
    required this.onRechazar,
  });

  final TaqeInvitacion invitacion;
  final VoidCallback onAceptar;
  final VoidCallback onRechazar;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDF4),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFBBF7D0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF16A34A).withValues(alpha: 0.06),
            blurRadius: 10,
            offset: const Offset(0, 3),
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
                  color: const Color(0xFF16A34A).withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.mail_outline, color: Color(0xFF16A34A), size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Te invitaron al grupo "${invitacion.grupoNombre}"',
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5, color: Color(0xFF14532D)),
                    ),
                    Text(
                      '${invitacion.cursoNombre} · De: ${invitacion.creadorNombre}',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF15803D)),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              OutlinedButton(
                onPressed: onRechazar,
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF64748B),
                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                  visualDensity: VisualDensity.compact,
                ),
                child: const Text('Rechazar', style: TextStyle(fontSize: 12.5)),
              ),
              const SizedBox(width: 8),
              FilledButton.icon(
                onPressed: onAceptar,
                icon: const Icon(Icons.check, size: 16),
                label: const Text('Aceptar', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.teal,
                  visualDensity: VisualDensity.compact,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

/// Tarjeta del Proyecto Integrador del Alumno
class _ProyectoIntegradorCard extends StatelessWidget {
  const _ProyectoIntegradorCard({
    required this.proyecto,
    required this.onVerDetalle,
  });

  final TaqeProyecto proyecto;
  final VoidCallback onVerDetalle;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
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
                  color: AppColors.gold.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.account_tree_outlined, color: AppColors.goldHover, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'PROYECTO INTEGRADOR · ${proyecto.ciclo ?? "CICLO"}',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.7,
                        color: Color(0xFF64748B),
                      ),
                    ),
                    Text(
                      proyecto.nombre,
                      style: const TextStyle(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w800,
                        color: AppColors.tealDark,
                      ),
                    ),
                  ],
                ),
              ),
              TextButton(
                onPressed: onVerDetalle,
                style: TextButton.styleFrom(visualDensity: VisualDensity.compact),
                child: const Text('Ver detalle', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
              ),
            ],
          ),
          if (proyecto.producto != null) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFFFFBEB),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  const Icon(Icons.inbox, color: Color(0xFFD97706), size: 16),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Producto: ${proyecto.producto}',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF92400E)),
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

/// Estado cuando el alumno aún no tiene grupos
class _EmptyGruposCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Center(
        child: Column(
          children: [
            Icon(Icons.science_outlined, size: 42, color: Colors.grey.shade400),
            const SizedBox(height: 10),
            const Text(
              "Aún no perteneces a ningún grupo Taq'e este periodo",
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700, color: AppColors.tealDark),
            ),
            const SizedBox(height: 6),
            const Text(
              'Tus compañeros pueden agregarte a su grupo o puedes crearlo desde la intranet institucional.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12.5, color: Color(0xFF64748B), height: 1.35),
            ),
          ],
        ),
      ),
    );
  }
}

/// Tarjeta completa de Grupo con Miembros, Acciones, Evidencias y Trabajo Final
class _GrupoCard extends StatefulWidget {
  const _GrupoCard({
    required this.grupo,
    required this.onPedirAyuda,
  });

  final TaqeGrupo grupo;
  final VoidCallback onPedirAyuda;

  @override
  State<_GrupoCard> createState() => _GrupoCardState();
}

class _GrupoCardState extends State<_GrupoCard> {
  int _archivosTab = 0; // 0: Evidencias, 1: Trabajo Final

  @override
  Widget build(BuildContext context) {
    final grupo = widget.grupo;
    final esFinalizado = grupo.entregado;

    return Container(
      margin: const EdgeInsets.only(bottom: 18),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.05),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Cabecera: Nombre y Estado
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.teal.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.biotech_outlined, color: AppColors.teal, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      grupo.nombre,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.tealDark),
                    ),
                    Text(
                      grupo.cursoNombre,
                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: esFinalizado ? const Color(0xFFE6F4EC) : const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      esFinalizado ? Icons.check_circle : Icons.autorenew,
                      size: 13,
                      color: esFinalizado ? const Color(0xFF0B7A43) : const Color(0xFF1D4ED8),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      esFinalizado ? 'Finalizado' : 'En proceso',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: esFinalizado ? const Color(0xFF0B7A43) : const Color(0xFF1D4ED8),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Equipo de Integrantes
          Row(
            children: [
              const Icon(Icons.people_alt_outlined, size: 16, color: Color(0xFF64748B)),
              const SizedBox(width: 6),
              Text(
                'Equipo · ${grupo.integrantes.length} ${grupo.integrantes.length == 1 ? "integrante" : "integrantes"}',
                style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF475569)),
              ),
              const Spacer(),
              TextButton.icon(
                onPressed: widget.onPedirAyuda,
                icon: const Icon(Icons.support_agent_outlined, size: 16),
                label: const Text('Pedir ayuda', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                style: TextButton.styleFrom(
                  foregroundColor: AppColors.teal,
                  visualDensity: VisualDensity.compact,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),

          // Lista de miembros
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: grupo.integrantes.map((m) {
              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircleAvatar(
                      radius: 11,
                      backgroundColor: m.esOrganizador ? AppColors.gold : AppColors.teal,
                      child: Text(
                        m.nombre.isNotEmpty ? m.nombre[0].toUpperCase() : '?',
                        style: const TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.w800),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      m.nombre,
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF1E293B)),
                    ),
                    if (m.esOrganizador) ...[
                      const SizedBox(width: 5),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                        decoration: BoxDecoration(
                          color: AppColors.gold.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'Org.',
                          style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: AppColors.goldHover),
                        ),
                      ),
                    ],
                  ],
                ),
              );
            }).toList(),
          ),

          const SizedBox(height: 18),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 14),

          // Pestañas de Archivos: Evidencias vs Trabajo Final
          Row(
            children: [
              Expanded(
                child: _PestanaArchivo(
                  titulo: 'Evidencias (${grupo.evidencias.length})',
                  icon: Icons.photo_library_outlined,
                  isSelected: _archivosTab == 0,
                  onTap: () => setState(() => _archivosTab = 0),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _PestanaArchivo(
                  titulo: 'Trabajo Final (${grupo.finales.length})',
                  icon: Icons.assignment_turned_in_outlined,
                  isSelected: _archivosTab == 1,
                  onTap: () => setState(() => _archivosTab = 1),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Lista de archivos según pestaña
          if (_archivosTab == 0)
            _ListaArchivos(archivos: grupo.evidencias, tipoVacio: 'evidencias')
          else
            _ListaArchivos(archivos: grupo.finales, tipoVacio: 'entregables finales'),
        ],
      ),
    );
  }
}

class _PestanaArchivo extends StatelessWidget {
  const _PestanaArchivo({
    required this.titulo,
    required this.icon,
    required this.isSelected,
    required this.onTap,
  });

  final String titulo;
  final IconData icon;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.teal.withValues(alpha: 0.1) : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isSelected ? AppColors.teal : const Color(0xFFE2E8F0),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 15, color: isSelected ? AppColors.teal : const Color(0xFF64748B)),
            const SizedBox(width: 6),
            Flexible(
              child: Text(
                titulo,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
                  color: isSelected ? AppColors.teal : const Color(0xFF64748B),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ListaArchivos extends StatelessWidget {
  const _ListaArchivos({
    required this.archivos,
    required this.tipoVacio,
  });

  final List<TaqeArchivo> archivos;
  final String tipoVacio;

  IconData _iconForType(String tipo) {
    switch (tipo.toLowerCase()) {
      case 'imagen':
      case 'image':
        return Icons.image_outlined;
      case 'pdf':
        return Icons.picture_as_pdf_outlined;
      case 'word':
      case 'doc':
      case 'docx':
        return Icons.description_outlined;
      case 'audio':
        return Icons.audiotrack_outlined;
      case 'video':
        return Icons.videocam_outlined;
      case 'enlace':
      case 'link':
        return Icons.link_outlined;
      default:
        return Icons.insert_drive_file_outlined;
    }
  }

  Color _colorForType(String tipo) {
    switch (tipo.toLowerCase()) {
      case 'imagen':
      case 'image':
        return const Color(0xFF10B981);
      case 'pdf':
        return const Color(0xFFEF4444);
      case 'word':
      case 'doc':
      case 'docx':
        return const Color(0xFF2563EB);
      case 'audio':
        return const Color(0xFF8B5CF6);
      case 'video':
        return const Color(0xFFF59E0B);
      case 'enlace':
      case 'link':
        return const Color(0xFF0EA5E9);
      default:
        return const Color(0xFF64748B);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (archivos.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        alignment: Alignment.center,
        child: Text(
          'No se han registrado $tipoVacio aún.',
          style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8), fontStyle: FontStyle.italic),
        ),
      );
    }

    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: archivos.length,
      separatorBuilder: (_, __) => const Divider(height: 12, color: Color(0xFFF1F5F9)),
      itemBuilder: (context, index) {
        final a = archivos[index];
        final color = _colorForType(a.tipoIcono);

        return Row(
          children: [
            Container(
              padding: const EdgeInsets.all(7),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(_iconForType(a.tipoIcono), size: 16, color: color),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    a.nombre,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Color(0xFF1E293B)),
                  ),
                  if (a.subidoPor != null)
                    Text(
                      'Por: ${a.subidoPor}${a.fecha != null ? " · ${a.fecha}" : ""}',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                    ),
                ],
              ),
            ),
            if (a.url != null)
              IconButton(
                icon: const Icon(Icons.open_in_new, size: 17, color: AppColors.teal),
                tooltip: 'Abrir archivo o enlace',
                onPressed: () async {
                  final uri = Uri.tryParse(a.url!);
                  if (uri != null && await canLaunchUrl(uri)) {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  }
                },
              ),
          ],
        );
      },
    );
  }
}

/// Sección de Investigadores de Apoyo
class _InvestigadoresSection extends StatelessWidget {
  const _InvestigadoresSection({required this.investigadores});

  final List<TaqeInvestigador> investigadores;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.school_outlined, size: 18, color: AppColors.teal),
              SizedBox(width: 8),
              Text(
                'Docentes Investigadores de apoyo',
                style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, color: AppColors.tealDark),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ...investigadores.map(
            (inv) => Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                children: [
                  const Icon(Icons.person_outline, size: 16, color: Color(0xFF64748B)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      inv.nombre,
                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Color(0xFF334155)),
                    ),
                  ),
                  if (inv.email != null)
                    Text(
                      inv.email!,
                      style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
