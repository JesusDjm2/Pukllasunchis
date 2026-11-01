import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
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
import 'curso_detalle_sheet.dart';
import '../../perfil/presentation/carnet_digital_sheet.dart';

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
        builder: (dialogCtx) {
          final isDarkDialog = PukllaDashboardStyles.isDark(dialogCtx);
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            backgroundColor: PukllaDashboardStyles.surfaceColor(dialogCtx),
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: isDarkDialog
                        ? const Color(0xFF78350F).withValues(alpha: 0.35)
                        : const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.warning_amber_rounded, color: Color(0xFFD97706), size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    '¡Aún no te has matriculado!',
                    style: TextStyle(
                      fontSize: 16.5,
                      fontWeight: FontWeight.w800,
                      color: isDarkDialog ? const Color(0xFFFBBF24) : const Color(0xFF92400E),
                    ),
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
                  style: TextStyle(
                    fontSize: 13.5,
                    color: isDarkDialog ? AppColors.darkText : const Color(0xFF334155),
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  'No estás matriculado en el período actual activo. Completa tu ficha de matrícula para confirmar tus asignaturas, registrar tus datos y acceder a tus notas.',
                  style: TextStyle(
                    fontSize: 13,
                    color: isDarkDialog ? AppColors.darkMuted : const Color(0xFF64748B),
                    height: 1.35,
                  ),
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(dialogCtx),
                child: Text(
                  'Recordarme después',
                  style: TextStyle(color: isDarkDialog ? AppColors.darkMuted : const Color(0xFF64748B)),
                ),
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
          );
        },
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
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
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
                    PukllaFadeInSlide(
                      delayMs: 40,
                      child: _PeriodoMatriculaCard(
                        periodo: periodoAsync.valueOrNull!,
                        yaMatriculado: yaMatriculadoAsync.valueOrNull,
                      ),
                    ),
                    const SizedBox(height: 18),
                  ],
                  const SizedBox(height: 22),

                  // ── TÍTULO SECCIÓN ACCESOS DIRECTOS & BENTO ──
                  PukllaFadeInSlide(
                    delayMs: 80,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const PukllaSectionHeader(
                          title: 'Servicios y Módulos',
                          subtitle: 'Todo en un solo lugar',
                        ),
                        const SizedBox(height: 12),
                        _BentoGridModulos(),
                      ],
                    ),
                  ),
                  const SizedBox(height: 26),

                  // ── MIS CURSOS ACTIVOS ──
                  PukllaFadeInSlide(
                    delayMs: 140,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        PukllaSectionHeader(
                          title: 'Cursos del Semestre',
                          actionText: 'Ver notas',
                          onAction: () => context.push('/calificaciones'),
                        ),
                        const SizedBox(height: 12),
                        cursosAsync.when(
                          data: (cursos) {
                            if (cursos.isEmpty) {
                              return Container(
                                padding: const EdgeInsets.all(20),
                                decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
                                child: Center(
                                  child: Text(
                                    'No tienes cursos matriculados en este periodo.',
                                    style: TextStyle(
                                      color: PukllaDashboardStyles.textSecondary(context),
                                      fontSize: 13.5,
                                    ),
                                  ),
                                ),
                              );
                            }
                            return Column(
                              children: cursos.map((c) => _CursoItemCard(curso: c)).toList(),
                            );
                          },
                          loading: () => const Column(
                            children: [
                              PukllaSkeletonCard(height: 72),
                              PukllaSkeletonCard(height: 72),
                            ],
                          ),
                          error: (_, __) => const SizedBox.shrink(),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // ── COMUNICADOS RECIENTES ──
                  PukllaFadeInSlide(
                    delayMs: 200,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        PukllaSectionHeader(
                          title: 'Avisos y Comunicados',
                          actionText: 'Ver todos',
                          onAction: () => context.push('/comunicados'),
                        ),
                        const SizedBox(height: 12),
                        comunicadosAsync.when(
                          data: (page) {
                            final items = page.items.take(3).toList();
                            if (items.isEmpty) {
                              return Container(
                                padding: const EdgeInsets.all(18),
                                decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
                                child: Center(
                                  child: Text(
                                    'No hay comunicados recientes.',
                                    style: TextStyle(
                                      color: PukllaDashboardStyles.textSecondary(context),
                                    ),
                                  ),
                                ),
                              );
                            }
                            return Column(
                              children: items.map((item) {
                                return PukllaScaleTap(
                                  onTap: () => context.push('/comunicados'),
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 10),
                                    padding: const EdgeInsets.all(14),
                                    decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
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
                                                style: TextStyle(
                                                  fontWeight: FontWeight.w700,
                                                  fontSize: 13.5,
                                                  color: PukllaDashboardStyles.textPrimary(context),
                                                ),
                                              ),
                                              const SizedBox(height: 3),
                                              Text(
                                                item.descripcion ?? 'Aviso institucional para la comunidad educativa.',
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: TextStyle(
                                                  fontSize: 12,
                                                  color: PukllaDashboardStyles.textSecondary(context),
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              }).toList(),
                            );
                          },
                          loading: () => const Column(
                            children: [
                              PukllaSkeletonCard(height: 64),
                              PukllaSkeletonCard(height: 64),
                            ],
                          ),
                          error: (_, __) => const SizedBox.shrink(),
                        ),
                      ],
                    ),
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

    final cicloRaw = perfil.cicloNombre;
    final cicloFormatted = (cicloRaw != null && cicloRaw.isNotEmpty)
        ? (cicloRaw.toLowerCase().startsWith('ciclo') ? cicloRaw : 'Ciclo $cicloRaw')
        : null;

    final programaCiclo = [
      if (perfil.programaNombre != null && perfil.programaNombre!.isNotEmpty)
        perfil.programaNombre!
      else
        'EESP Pukllasunchis',
      if (cicloFormatted != null) cicloFormatted,
    ].join(' · ');

    return Container(
      width: double.infinity,
      decoration: PukllaDashboardStyles.heroHeaderDecoration(context),
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
                        const SizedBox(height: 3),
                        Row(
                          children: [
                            const Icon(
                              Icons.school_outlined,
                              size: 14,
                              color: Color(0xFFFDE68A),
                            ),
                            const SizedBox(width: 5),
                            Expanded(
                              child: Text(
                                programaCiclo,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.88),
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              if (perfil.ayudaEconomica == true) ...[
                const SizedBox(height: 12),
                _PillBadge(
                  icon: Icons.star_border,
                  label: 'Apoyo Económico',
                  bg: AppColors.gold.withValues(alpha: 0.3),
                  color: const Color(0xFFFEF08A),
                ),
              ],
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
      decoration: PukllaDashboardStyles.heroHeaderDecoration(context),
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
    final isDark = PukllaDashboardStyles.isDark(context);
    final tieneHorario = periodo.horario != null && periodo.horario!.isNotEmpty;
    final esPendiente = yaMatriculado == false && periodo.formularioHabilitado;
    final esMatriculado = yaMatriculado == true;

    void onCardTap() {
      if (esPendiente) {
        context.push('/matricula/formulario');
      } else {
        context.push('/calificaciones');
      }
    }

    final borderColor = esPendiente
        ? (isDark ? const Color(0xFFD97706) : const Color(0xFFFDE68A))
        : (esMatriculado
            ? (isDark ? const Color(0xFF059669) : const Color(0xFFA7F3D0))
            : PukllaDashboardStyles.borderColor(context));

    return PukllaScaleTap(
      onTap: onCardTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: PukllaDashboardStyles.cardDecoration(
          context,
          radius: 16,
          customBorder: borderColor,
        ),
        child: Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: esMatriculado
                      ? const Color(0xFF10B981).withValues(alpha: isDark ? 0.20 : 0.12)
                      : (esPendiente
                          ? const Color(0xFFF59E0B).withValues(alpha: isDark ? 0.22 : 0.15)
                          : const Color(0xFF3B82F6).withValues(alpha: isDark ? 0.20 : 0.10)),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  esMatriculado
                      ? Icons.verified_rounded
                      : (esPendiente ? Icons.assignment_late_outlined : Icons.calendar_month_outlined),
                  color: esMatriculado
                      ? (isDark ? const Color(0xFF34D399) : const Color(0xFF059669))
                      : (esPendiente
                          ? (isDark ? const Color(0xFFFBBF24) : const Color(0xFFD97706))
                          : (isDark ? const Color(0xFF60A5FA) : const Color(0xFF2563EB))),
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
                          style: TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 14.5,
                            color: PukllaDashboardStyles.textPrimary(context),
                          ),
                        ),
                        const SizedBox(width: 8),
                        if (esMatriculado)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            decoration: BoxDecoration(
                              color: isDark ? const Color(0xFF064E3B) : const Color(0xFFECFDF5),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: isDark ? const Color(0xFF059669) : const Color(0xFFA7F3D0),
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.check_circle_rounded,
                                  size: 11,
                                  color: isDark ? const Color(0xFF34D399) : const Color(0xFF059669),
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  'Matriculado',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w700,
                                    color: isDark ? const Color(0xFF34D399) : const Color(0xFF059669),
                                  ),
                                ),
                              ],
                            ),
                          )
                        else if (esPendiente)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            decoration: BoxDecoration(
                              color: isDark ? const Color(0xFF451A03) : const Color(0xFFFFFBEB),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: isDark ? const Color(0xFFD97706) : const Color(0xFFFDE68A),
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  Icons.warning_amber_rounded,
                                  size: 11,
                                  color: isDark ? const Color(0xFFFBBF24) : const Color(0xFFD97706),
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  'Pendiente',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w700,
                                    color: isDark ? const Color(0xFFFBBF24) : const Color(0xFFD97706),
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
                        color: esPendiente
                            ? (isDark ? const Color(0xFFFBBF24) : const Color(0xFFB45309))
                            : PukllaDashboardStyles.textSecondary(context),
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
              ] else ...[
                if (tieneHorario) ...[
                  const SizedBox(width: 4),
                  IconButton(
                    onPressed: () {
                      final uri = Uri.parse(periodo.horario!);
                      launchUrl(uri, mode: LaunchMode.externalApplication);
                    },
                    icon: const Icon(Icons.picture_as_pdf_outlined, size: 18),
                    tooltip: 'Ver horario del ciclo',
                    style: IconButton.styleFrom(
                      backgroundColor: AppColors.gold.withValues(alpha: isDark ? 0.20 : 0.12),
                      foregroundColor: isDark ? const Color(0xFFF1B768) : AppColors.goldHover,
                      padding: const EdgeInsets.all(7),
                      minimumSize: const Size(34, 34),
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                  ),
                ],
                const SizedBox(width: 2),
                IconButton(
                  onPressed: () => context.push('/calificaciones'),
                  icon: const Icon(Icons.chevron_right_rounded, size: 22),
                  tooltip: 'Ver asignaturas y notas',
                  style: IconButton.styleFrom(
                    foregroundColor: isDark ? AppColors.gold : AppColors.teal,
                    padding: const EdgeInsets.all(4),
                    minimumSize: const Size(32, 32),
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                ),
              ],
            ],
          ),
        ),
      );
    }
  }

