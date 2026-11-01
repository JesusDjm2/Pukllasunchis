import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_avatar.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../../auth/application/auth_controller.dart';
import '../../comunicados/application/comunicados_providers.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';
import '../../perfil/application/perfil_providers.dart';
import '../../perfil/data/alumno_perfil.dart';
import '../application/home_providers.dart';
import '../data/curso.dart';
import '../data/periodo_actual.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  static bool _popupMatriculaMostrado = false;

  void _verificarAlertaMatricula(PeriodoActual? periodo, bool? yaMatriculado) {
    if (_popupMatriculaMostrado) return;
    if (periodo == null || !periodo.formularioHabilitado) return;
    if (yaMatriculado == null || yaMatriculado == true) return;

    _popupMatriculaMostrado = true;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      showDialog(
        context: context,
        barrierDismissible: true,
        builder: (dialogCtx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.warning_amber_rounded, color: Color(0xFFD97706), size: 24),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Text(
                  '¡Aún no te has matriculado!',
                  style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800, color: Color(0xFF92400E)),
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'El período académico ${periodo.nombre} ya se encuentra activo con el formulario de matrícula habilitado.',
                style: const TextStyle(fontSize: 13.5, color: Color(0xFF334155), height: 1.4),
              ),
              const SizedBox(height: 10),
              const Text(
                'No estás matriculado en el período actual activo. Completa tu ficha de matrícula para confirmar tus asignaturas, registrar tus datos y acceder a tus notas.',
                style: TextStyle(fontSize: 13, color: Color(0xFF64748B), height: 1.35),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogCtx),
              child: const Text('Recordarme después', style: TextStyle(color: Color(0xFF64748B))),
            ),
            FilledButton.icon(
              onPressed: () {
                Navigator.pop(dialogCtx);
                context.push('/matricula/formulario');
              },
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.goldHover,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              icon: const Icon(Icons.arrow_forward_rounded, size: 16),
              label: const Text('Completar matrícula ahora', style: TextStyle(fontWeight: FontWeight.w700)),
            ),
          ],
        ),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authControllerProvider).user;
    final perfilAsync = ref.watch(perfilProvider);
    final periodoAsync = ref.watch(periodoActualProvider);
    final cursosAsync = ref.watch(cursosProvider);
    final comunicadosAsync = ref.watch(comunicadosProvider);
    final yaMatriculadoAsync = ref.watch(yaMatriculadoProvider);

    // Si el usuario es alumno y el formulario está abierto sin matrícula, mostrar popup de alerta
    _verificarAlertaMatricula(periodoAsync.valueOrNull, yaMatriculadoAsync.valueOrNull);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Portal Estudiantil',
          style: TextStyle(fontWeight: FontWeight.w800, fontSize: 19),
        ),
        actions: const [PukllaAppBarActions()],
        elevation: 0,
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(perfilProvider);
          ref.invalidate(periodoActualProvider);
          ref.invalidate(cursosProvider);
          ref.invalidate(comunicadosProvider);
          ref.invalidate(yaMatriculadoProvider);
          await ref.read(perfilProvider.future);
        },
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            // ── HERO BANNER SENIOR CON IDENTIDAD VISUAL ──
            perfilAsync.when(
              data: (perfil) => _HeroHeader(
                perfil: perfil,
              ),
              loading: () => Container(
                height: 180,
                color: AppColors.teal,
                child: const Center(child: CircularProgressIndicator(color: Colors.white)),
              ),
              error: (_, __) => _HeroHeaderFallback(
                userNombre: user?.name ?? 'Estudiante',
                fotoUrl: user?.foto,
              ),
            ),

            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── TARJETA COMPACTA UNIFICADA: PERÍODO ACTUAL & ESTADO DE MATRÍCULA ──
                  if (periodoAsync.valueOrNull != null) ...[
                    _PeriodoMatriculaCard(
                      periodo: periodoAsync.valueOrNull!,
                      yaMatriculado: yaMatriculadoAsync.valueOrNull,
                    ),
                    const SizedBox(height: 18),
                  ],
                  const SizedBox(height: 22),

                  // ── TÍTULO SECCIÓN ACCESOS DIRECTOS ──
                  const Row(
                    children: [
                      Text(
                        'Servicios y Módulos',
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                          letterSpacing: -0.2,
                        ),
                      ),
                      Spacer(),
                      Text(
                        'Todo en un solo lugar',
                        style: TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // ── BENTO GRID SENIOR ──
                  _BentoGridModulos(),
                  const SizedBox(height: 26),

                  // ── MIS CURSOS ACTIVOS ──
                  Row(
                    children: [
                      const Text(
                        'Cursos del Semestre',
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                          letterSpacing: -0.2,
                        ),
                      ),
                      const Spacer(),
                      TextButton(
                        onPressed: () => context.push('/calificaciones'),
                        style: TextButton.styleFrom(
                          padding: EdgeInsets.zero,
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        child: const Row(
                          children: [
                            Text('Ver notas', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: AppColors.goldHover)),
                            SizedBox(width: 2),
                            Icon(Icons.chevron_right, size: 16, color: AppColors.goldHover),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  cursosAsync.when(
                    data: (cursos) {
                      if (cursos.isEmpty) {
                        return Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: const Center(
                            child: Text(
                              'No tienes cursos matriculados en este periodo.',
                              style: TextStyle(color: Color(0xFF64748B), fontSize: 13.5),
                            ),
                          ),
                        );
                      }
                      return Column(
                        children: cursos.map((c) => _CursoItemCard(curso: c)).toList(),
                      );
                    },
                    loading: () => const Center(
                      child: Padding(
                        padding: EdgeInsets.all(20),
                        child: CircularProgressIndicator(),
                      ),
                    ),
                    error: (_, __) => const SizedBox.shrink(),
                  ),

                  const SizedBox(height: 24),

                  // ── COMUNICADOS RECIENTES ──
                  Row(
                    children: [
                      const Text(
                        'Avisos y Comunicados',
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF0F172A),
                          letterSpacing: -0.2,
                        ),
                      ),
                      const Spacer(),
                      TextButton(
                        onPressed: () => context.push('/comunicados'),
                        style: TextButton.styleFrom(
                          padding: EdgeInsets.zero,
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        child: const Row(
                          children: [
                            Text('Ver todos', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: AppColors.teal)),
                            SizedBox(width: 2),
                            Icon(Icons.chevron_right, size: 16, color: AppColors.teal),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  comunicadosAsync.when(
                    data: (page) {
                      final items = page.items.take(3).toList();
                      if (items.isEmpty) {
                        return Container(
                          padding: const EdgeInsets.all(18),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: const Center(
                            child: Text('No hay comunicados recientes.', style: TextStyle(color: Color(0xFF64748B))),
                          ),
                        );
                      }
                      return Column(
                        children: items.map((item) {
                          return Container(
                            margin: const EdgeInsets.only(bottom: 10),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(14),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(0xFF0F172A).withValues(alpha: 0.03),
                                  blurRadius: 10,
                                  offset: const Offset(0, 3),
                                ),
                              ],
                              border: Border.all(color: const Color(0xFFF1F5F9)),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: AppColors.gold.withValues(alpha: 0.12),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: const Icon(Icons.campaign_outlined, color: AppColors.goldHover, size: 20),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        item.titulo,
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w700,
                                          fontSize: 13.5,
                                          color: Color(0xFF1E293B),
                                        ),
                                      ),
                                      const SizedBox(height: 3),
                                      Text(
                                        item.descripcion ?? 'Aviso institucional para la comunidad educativa.',
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      );
                    },
                    loading: () => const SizedBox.shrink(),
                    error: (_, __) => const SizedBox.shrink(),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── CABECERA HERO SENIOR ──
class _HeroHeader extends StatelessWidget {
  const _HeroHeader({
    required this.perfil,
  });

  final AlumnoPerfil perfil;

  @override
  Widget build(BuildContext context) {
    final iniciales = [perfil.nombres, perfil.apellidos]
        .where((s) => s.isNotEmpty)
        .map((s) => s[0].toUpperCase())
        .take(2)
        .join();

    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
        boxShadow: [
          BoxShadow(
            color: Color(0x221B2A4A),
            blurRadius: 20,
            offset: Offset(0, 10),
          ),
        ],
      ),
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  // Avatar: si tiene foto se muestra, si no las iniciales
                  PukllaAvatar(
                    fotoUrl: perfil.foto,
                    iniciales: iniciales,
                    size: 58,
                    borderWidth: 2.2,
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                              decoration: BoxDecoration(
                                color: AppColors.gold.withValues(alpha: 0.25),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: const Text(
                                'ESTUDIANTE REGULAR',
                                style: TextStyle(
                                  color: Color(0xFFFDE68A),
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${perfil.nombres} ${perfil.apellidos}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 17.5,
                            fontWeight: FontWeight.w800,
                            letterSpacing: -0.2,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          perfil.programaNombre ?? 'EESP Pukllasunchis',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.8),
                            fontSize: 12.5,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              // Chips de estado institucional del estudiante
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _PillBadge(
                    icon: Icons.layers_outlined,
                    label: perfil.cicloNombre ?? 'Semestre Vigente',
                    bg: Colors.white.withValues(alpha: 0.12),
                    color: Colors.white,
                  ),
                  if (perfil.ayudaEconomica == true)
                    _PillBadge(
                      icon: Icons.star_border,
                      label: 'Apoyo Económico',
                      bg: AppColors.gold.withValues(alpha: 0.3),
                      color: const Color(0xFFFEF08A),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _HeroHeaderFallback extends StatelessWidget {
  const _HeroHeaderFallback({required this.userNombre, this.fotoUrl});
  final String userNombre;
  final String? fotoUrl;

  @override
  Widget build(BuildContext context) {
    final inicial = userNombre.isNotEmpty ? userNombre[0].toUpperCase() : 'P';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: Row(
        children: [
          PukllaAvatar(
            fotoUrl: fotoUrl,
            iniciales: inicial,
            size: 50,
            borderWidth: 2,
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '¡Hola, $userNombre!',
                  style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Bienvenido al portal móvil de la EESP Pukllasunchis',
                  style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PillBadge extends StatelessWidget {
  const _PillBadge({required this.icon, required this.label, required this.bg, required this.color});

  final IconData icon;
  final String label;
  final Color bg;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: color),
          const SizedBox(width: 5),
          Text(label, style: TextStyle(color: color, fontSize: 11.5, fontWeight: FontWeight.w700)),
        ],
      ),
    );
  }
}

/// Tarjeta compacta institucional unificada de período actual y estado de matrícula
class _PeriodoMatriculaCard extends StatelessWidget {
  const _PeriodoMatriculaCard({
    required this.periodo,
    required this.yaMatriculado,
  });

  final PeriodoActual periodo;
  final bool? yaMatriculado;

  @override
  Widget build(BuildContext context) {
    final tieneHorario = periodo.horario != null && periodo.horario!.isNotEmpty;
    final esPendiente = yaMatriculado == false && periodo.formularioHabilitado;
    final esMatriculado = yaMatriculado == true;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
        border: Border.all(
          color: esPendiente
              ? const Color(0xFFFDE68A)
              : (esMatriculado ? const Color(0xFFA7F3D0) : const Color(0xFFE2E8F0)),
          width: esPendiente || esMatriculado ? 1.2 : 1,
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: esMatriculado
                  ? const Color(0xFF10B981).withValues(alpha: 0.12)
                  : (esPendiente
                      ? const Color(0xFFF59E0B).withValues(alpha: 0.15)
                      : const Color(0xFF3B82F6).withValues(alpha: 0.10)),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(
              esMatriculado
                  ? Icons.verified_rounded
                  : (esPendiente ? Icons.assignment_late_outlined : Icons.calendar_month_outlined),
              color: esMatriculado
                  ? const Color(0xFF059669)
                  : (esPendiente ? const Color(0xFFD97706) : const Color(0xFF2563EB)),
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Text(
                      'Período ${periodo.nombre}',
                      style: const TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 14.5,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(width: 8),
                    if (esMatriculado)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFECFDF5),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFA7F3D0)),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.check_circle_rounded, size: 11, color: Color(0xFF059669)),
                            SizedBox(width: 4),
                            Text(
                              'Matriculado',
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF059669),
                              ),
                            ),
                          ],
                        ),
                      )
                    else if (esPendiente)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFFBEB),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFFDE68A)),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.warning_amber_rounded, size: 11, color: Color(0xFFD97706)),
                            SizedBox(width: 4),
                            Text(
                              'Pendiente',
                              style: TextStyle(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFFD97706),
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  esMatriculado
                      ? 'Matrícula confirmada · Clases y evaluaciones activas'
                      : (esPendiente
                          ? 'Formulario habilitado · Toca para formalizar'
                          : 'Clases vigentes y evaluaciones en curso'),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 12,
                    color: esPendiente ? const Color(0xFFB45309) : const Color(0xFF64748B),
                    fontWeight: esPendiente ? FontWeight.w600 : FontWeight.normal,
                  ),
                ),
              ],
            ),
          ),
          if (esPendiente) ...[
            const SizedBox(width: 8),
            FilledButton.tonal(
              onPressed: () => context.push('/matricula/formulario'),
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.goldHover,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('Llenar', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
                  SizedBox(width: 2),
                  Icon(Icons.arrow_forward_rounded, size: 13),
                ],
              ),
            ),
          ] else if (tieneHorario) ...[
            const SizedBox(width: 6),
            IconButton(
              onPressed: () {
                final uri = Uri.parse(periodo.horario!);
                launchUrl(uri, mode: LaunchMode.externalApplication);
              },
              icon: const Icon(Icons.picture_as_pdf_outlined, size: 20),
              tooltip: 'Ver horario del ciclo',
              style: IconButton.styleFrom(
                backgroundColor: AppColors.gold.withValues(alpha: 0.12),
                foregroundColor: AppColors.goldHover,
                padding: const EdgeInsets.all(8),
                minimumSize: const Size(36, 36),
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ── BENTO GRID COMPLETO (8 MÓDULOS DE LA WEB) ──
class _BentoGridModulos extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _BentoItem(
                icon: Icons.badge_outlined,
                color: const Color(0xFF2563EB),
                title: 'Ficha Técnica',
                subtitle: 'Datos personales y PDF',
                onTap: () => context.go('/ficha-tecnica'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _BentoItem(
                icon: Icons.grade_outlined,
                color: const Color(0xFF059669),
                title: 'Calificaciones',
                subtitle: 'Notas y récord académico',
                onTap: () => context.go('/calificaciones'),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _BentoItem(
                icon: Icons.science_outlined,
                color: const Color(0xFF7C3AED),
                title: "Taq'e Proyectos",
                subtitle: 'Investigación formativa',
                onTap: () => context.go('/taqe'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _BentoItem(
                icon: Icons.work_outline,
                color: const Color(0xFFD97706),
                title: 'Bolsa de Trabajo',
                subtitle: 'Convocatorias y prácticas',
                onTap: () => context.go('/bolsa-trabajo'),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _BentoItem(
                icon: Icons.school_outlined,
                color: const Color(0xFF0891B2),
                title: 'Cursos Especiales',
                subtitle: 'Talleres asincrónicos',
                onTap: () => context.go('/cursos-especiales'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _BentoItem(
                icon: Icons.favorite_outline,
                color: const Color(0xFFE11D48),
                title: 'Tutoría y Apoyo',
                subtitle: 'Atención y sugerencias',
                onTap: () => context.go('/tutoria'),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _BentoItem(
                icon: Icons.campaign_outlined,
                color: const Color(0xFF4F46E5),
                title: 'Comunicados',
                subtitle: 'Avisos institucionales',
                onTap: () => context.go('/comunicados'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _BentoItem(
                icon: Icons.local_library_outlined,
                color: const Color(0xFFCA8A04),
                title: 'Biblioteca & Tesis',
                subtitle: 'Guías y libros virtuales',
                onTap: () => context.go('/recursos'),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _BentoItem extends StatelessWidget {
  const _BentoItem({
    required this.icon,
    required this.color,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  final IconData icon;
  final Color color;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(18),
      child: Container(
        padding: const EdgeInsets.all(15),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF0F172A).withValues(alpha: 0.04),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
          border: Border.all(color: const Color(0xFFF1F5F9), width: 1.2),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(9),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: color, size: 22),
                ),
                const Spacer(),
                Icon(Icons.arrow_forward_ios, size: 12, color: Colors.grey.shade400),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: 14,
                color: Color(0xFF1E293B),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              subtitle,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 11.5,
                color: Color(0xFF64748B),
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── TARJETA DE CURSO SENIOR ──
class _CursoItemCard extends StatelessWidget {
  const _CursoItemCard({required this.curso});
  final Curso curso;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F172A).withValues(alpha: 0.03),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
        border: Border.all(color: const Color(0xFFF1F5F9)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.teal.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.book_outlined, color: AppColors.teal, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  curso.nombre,
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 14,
                    color: Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  curso.horas.isNotEmpty
                      ? '${curso.horas} horas lectivas · Plan de estudios'
                      : 'Horario semestral regular',
                  style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                ),
              ],
            ),
          ),
          if (curso.creditos != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                '${curso.creditos} CR',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF475569),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