// ── BENTO GRID COMPLETO (MÓDULOS DE LA WEB + CARNET + TUTOR) ──
class _BentoGridModulos extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final perfil = ref.watch(perfilProvider).valueOrNull;

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
                icon: Icons.credit_card_rounded,
                color: const Color(0xFFB96328),
                title: 'Carnet Digital',
                subtitle: 'ID Estudiante & Biblioteca',
                onTap: () {
                  if (perfil != null) {
                    CarnetDigitalSheet.show(context, perfil);
                  } else {
                    context.go('/ficha-tecnica');
                  }
                },
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _BentoItem(
                icon: Icons.support_agent_rounded,
                color: AppColors.teal,
                title: 'Mi Tutor',
                subtitle: perfil?.tutor != null
                    ? perfil!.tutor!.nombre
                    : 'Acompañamiento docente',
                onTap: () => context.go('/ficha-tecnica'),
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
                title: 'Guías de TI y Tesis',
                subtitle: 'Biblioteca virtual y guías',
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
    final isDark = PukllaDashboardStyles.isDark(context);

    return PukllaScaleTap(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(15),
        decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(9),
                  decoration: BoxDecoration(
                    color: color.withValues(alpha: isDark ? 0.20 : 0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: color, size: 22),
                ),
                const Spacer(),
                Icon(
                  Icons.arrow_forward_ios,
                  size: 12,
                  color: isDark ? const Color(0xFF475569) : Colors.grey.shade400,
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: 14,
                color: PukllaDashboardStyles.textPrimary(context),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              subtitle,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 11.5,
                color: PukllaDashboardStyles.textSecondary(context),
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
    final isDark = PukllaDashboardStyles.isDark(context);

    return PukllaScaleTap(
      onTap: () => CursoDetalleSheet.show(context, curso),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(14),
        decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.08),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                Icons.book_outlined,
                color: isDark ? const Color(0xFF38BDF8) : AppColors.teal,
                size: 22,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    curso.nombre,
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                      color: PukllaDashboardStyles.textPrimary(context),
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    '${curso.cc} · ${curso.horas} h · Toca para ver detalle',
                    style: TextStyle(fontSize: 12, color: PukllaDashboardStyles.textSecondary(context)),
                  ),
                ],
              ),
            ),
            if (curso.creditos.isNotEmpty && curso.creditos != '—')
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '${curso.creditos} CR',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
                  ),
                ),
              ),
            const SizedBox(width: 6),
            Icon(Icons.chevron_right_rounded, size: 20, color: PukllaDashboardStyles.textSecondary(context)),
          ],
        ),
      ),
    );
  }
}
