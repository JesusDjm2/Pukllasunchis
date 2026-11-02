import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../../../core/widgets/stat_card.dart';
import '../../../routing/app_drawer.dart';
import '../../auth/application/auth_controller.dart';
import '../application/docente_providers.dart';
import '../data/docente_alumno.dart';
import '../data/docente_curso.dart';
import '../data/docente_resumen.dart';

/// Dashboard integral del docente, calcado de `docentes/show.blade.php`
/// y `layouts/docente.blade.php` con soporte para roles especiales:
/// Docente, Tutor, Investigador Taq'e, Titulación, Bolsa de Trabajo y Admin.
class DocenteHomeScreen extends ConsumerStatefulWidget {
  const DocenteHomeScreen({super.key});

  @override
  ConsumerState<DocenteHomeScreen> createState() => _DocenteHomeScreenState();
}

class _DocenteHomeScreenState extends ConsumerState<DocenteHomeScreen> {
  // Filtro de cursos: 'todos', 'fid', 'ppd'
  String _filtroArea = 'todos';

  void _mostrarModalAlumnos(BuildContext context, {int? cursoIdFiltro}) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _ModalAlumnosDocente(cursoIdFiltro: cursoIdFiltro),
    );
  }

  void _mostrarModalCalificar(BuildContext context, DocenteCurso curso) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _ModalCalificarCurso(curso: curso),
    );
  }

  void _mostrarModalSelectorCalificar(BuildContext context, List<DocenteCurso> cursos) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _ModalSelectorCalificar(
        cursos: cursos,
        onSeleccionarCurso: (c) {
          Navigator.pop(ctx);
          _mostrarModalCalificar(context, c);
        },
      ),
    );
  }

  void _mostrarModalTutoria(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => const _ModalTutoriaDocente(),
    );
  }

  void _mostrarModalTaqe(BuildContext context, DocenteResumen resumen) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _ModalTaqeDocente(resumen: resumen),
    );
  }

  void _mostrarModalTitulacion(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => const _ModalTitulacionDocente(),
    );
  }

  void _mostrarModalBolsa(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => const _ModalBolsaDocente(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final resumenAsync = ref.watch(docenteResumenProvider);
    final cursosAsync = ref.watch(docenteCursosProvider);
    final nombre = ref.watch(authControllerProvider).user?.name ?? 'Docente';
    final cursos = cursosAsync.valueOrNull ?? [];

    // Reaccionar a las acciones disparadas desde la barra de navegación inferior
    ref.listen<DocenteModalVista?>(docenteModalVistaProvider, (prev, next) {
      if (next == null) return;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        ref.read(docenteModalVistaProvider.notifier).state = null;
        switch (next) {
          case DocenteModalVista.alumnos:
            _mostrarModalAlumnos(context);
            break;
          case DocenteModalVista.calificar:
            _mostrarModalSelectorCalificar(context, cursos);
            break;
          case DocenteModalVista.tutoria:
            _mostrarModalTutoria(context);
            break;
        }
      });
    });

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mi panel'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(docenteResumenProvider);
          ref.invalidate(docenteCursosProvider);
          ref.invalidate(docenteAlumnosProvider);
          await ref.read(docenteResumenProvider.future);
        },
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            // Hero Header con Saludo, Período y Badges de Roles
            _DocenteHeroHeader(
              nombre: nombre,
              resumen: resumenAsync.valueOrNull,
            ),

            Padding(
              padding: const EdgeInsets.fromLTRB(16, 18, 16, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Tarjetas KPI de Resumen
                  resumenAsync.when(
                    data: (data) => _KpisSection(
                      resumen: data,
                      onVerAlumnos: () => _mostrarModalAlumnos(context),
                    ),
                    loading: () => const _ResumenSkeleton(),
                    error: (e, _) => ErrorState(
                      message: 'No se pudo cargar tu resumen.',
                      onRetry: () => ref.invalidate(docenteResumenProvider),
                    ),
                  ),

                  // Sección "Mis Funciones Especiales" según los roles del usuario
                  if (resumenAsync.valueOrNull != null &&
                      resumenAsync.valueOrNull!.tieneFuncionesEspeciales) ...[
                    const SizedBox(height: 24),
                    _FuncionesEspecialesSection(
                      resumen: resumenAsync.valueOrNull!,
                      onVerAlumnos: () => _mostrarModalAlumnos(context),
                      onVerTutoria: () => _mostrarModalTutoria(context),
                      onVerTaqe: () => _mostrarModalTaqe(context, resumenAsync.valueOrNull!),
                      onVerTitulacion: () => _mostrarModalTitulacion(context),
                      onVerBolsa: () => _mostrarModalBolsa(context),
                    ),
                  ],

                  const SizedBox(height: 26),

                  // Cabecera de Mis Cursos con Interruptor FID / PPD (igual a la web)
                  _CabeceraCursosConFiltro(
                    filtroActual: _filtroArea,
                    cursos: cursosAsync.valueOrNull ?? [],
                    onFiltroChanged: (area) => setState(() => _filtroArea = area),
                  ),
                  const SizedBox(height: 14),

                  // Listado de Cursos
                  cursosAsync.when(
                    data: (lista) {
                      final filtrados = lista.where((c) {
                        if (_filtroArea == 'fid') return !c.esPpd;
                        if (_filtroArea == 'ppd') return c.esPpd;
                        return true;
                      }).toList();

                      if (filtrados.isEmpty) {
                        return EmptyState(
                          icon: Icons.school_outlined,
                          message: _filtroArea == 'todos'
                              ? 'No tienes cursos asignados todavía.'
                              : 'No tienes cursos en el área ${_filtroArea.toUpperCase()}.',
                        );
                      }

                      return Column(
                        children: [
                          for (final curso in filtrados)
                            Padding(
                              padding: const EdgeInsets.only(bottom: 14),
                              child: _TarjetaCursoSenior(
                                curso: curso,
                                onVerAlumnos: () => _mostrarModalAlumnos(context, cursoIdFiltro: curso.id),
                                onCalificar: () => _mostrarModalCalificar(context, curso),
                              ),
                            ),
                        ],
                      );
                    },
                    loading: () => const Padding(
                      padding: EdgeInsets.symmetric(vertical: 24),
                      child: PukllaPreloader.inline(mensaje: 'Cargando asignaturas del docente...'),
                    ),
                    error: (e, _) => ErrorState(
                      message: 'No se pudieron cargar tus cursos.',
                      onRetry: () => ref.invalidate(docenteCursosProvider),
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

/// Cabecera Hero con Saludo, Período y Badges de los Roles Activos
class _DocenteHeroHeader extends StatelessWidget {
  const _DocenteHeroHeader({
    required this.nombre,
    required this.resumen,
  });

  final String nombre;
  final DocenteResumen? resumen;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final iniciales = nombre.trim().isEmpty
        ? 'D'
        : nombre.trim().split(RegExp(r'\s+')).take(2).map((p) => p[0].toUpperCase()).join();

    final periodoTexto = resumen?.periodoActual != null
        ? 'Período académico ${resumen!.periodoActual}'
        : 'Período académico activo';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 26),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: isDark
              ? const [Color(0xFF0F172A), Color(0xFF1E293B)]
              : const [Color(0xFF1E293B), Color(0xFF33445A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: isDark ? Colors.black45 : const Color(0x240F172A),
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
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.gold, width: 2),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.gold.withValues(alpha: 0.35),
                      blurRadius: 10,
                      spreadRadius: 1,
                    ),
                  ],
                ),
                child: CircleAvatar(
                  radius: 26,
                  backgroundColor: AppColors.gold.withValues(alpha: 0.22),
                  child: Text(
                    iniciales,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18),
                  ),
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Hola, $nombre',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18.5,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.2,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3.5),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: isDark ? 0.08 : 0.14),
                        borderRadius: BorderRadius.circular(999),
                        border: Border.all(
                          color: Colors.white.withValues(alpha: isDark ? 0.15 : 0.25),
                          width: 0.8,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.event_available, size: 12, color: AppColors.goldHover),
                          const SizedBox(width: 5),
                          Text(
                            periodoTexto,
                            style: const TextStyle(color: Color(0xFFE2E8F0), fontSize: 11.5, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          // Roles activos en forma de pills (Docente, Tutor, Investigador, Titulación, etc.)
          if (resumen != null && resumen!.roles.isNotEmpty) ...[
            const SizedBox(height: 14),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                const _RolPill(label: 'Docente', color: Color(0xFF38BDF8)),
                if (resumen!.esTutor)
                  const _RolPill(label: 'Tutor(a)', color: Color(0xFF34D399), icon: Icons.support_agent),
                if (resumen!.esInvestigador)
                  const _RolPill(label: "Investigador Taq'e", color: Color(0xFFA78BFA), icon: Icons.science_outlined),
                if (resumen!.esTitulacion)
                  const _RolPill(label: 'Titulación', color: Color(0xFFFBBF24), icon: Icons.school_outlined),
                if (resumen!.esBolsa)
                  const _RolPill(label: 'Bolsa de trabajo', color: Color(0xFF60A5FA), icon: Icons.work_outline),
                if (resumen!.esAdmin)
                  const _RolPill(label: 'Administrador', color: Color(0xFFF87171), icon: Icons.security),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

class _RolPill extends StatelessWidget {
  const _RolPill({
    required this.label,
    required this.color,
    this.icon,
  });

  final String label;
  final Color color;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.18),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: color.withValues(alpha: 0.40)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 12, color: color),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: color),
          ),
        ],
      ),
    );
  }
}

/// Grid de métricas clave (KPIs)
class _KpisSection extends StatelessWidget {
  const _KpisSection({
    required this.resumen,
    required this.onVerAlumnos,
  });

  final DocenteResumen resumen;
  final VoidCallback onVerAlumnos;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: StatCard(
                icon: Icons.school_outlined,
                value: '${resumen.cursos}',
                label: 'Cursos asignados',
                detail: '${resumen.cursosFid} FID · ${resumen.cursosPpd} PPD',
                color: AppColors.gold,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: StatCard(
                icon: Icons.people_alt_outlined,
                value: '${resumen.totalAlumnos}',
                label: 'Alumnos en cursos',
                detail: 'Ver directorio',
                color: AppColors.teal,
                onTap: onVerAlumnos,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: StatCard(
                icon: Icons.assignment_outlined,
                value: '${resumen.incidenciasTotal}',
                label: 'Incidencias',
                detail: '${resumen.incidenciasPendientes} pendientes',
                color: resumen.incidenciasPendientes > 0 ? const Color(0xFFD97706) : const Color(0xFF10B981),
                onTap: () => context.go('/docente/incidencias'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: StatCard(
                icon: Icons.notifications_outlined,
                value: '${resumen.notificacionesSinLeer}',
                label: 'Notificaciones',
                detail: resumen.notificacionesSinLeer > 0 ? 'Sin leer' : 'Al día',
                color: resumen.notificacionesSinLeer > 0 ? AppColors.danger : const Color(0xFF64748B),
                onTap: () => context.go('/notificaciones'),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

/// Sección dinámica de "Mis Funciones Especiales" según los roles del docente
class _FuncionesEspecialesSection extends StatelessWidget {
  const _FuncionesEspecialesSection({
    required this.resumen,
    required this.onVerAlumnos,
    required this.onVerTutoria,
    required this.onVerTaqe,
    required this.onVerTitulacion,
    required this.onVerBolsa,
  });

  final DocenteResumen resumen;
  final VoidCallback onVerAlumnos;
  final VoidCallback onVerTutoria;
  final VoidCallback onVerTaqe;
  final VoidCallback onVerTitulacion;
  final VoidCallback onVerBolsa;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(Icons.stars_rounded, size: 20, color: AppColors.gold),
            const SizedBox(width: 8),
            Text(
              'Mis funciones institucionales',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: isDark ? AppColors.darkText : AppColors.tealDark,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),

        // 1. Tarjeta de Tutoría (si es tutor)
        if (resumen.esTutor) ...[
          _FuncionCard(
            icon: Icons.support_agent_outlined,
            iconoBg: const Color(0xFFECFDF5),
            iconoColor: const Color(0xFF10B981),
            titulo: 'Tutoría y Acompañamiento',
            subtitulo: resumen.tutorData != null && resumen.tutorData!.ciclos.isNotEmpty
                ? 'Ciclos a cargo: ${resumen.tutorData!.ciclos.map((c) => c.nombre).join(", ")}'
                : 'Gestión y atención a tus estudiantes asignados',
            chips: [
              if (resumen.tutorData != null) ...[
                _BadgeInfo(
                  texto: '${resumen.tutorData!.tutoriasPendientes} tutorías pend.',
                  isAlert: resumen.tutorData!.tutoriasPendientes > 0,
                ),
                _BadgeInfo(
                  texto: '${resumen.tutorData!.sugerenciasPendientes} sugerencias',
                  isAlert: resumen.tutorData!.sugerenciasPendientes > 0,
                ),
              ],
            ],
            onTap: onVerTutoria,
          ),
          const SizedBox(height: 10),
        ],

        // 2. Tarjeta Taq'e Investigación (si es investigador o tiene proyectos)
        if (resumen.esInvestigador || resumen.proyectosACargo.isNotEmpty) ...[
          _FuncionCard(
            icon: Icons.science_outlined,
            iconoBg: const Color(0xFFF5F3FF),
            iconoColor: const Color(0xFF8B5CF6),
            titulo: "Taq'e — Seguimiento de Proyectos",
            subtitulo: 'Ferias de investigación, asesoría metodológica y proyectos a tu cargo.',
            chips: [
              if (resumen.investigadorData != null && resumen.investigadorData!.solicitudesPendientes > 0)
                _BadgeInfo(
                  texto: '${resumen.investigadorData!.solicitudesPendientes} consultas pend.',
                  isAlert: true,
                ),
              if (resumen.proyectosACargo.isNotEmpty)
                _BadgeInfo(
                  texto: '${resumen.proyectosACargo.length} proyectos asignados',
                  isAlert: false,
                ),
            ],
            onTap: onVerTaqe,
          ),
          const SizedBox(height: 10),
        ],

        // 3. Proceso de Titulación (si tiene rol gestor o secretaria)
        if (resumen.esTitulacion) ...[
          _FuncionCard(
            icon: Icons.school_outlined,
            iconoBg: const Color(0xFFFFFBEB),
            iconoColor: const Color(0xFFD97706),
            titulo: 'Proceso de Titulación',
            subtitulo: 'Seguimiento de egresados, planes de tesis y sustentaciones.',
            onTap: onVerTitulacion,
          ),
          const SizedBox(height: 10),
        ],

        // 4. Bolsa de Trabajo (si tiene rol adminB)
        if (resumen.esBolsa) ...[
          _FuncionCard(
            icon: Icons.work_outline,
            iconoBg: const Color(0xFFEFF6FF),
            iconoColor: const Color(0xFF2563EB),
            titulo: 'Bolsa de Trabajo',
            subtitulo: 'Gestión y seguimiento de ofertas laborales para egresados.',
            onTap: onVerBolsa,
          ),
          const SizedBox(height: 10),
        ],

        // 5. Directorio de Mis Alumnos
        _FuncionCard(
          icon: Icons.contacts_outlined,
          iconoBg: const Color(0xFFEFF6FF),
          iconoColor: const Color(0xFF3B82F6),
          titulo: 'Directorio de Mis Alumnos',
          subtitulo: 'Consulta los estudiantes inscritos en tus asignaturas activas.',
          onTap: onVerAlumnos,
        ),
      ],
    );
  }
}

class _FuncionCard extends StatelessWidget {
  const _FuncionCard({
    required this.icon,
    required this.iconoBg,
    required this.iconoColor,
    required this.titulo,
    required this.subtitulo,
    this.chips = const [],
    required this.onTap,
  });

  final IconData icon;
  final Color iconoBg;
  final Color iconoColor;
  final String titulo;
  final String subtitulo;
  final List<Widget> chips;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return PukllaScaleTap(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: isDark ? iconoColor.withValues(alpha: 0.18) : iconoBg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: iconoColor, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    titulo,
                    style: TextStyle(
                      fontSize: 14.5,
                      fontWeight: FontWeight.w700,
                      color: isDark ? AppColors.darkText : AppColors.tealDark,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitulo,
                    style: TextStyle(
                      fontSize: 12,
                      color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                      height: 1.3,
                    ),
                  ),
                  if (chips.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Wrap(spacing: 6, runSpacing: 4, children: chips),
                  ],
                ],
              ),
            ),
            Icon(Icons.chevron_right, color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8), size: 20),
          ],
        ),
      ),
    );
  }
}

class _BadgeInfo extends StatelessWidget {
  const _BadgeInfo({required this.texto, required this.isAlert});

  final String texto;
  final bool isAlert;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final bg = isAlert
        ? (isDark ? const Color(0xFF450A0A) : const Color(0xFFFEF2F2))
        : (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9));
    final border = isAlert
        ? (isDark ? const Color(0xFFB91C1C) : const Color(0xFFFECACA))
        : (isDark ? AppColors.darkBorder : const Color(0xFFCBD5E1));
    final text = isAlert
        ? (isDark ? const Color(0xFFFCA5A5) : const Color(0xFFDC2626))
        : (isDark ? AppColors.darkText : const Color(0xFF475569));

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: border),
      ),
      child: Text(
        texto,
        style: TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
          color: text,
        ),
      ),
    );
  }
}

/// Cabecera con Interruptor FID / PPD (idéntico a la web .docente-dash-toggle)
class _CabeceraCursosConFiltro extends StatelessWidget {
  const _CabeceraCursosConFiltro({
    required this.filtroActual,
    required this.cursos,
    required this.onFiltroChanged,
  });

  final String filtroActual;
  final List<DocenteCurso> cursos;
  final ValueChanged<String> onFiltroChanged;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final fidCount = cursos.where((c) => !c.esPpd).length;
    final ppdCount = cursos.where((c) => c.esPpd).length;

    return Row(
      children: [
        const Icon(Icons.book_outlined, color: AppColors.teal, size: 20),
        const SizedBox(width: 8),
        Text(
          'Mis cursos',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w800,
            color: isDark ? AppColors.darkText : AppColors.tealDark,
          ),
        ),
        const Spacer(),
        // Selector tipo píldora FID / PPD
        Container(
          padding: const EdgeInsets.all(3),
          decoration: BoxDecoration(
            color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(999),
            border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              _FiltroChipBtn(
                label: 'Todos (${cursos.length})',
                isSelected: filtroActual == 'todos',
                onTap: () => onFiltroChanged('todos'),
              ),
              _FiltroChipBtn(
                label: 'FID ($fidCount)',
                isSelected: filtroActual == 'fid',
                onTap: () => onFiltroChanged('fid'),
              ),
              if (ppdCount > 0)
                _FiltroChipBtn(
                  label: 'PPD ($ppdCount)',
                  isSelected: filtroActual == 'ppd',
                  onTap: () => onFiltroChanged('ppd'),
                ),
            ],
          ),
        ),
      ],
    );
  }
}

class _FiltroChipBtn extends StatelessWidget {
  const _FiltroChipBtn({
    required this.label,
    required this.isSelected,
    required this.onTap,
  });

  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(999),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 160),
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.teal : Colors.transparent,
          borderRadius: BorderRadius.circular(999),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected ? Colors.white : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
          ),
        ),
      ),
    );
  }
}

/// Tarjeta de Curso de Nivel Senior con métricas, barras de evaluación y estado de sílabo
class _TarjetaCursoSenior extends StatelessWidget {
  const _TarjetaCursoSenior({
    required this.curso,
    required this.onVerAlumnos,
    required this.onCalificar,
  });

  final DocenteCurso curso;
  final VoidCallback onVerAlumnos;
  final VoidCallback onCalificar;

  static const _etiquetas = {
    'parcial_1': 'Parcial 1',
    'parcial_2': 'Parcial 2',
    'desempeno': 'Desempeño',
    'calificado': 'Calificado',
  };

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final accentColor = curso.esPpd ? const Color(0xFF9333EA) : AppColors.teal;

    return Container(
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
      clipBehavior: Clip.antiAlias,
      child: IntrinsicHeight(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Indicador de color lateral (FID = Verde azulado, PPD = Púrpura)
            Container(
              width: 5,
              color: accentColor,
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Título y Badge de Área (FID / PPD)
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                curso.nombre,
                                style: TextStyle(
                                  fontWeight: FontWeight.w800,
                                  fontSize: 15.5,
                                  color: isDark ? AppColors.darkText : AppColors.tealDark,
                                ),
                              ),
                              if (curso.ciclo != null || curso.programa != null)
                                Padding(
                                  padding: const EdgeInsets.only(top: 3),
                                  child: Text(
                                    [curso.ciclo, curso.programa].whereType<String>().join(' · '),
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: curso.esPpd
                                ? (isDark ? const Color(0xFF581C87).withValues(alpha: 0.4) : const Color(0xFFF3E8FF))
                                : (isDark ? const Color(0xFF0C4A6E).withValues(alpha: 0.4) : const Color(0xFFE0F2FE)),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: curso.esPpd
                                  ? (isDark ? const Color(0xFF9333EA).withValues(alpha: 0.4) : const Color(0xFFE9D5FF))
                                  : (isDark ? const Color(0xFF0284C7).withValues(alpha: 0.4) : const Color(0xFFBAE6FD)),
                            ),
                          ),
                          child: Text(
                            curso.esPpd ? 'PPD' : 'FID',
                            style: TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w800,
                              color: curso.esPpd
                                  ? (isDark ? const Color(0xFFC084FC) : const Color(0xFF7E22CE))
                                  : (isDark ? const Color(0xFF38BDF8) : const Color(0xFF0369A1)),
                            ),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 12),

                    // Chips informativos: Horas, Créditos, Alumnos
                    Row(
                      children: [
                        if (curso.horas > 0)
                          _InfoPill(icon: Icons.access_time, texto: '${curso.horas} hrs/sem'),
                        if (curso.creditos > 0) ...[
                          const SizedBox(width: 6),
                          _InfoPill(icon: Icons.military_tech_outlined, texto: '${curso.creditos} créditos'),
                        ],
                        const Spacer(),
                        PukllaScaleTap(
                          onTap: onVerAlumnos,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.teal.withValues(alpha: isDark ? 0.20 : 0.10),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: AppColors.teal.withValues(alpha: isDark ? 0.35 : 0.20),
                              ),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.people, size: 13, color: AppColors.teal),
                                const SizedBox(width: 4),
                                Text(
                                  '${curso.alumnosCount} alumnos',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.teal),
                                ),
                                const SizedBox(width: 2),
                                const Icon(Icons.chevron_right, size: 14, color: AppColors.teal),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 14),
                    Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
                    const SizedBox(height: 12),

                    // Barras de progreso de evaluaciones
                    for (final entrada in curso.progreso.entries)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: _BarraProgresoSenior(
                          etiqueta: _etiquetas[entrada.key] ?? entrada.key,
                          valor: entrada.value,
                        ),
                      ),

                    const SizedBox(height: 10),

                    // Fila de acciones del curso: Sílabo institucional y Botón Calificar
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        // Badge de Sílabo institucional
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                          decoration: BoxDecoration(
                            color: curso.silaboListo
                                ? (isDark ? const Color(0xFF064E3B).withValues(alpha: 0.35) : const Color(0xFF10B981).withValues(alpha: 0.12))
                                : (isDark ? const Color(0xFF78350F).withValues(alpha: 0.35) : const Color(0xFFF59E0B).withValues(alpha: 0.12)),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: curso.silaboListo
                                  ? (isDark ? const Color(0xFF059669).withValues(alpha: 0.4) : const Color(0xFFA7F3D0))
                                  : (isDark ? const Color(0xFFD97706).withValues(alpha: 0.4) : const Color(0xFFFDE68A)),
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                curso.silaboListo ? Icons.check_circle_outline : Icons.error_outline,
                                size: 14,
                                color: curso.silaboListo
                                    ? (isDark ? const Color(0xFF34D399) : const Color(0xFF0B7A43))
                                    : (isDark ? const Color(0xFFFBBF24) : const Color(0xFFB45309)),
                              ),
                              const SizedBox(width: 5),
                              Text(
                                curso.silaboListo ? 'Sílabo registrado' : 'Sílabo pendiente',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w700,
                                  color: curso.silaboListo
                                      ? (isDark ? const Color(0xFF34D399) : const Color(0xFF0B7A43))
                                      : (isDark ? const Color(0xFFFBBF24) : const Color(0xFFB45309)),
                                ),
                              ),
                            ],
                          ),
                        ),

                        // Botón dorado Calificar (espejo del botón de la app web)
                        PukllaScaleTap(
                          onTap: onCalificar,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6.5),
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [AppColors.gold, Color(0xFFD97706)],
                              ),
                              borderRadius: BorderRadius.circular(8),
                              boxShadow: [
                                BoxShadow(
                                  color: AppColors.gold.withValues(alpha: 0.30),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.edit_note_rounded, size: 16, color: Colors.white),
                                SizedBox(width: 5),
                                Text(
                                  'Calificar',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w800,
                                    color: Colors.white,
                                    letterSpacing: 0.2,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoPill extends StatelessWidget {
  const _InfoPill({required this.icon, required this.texto});

  final IconData icon;
  final String texto;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
          const SizedBox(width: 4),
          Text(
            texto,
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.w600,
              color: isDark ? AppColors.darkText : const Color(0xFF475569),
            ),
          ),
        ],
      ),
    );
  }
}

class _BarraProgresoSenior extends StatelessWidget {
  const _BarraProgresoSenior({required this.etiqueta, required this.valor});

  final String etiqueta;
  final double valor;

  Color get _color {
    if (valor >= 80) return const Color(0xFF10B981);
    if (valor >= 40) return AppColors.gold;
    return const Color(0xFFEF4444);
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return Row(
      children: [
        SizedBox(
          width: 82,
          child: Text(
            etiqueta,
            style: TextStyle(
              fontSize: 12,
              color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        Expanded(
          child: ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: (valor / 100).clamp(0, 1),
              minHeight: 7,
              backgroundColor: isDark ? AppColors.darkBackground : const Color(0xFFF1F5F9),
              color: _color,
            ),
          ),
        ),
        const SizedBox(width: 10),
        SizedBox(
          width: 38,
          child: Text(
            '${valor.toStringAsFixed(0)}%',
            textAlign: TextAlign.right,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              color: isDark ? AppColors.darkText : const Color(0xFF334155),
            ),
          ),
        ),
      ],
    );
  }
}

class _ResumenSkeleton extends StatelessWidget {
  const _ResumenSkeleton();

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.symmetric(vertical: 20),
      child: PukllaPreloader.inline(mensaje: 'Cargando indicadores del período...'),
    );
  }
}

/// Hoja Modal para explorar los Alumnos de los cursos del Docente con buscador en vivo
class _ModalAlumnosDocente extends ConsumerStatefulWidget {
  const _ModalAlumnosDocente({this.cursoIdFiltro});

  final int? cursoIdFiltro;

  @override
  ConsumerState<_ModalAlumnosDocente> createState() => _ModalAlumnosDocenteState();
}

class _ModalAlumnosDocenteState extends ConsumerState<_ModalAlumnosDocente> {
  final TextEditingController _busquedaController = TextEditingController();
  String _filtroTexto = '';

  @override
  void dispose() {
    _busquedaController.dispose();
    super.dispose();
  }

  Future<void> _contactarPorEmail(String email) async {
    final uri = Uri.parse('mailto:$email');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _contactarPorTelefono(String telefono) async {
    final uri = Uri.parse('tel:$telefono');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  @override
  Widget build(BuildContext context) {
    final alumnosAsync = ref.watch(docenteAlumnosProvider);
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.2),
            blurRadius: 25,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Píldora de arrastre
          Center(
            child: Container(
              width: 44,
              height: 4.5,
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkBorder : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Cabecera del modal
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.teal.withValues(alpha: isDark ? 0.20 : 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.people_alt, color: AppColors.teal, size: 22),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Mis Alumnos Matriculados',
                  style: TextStyle(
                    fontSize: 16.5,
                    fontWeight: FontWeight.w800,
                    color: isDark ? AppColors.darkText : AppColors.tealDark,
                  ),
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: Icon(Icons.close, size: 20, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Campo de búsqueda en vivo
          Container(
            decoration: BoxDecoration(
              color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
            ),
            child: TextField(
              controller: _busquedaController,
              onChanged: (val) => setState(() => _filtroTexto = val.trim().toLowerCase()),
              style: TextStyle(
                fontSize: 13.5,
                color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
              ),
              decoration: InputDecoration(
                hintText: 'Buscar por nombre, apellido, correo o DNI...',
                hintStyle: TextStyle(
                  fontSize: 12.5,
                  color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8),
                ),
                prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.gold),
                suffixIcon: _filtroTexto.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _busquedaController.clear();
                          setState(() => _filtroTexto = '');
                        },
                      )
                    : null,
                border: InputBorder.none,
                enabledBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              ),
            ),
          ),

          const SizedBox(height: 12),
          Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
          const SizedBox(height: 8),

          Expanded(
            child: alumnosAsync.when(
              loading: () => const Center(
                child: PukllaPreloader.inline(mensaje: 'Cargando directorio de estudiantes...'),
              ),
              error: (err, _) => Center(
                child: Text(
                  'Error al cargar alumnos: $err',
                  style: const TextStyle(color: AppColors.danger),
                ),
              ),
              data: (cursos) {
                final filtradosPorCurso = widget.cursoIdFiltro != null
                    ? cursos.where((c) => c.cursoId == widget.cursoIdFiltro).toList()
                    : cursos;

                if (filtradosPorCurso.isEmpty || filtradosPorCurso.every((c) => c.alumnos.isEmpty)) {
                  return Center(
                    child: Text(
                      'No hay alumnos registrados en este curso.',
                      style: TextStyle(color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                    ),
                  );
                }

                // Filtrar alumnos en memoria según la búsqueda
                final cursosConAlumnosFiltrados = <DocenteCursoAlumnos>[];
                for (final curso in filtradosPorCurso) {
                  final alumnosFiltrados = curso.alumnos.where((a) {
                    if (_filtroTexto.isEmpty) return true;
                    final nombreMatch = a.nombreCompleto.toLowerCase().contains(_filtroTexto);
                    final emailMatch = a.email?.toLowerCase().contains(_filtroTexto) ?? false;
                    final dniMatch = a.dni?.contains(_filtroTexto) ?? false;
                    return nombreMatch || emailMatch || dniMatch;
                  }).toList();

                  if (alumnosFiltrados.isNotEmpty) {
                    cursosConAlumnosFiltrados.add(
                      DocenteCursoAlumnos(
                        cursoId: curso.cursoId,
                        cursoNombre: curso.cursoNombre,
                        ciclo: curso.ciclo,
                        programa: curso.programa,
                        total: alumnosFiltrados.length,
                        alumnos: alumnosFiltrados,
                      ),
                    );
                  }
                }

                if (cursosConAlumnosFiltrados.isEmpty) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.person_search_outlined, size: 48, color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8)),
                          const SizedBox(height: 12),
                          Text(
                            'No se encontraron coincidencias para "$_filtroTexto"',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 13.5,
                              color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                return ListView.builder(
                  itemCount: cursosConAlumnosFiltrados.length,
                  itemBuilder: (context, idx) {
                    final c = cursosConAlumnosFiltrados[idx];
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          margin: const EdgeInsets.symmetric(vertical: 8),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          decoration: BoxDecoration(
                            color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
                          ),
                          child: Row(
                            children: [
                              Expanded(
                                child: Text(
                                  c.cursoNombre,
                                  style: TextStyle(
                                    fontWeight: FontWeight.w700,
                                    fontSize: 13,
                                    color: isDark ? AppColors.darkText : AppColors.tealDark,
                                  ),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.12),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  '${c.total} ${c.total == 1 ? "alumno" : "alumnos"}',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.teal),
                                ),
                              ),
                            ],
                          ),
                        ),
                        for (final a in c.alumnos)
                          Container(
                            margin: const EdgeInsets.only(bottom: 6),
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                            decoration: BoxDecoration(
                              color: isDark ? AppColors.darkSurface : Colors.white,
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: isDark ? AppColors.darkBorder.withValues(alpha: 0.5) : const Color(0xFFF1F5F9),
                              ),
                            ),
                            child: Row(
                              children: [
                                CircleAvatar(
                                  radius: 16,
                                  backgroundColor: AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.15),
                                  child: Text(
                                    a.nombreCompleto.isNotEmpty ? a.nombreCompleto[0].toUpperCase() : '?',
                                    style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.teal),
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        a.nombreCompleto,
                                        style: TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w600,
                                          color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                                        ),
                                      ),
                                      const SizedBox(height: 1),
                                      Text(
                                        [a.email, a.telefono].whereType<String>().where((s) => s.isNotEmpty).join(' · '),
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                // Acciones rápidas de contacto
                                if (a.email != null && a.email!.isNotEmpty)
                                  IconButton(
                                    icon: const Icon(Icons.email_outlined, size: 18, color: AppColors.teal),
                                    tooltip: 'Enviar correo a ${a.email}',
                                    onPressed: () => _contactarPorEmail(a.email!),
                                  ),
                                if (a.telefono != null && a.telefono!.isNotEmpty)
                                  IconButton(
                                    icon: const Icon(Icons.phone_outlined, size: 18, color: AppColors.gold),
                                    tooltip: 'Llamar a ${a.telefono}',
                                    onPressed: () => _contactarPorTelefono(a.telefono!),
                                  ),
                              ],
                            ),
                          ),
                      ],
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

/// Hoja modal para la gestión de Tutoría Docente (Solicitudes y Buzón de Sugerencias)
class _ModalTutoriaDocente extends ConsumerStatefulWidget {
  const _ModalTutoriaDocente();

  @override
  ConsumerState<_ModalTutoriaDocente> createState() => _ModalTutoriaDocenteState();
}

class _ModalTutoriaDocenteState extends ConsumerState<_ModalTutoriaDocente>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final tutorAsync = ref.watch(docenteTutorDashboardProvider);
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      height: MediaQuery.of(context).size.height * 0.85,
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.2),
            blurRadius: 25,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 44,
              height: 4.5,
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkBorder : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: isDark ? 0.20 : 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.support_agent_rounded, color: Color(0xFF10B981), size: 22),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Tutoría y Acompañamiento',
                      style: TextStyle(
                        fontSize: 16.5,
                        fontWeight: FontWeight.w800,
                        color: isDark ? AppColors.darkText : AppColors.tealDark,
                      ),
                    ),
                    Text(
                      'Gestión de tutorías y sugerencias de tus estudiantes',
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: Icon(Icons.close, size: 20, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
              ),
            ],
          ),
          const SizedBox(height: 12),
          tutorAsync.when(
            loading: () => const Expanded(
              child: Center(
                child: PukllaPreloader.inline(mensaje: 'Cargando información de tutoría...'),
              ),
            ),
            error: (err, _) => Expanded(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.error_outline, size: 40, color: AppColors.danger),
                    const SizedBox(height: 10),
                    Text(
                      'No se pudo cargar la información de tutoría',
                      style: TextStyle(
                        fontWeight: FontWeight.w600,
                        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: () => ref.invalidate(docenteTutorDashboardProvider),
                      style: ElevatedButton.styleFrom(backgroundColor: AppColors.teal),
                      child: const Text('Reintentar', style: TextStyle(color: Colors.white)),
                    ),
                  ],
                ),
              ),
            ),
            data: (tutorData) {
              return Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (tutorData.ciclos.isNotEmpty) ...[
                      Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        children: tutorData.ciclos.map((c) {
                          return Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.teal.withValues(alpha: isDark ? 0.20 : 0.10),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: AppColors.teal.withValues(alpha: 0.3)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.class_outlined, size: 14, color: AppColors.teal),
                                const SizedBox(width: 5),
                                Text(
                                  c,
                                  style: const TextStyle(
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.teal,
                                  ),
                                ),
                              ],
                            ),
                          );
                        }).toList(),
                      ),
                      const SizedBox(height: 12),
                    ],
                    TabBar(
                      controller: _tabController,
                      labelColor: isDark ? AppColors.goldHover : AppColors.tealDark,
                      unselectedLabelColor: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                      indicatorColor: AppColors.teal,
                      indicatorWeight: 3,
                      tabs: [
                        Tab(text: 'Solicitudes (${tutorData.tutorias.length})'),
                        Tab(text: 'Buzón (${tutorData.sugerencias.length})'),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Expanded(
                      child: TabBarView(
                        controller: _tabController,
                        children: [
                          // Tab 1: Solicitudes de Tutoría
                          tutorData.tutorias.isEmpty
                              ? Center(
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.mark_email_read_outlined,
                                          size: 44,
                                          color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8)),
                                      const SizedBox(height: 10),
                                      Text(
                                        'No tienes solicitudes de tutoría pendientes',
                                        style: TextStyle(
                                          fontWeight: FontWeight.w600,
                                          color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                                        ),
                                      ),
                                    ],
                                  ),
                                )
                              : ListView.separated(
                                  padding: const EdgeInsets.symmetric(vertical: 8),
                                  itemCount: tutorData.tutorias.length,
                                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                                  itemBuilder: (context, index) {
                                    final item = tutorData.tutorias[index];
                                    final esUrgente = item.urgencia?.toLowerCase() == 'alta';
                                    final esPendiente = item.estado.toLowerCase() == 'pendiente';

                                    return Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(
                                          color: esUrgente
                                              ? AppColors.danger.withValues(alpha: 0.3)
                                              : PukllaDashboardStyles.borderColor(context),
                                        ),
                                      ),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              Expanded(
                                                child: Text(
                                                  item.alumnoNombre,
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.w700,
                                                    fontSize: 13.5,
                                                    color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                                                  ),
                                                ),
                                              ),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                decoration: BoxDecoration(
                                                  color: esPendiente
                                                      ? const Color(0xFFD97706).withValues(alpha: 0.15)
                                                      : const Color(0xFF10B981).withValues(alpha: 0.15),
                                                  borderRadius: BorderRadius.circular(6),
                                                ),
                                                child: Text(
                                                  item.estado.toUpperCase(),
                                                  style: TextStyle(
                                                    fontSize: 10,
                                                    fontWeight: FontWeight.w700,
                                                    color: esPendiente
                                                        ? const Color(0xFFD97706)
                                                        : const Color(0xFF10B981),
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                          if (item.ciclo != null || item.programa != null) ...[
                                            const SizedBox(height: 3),
                                            Text(
                                              [item.programa, item.ciclo].whereType<String>().join(' · '),
                                              style: TextStyle(
                                                fontSize: 11,
                                                color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                                              ),
                                            ),
                                          ],
                                          const SizedBox(height: 8),
                                          Text(
                                            item.motivo,
                                            style: TextStyle(
                                              fontSize: 12.5,
                                              color: isDark ? AppColors.darkText : const Color(0xFF334155),
                                            ),
                                          ),
                                          if (item.fecha != null) ...[
                                            const SizedBox(height: 8),
                                            Text(
                                              'Fecha: ${item.fecha}',
                                              style: TextStyle(
                                                fontSize: 10.5,
                                                color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8),
                                              ),
                                            ),
                                          ],
                                        ],
                                      ),
                                    );
                                  },
                                ),

                          // Tab 2: Sugerencias
                          tutorData.sugerencias.isEmpty
                              ? Center(
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.inbox_outlined,
                                          size: 44,
                                          color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8)),
                                      const SizedBox(height: 10),
                                      Text(
                                        'No hay sugerencias en el buzón',
                                        style: TextStyle(
                                          fontWeight: FontWeight.w600,
                                          color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                                        ),
                                      ),
                                    ],
                                  ),
                                )
                              : ListView.separated(
                                  padding: const EdgeInsets.symmetric(vertical: 8),
                                  itemCount: tutorData.sugerencias.length,
                                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                                  itemBuilder: (context, index) {
                                    final item = tutorData.sugerencias[index];
                                    return Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                                        borderRadius: BorderRadius.circular(12),
                                        border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
                                      ),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            children: [
                                              Expanded(
                                                child: Text(
                                                  item.alumnoNombre,
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.w700,
                                                    fontSize: 13,
                                                    color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                                                  ),
                                                ),
                                              ),
                                              if (item.fecha != null)
                                                Text(
                                                  item.fecha!,
                                                  style: TextStyle(
                                                    fontSize: 10.5,
                                                    color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8),
                                                  ),
                                                ),
                                            ],
                                          ),
                                          if (item.ciclo != null || item.programa != null) ...[
                                            const SizedBox(height: 2),
                                            Text(
                                              [item.programa, item.ciclo].whereType<String>().join(' · '),
                                              style: TextStyle(
                                                fontSize: 11,
                                                color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                                              ),
                                            ),
                                          ],
                                          const SizedBox(height: 8),
                                          Text(
                                            item.mensaje,
                                            style: TextStyle(
                                              fontSize: 12.5,
                                              color: isDark ? AppColors.darkText : const Color(0xFF334155),
                                            ),
                                          ),
                                        ],
                                      ),
                                    );
                                  },
                                ),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}

/// Hoja modal para Proyectos Taq'e e Investigación a cargo del docente
class _ModalTaqeDocente extends StatelessWidget {
  const _ModalTaqeDocente({required this.resumen});

  final DocenteResumen resumen;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      height: MediaQuery.of(context).size.height * 0.80,
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.2),
            blurRadius: 25,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 44,
              height: 4.5,
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkBorder : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFF8B5CF6).withValues(alpha: isDark ? 0.20 : 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.science_rounded, color: Color(0xFF8B5CF6), size: 22),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      "Taq'e — Proyectos de Investigación",
                      style: TextStyle(
                        fontSize: 16.5,
                        fontWeight: FontWeight.w800,
                        color: isDark ? AppColors.darkText : AppColors.tealDark,
                      ),
                    ),
                    Text(
                      'Seguimiento y evaluación de proyectos integradores',
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: Icon(Icons.close, size: 20, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Tarjeta informativa de consultas de investigación
          if (resumen.investigadorData != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFF8B5CF6).withValues(alpha: isDark ? 0.15 : 0.08),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFF8B5CF6).withValues(alpha: 0.25)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.help_center_outlined, color: Color(0xFF8B5CF6), size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Consultas metodológicas asignadas: ${resumen.investigadorData!.solicitudesPendientes} pendientes.',
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: isDark ? AppColors.darkText : const Color(0xFF334155),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),
          ],

          Text(
            'Proyectos Integradores Asignados (${resumen.proyectosACargo.length})',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: isDark ? AppColors.darkText : AppColors.tealDark,
            ),
          ),
          const SizedBox(height: 10),

          Expanded(
            child: resumen.proyectosACargo.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.folder_open_outlined,
                            size: 44,
                            color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8)),
                        const SizedBox(height: 10),
                        Text(
                          'No tienes proyectos integradores asignados en este ciclo',
                          style: TextStyle(
                            fontWeight: FontWeight.w600,
                            color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                          ),
                        ),
                      ],
                    ),
                  )
                : ListView.separated(
                    itemCount: resumen.proyectosACargo.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (context, index) {
                      final proyecto = resumen.proyectosACargo[index];
                      final esEncargado = proyecto.rol.toLowerCase().contains('encargad');

                      return Container(
                        padding: const EdgeInsets.all(13),
                        decoration: BoxDecoration(
                          color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    proyecto.nombre,
                                    style: TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 13.5,
                                      color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                                    ),
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: esEncargado
                                        ? const Color(0xFF8B5CF6).withValues(alpha: 0.15)
                                        : const Color(0xFF3B82F6).withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    proyecto.rol.toUpperCase(),
                                    style: TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      color: esEncargado ? const Color(0xFF8B5CF6) : const Color(0xFF3B82F6),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            if (proyecto.producto != null && proyecto.producto!.isNotEmpty) ...[
                              const SizedBox(height: 6),
                              Row(
                                children: [
                                  const Icon(Icons.inventory_2_outlined, size: 14, color: AppColors.teal),
                                  const SizedBox(width: 5),
                                  Expanded(
                                    child: Text(
                                      'Entregable: ${proyecto.producto}',
                                      style: TextStyle(
                                        fontSize: 11.5,
                                        color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ],
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

/// Hoja modal para el Proceso de Titulación
class _ModalTitulacionDocente extends StatelessWidget {
  const _ModalTitulacionDocente();

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      height: MediaQuery.of(context).size.height * 0.70,
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.2),
            blurRadius: 25,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 44,
              height: 4.5,
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkBorder : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFD97706).withValues(alpha: isDark ? 0.20 : 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.school_rounded, color: Color(0xFFD97706), size: 22),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Proceso de Titulación',
                      style: TextStyle(
                        fontSize: 16.5,
                        fontWeight: FontWeight.w800,
                        color: isDark ? AppColors.darkText : AppColors.tealDark,
                      ),
                    ),
                    Text(
                      'Gestión de egresados, planes de tesis y sustentaciones',
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: Icon(Icons.close, size: 20, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
              ),
            ],
          ),
          const SizedBox(height: 18),

          // Opciones de Titulación
          const _ItemTitulacion(
            icon: Icons.description_outlined,
            titulo: 'Revisión de Planes de Tesis',
            descripcion: 'Revisión y emisión de dictamen metodológico y temático de proyectos.',
            color: Color(0xFFD97706),
          ),
          const SizedBox(height: 10),
          const _ItemTitulacion(
            icon: Icons.gavel_outlined,
            titulo: 'Jurados de Sustentación',
            descripcion: 'Consulta de fechas programadas y actas de sustentación asignadas.',
            color: Color(0xFF3B82F6),
          ),
          const SizedBox(height: 10),
          const _ItemTitulacion(
            icon: Icons.assignment_turned_in_outlined,
            titulo: 'Expedientes de Grados y Títulos',
            descripcion: 'Validación de constancias de egresado y cumplimiento de requisitos.',
            color: Color(0xFF10B981),
          ),
          const Spacer(),

          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              children: [
                const Icon(Icons.info_outline, size: 18, color: AppColors.gold),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'La firma digital de actas y dictámenes oficiales se realiza a través de la plataforma web institucional.',
                    style: TextStyle(
                      fontSize: 11.5,
                      color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Hoja modal para el Módulo de Bolsa de Trabajo
class _ModalBolsaDocente extends StatelessWidget {
  const _ModalBolsaDocente();

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      height: MediaQuery.of(context).size.height * 0.65,
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.2),
            blurRadius: 25,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 44,
              height: 4.5,
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkBorder : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFF2563EB).withValues(alpha: isDark ? 0.20 : 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.work_outline_rounded, color: Color(0xFF2563EB), size: 22),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Bolsa de Trabajo',
                      style: TextStyle(
                        fontSize: 16.5,
                        fontWeight: FontWeight.w800,
                        color: isDark ? AppColors.darkText : AppColors.tealDark,
                      ),
                    ),
                    Text(
                      'Gestión de oportunidades laborales y prácticas',
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: Icon(Icons.close, size: 20, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
              ),
            ],
          ),
          const SizedBox(height: 18),
          const _ItemTitulacion(
            icon: Icons.post_add_outlined,
            titulo: 'Publicación de Convocatorias',
            descripcion: 'Registro de vacantes de colegios e instituciones educativas.',
            color: Color(0xFF2563EB),
          ),
          const SizedBox(height: 10),
          const _ItemTitulacion(
            icon: Icons.person_search_outlined,
            titulo: 'Seguimiento de Postulaciones',
            descripcion: 'Revisión de perfiles de egresados postulantes a convocatorias activas.',
            color: Color(0xFF10B981),
          ),
          const Spacer(),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              children: [
                const Icon(Icons.info_outline, size: 18, color: AppColors.gold),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'La aprobación y publicación masiva de ofertas se realiza a través del módulo web de Bolsa de Trabajo.',
                    style: TextStyle(
                      fontSize: 11.5,
                      color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ItemTitulacion extends StatelessWidget {
  const _ItemTitulacion({
    required this.icon,
    required this.titulo,
    required this.descripcion,
    required this.color,
  });

  final IconData icon;
  final String titulo;
  final String descripcion;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withValues(alpha: isDark ? 0.20 : 0.12),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  titulo,
                  style: TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 13,
                    color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  descripcion,
                  style: TextStyle(
                    fontSize: 11.5,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Hoja Modal para seleccionar el curso a calificar (disparada desde el botón "Calificar" de la barra inferior)
class _ModalSelectorCalificar extends StatelessWidget {
  const _ModalSelectorCalificar({
    required this.cursos,
    required this.onSeleccionarCurso,
  });

  final List<DocenteCurso> cursos;
  final ValueChanged<DocenteCurso> onSeleccionarCurso;

  static const _etiquetas = {
    'parcial_1': 'Parcial 1',
    'parcial_2': 'Parcial 2',
    'desempeno': 'Desempeño',
    'calificado': 'Avance General',
  };

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      height: MediaQuery.of(context).size.height * 0.82,
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.2),
            blurRadius: 25,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Barra de arrastre
          Center(
            child: Container(
              width: 44,
              height: 4.5,
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkBorder : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Cabecera del selector
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: isDark ? 0.22 : 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.edit_note_rounded, color: AppColors.gold, size: 24),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Calificar Cursos',
                      style: TextStyle(
                        fontSize: 16.5,
                        fontWeight: FontWeight.w800,
                        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                      ),
                    ),
                    Text(
                      'Selecciona uno de tus cursos asignados para ingresar notas',
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: Icon(Icons.close, size: 20, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
          const SizedBox(height: 12),

          // Lista de cursos disponibles
          Expanded(
            child: cursos.isEmpty
                ? const Center(
                    child: EmptyState(
                      icon: Icons.school_outlined,
                      message: 'No tienes cursos activos para calificar en este período lectivo.',
                    ),
                  )
                : ListView.separated(
                    itemCount: cursos.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (context, i) {
                      final curso = cursos[i];
                      return Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        curso.nombre,
                                        style: TextStyle(
                                          fontWeight: FontWeight.w700,
                                          fontSize: 14.5,
                                          color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                                        ),
                                      ),
                                      const SizedBox(height: 5),
                                      Wrap(
                                        spacing: 6,
                                        runSpacing: 4,
                                        children: [
                                          if (curso.programa != null && curso.programa!.isNotEmpty)
                                            _InfoPill(icon: Icons.school_outlined, texto: curso.programa!),
                                          if (curso.ciclo != null && curso.ciclo!.isNotEmpty)
                                            _InfoPill(icon: Icons.repeat, texto: 'Ciclo ${curso.ciclo}'),
                                          _InfoPill(icon: Icons.people_outline, texto: '${curso.alumnosCount} alumnos'),
                                          _InfoPill(icon: Icons.badge_outlined, texto: curso.area.toUpperCase()),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),

                            // Barras de progreso resumidas
                            for (final entrada in curso.progreso.entries)
                              Padding(
                                padding: const EdgeInsets.only(bottom: 6),
                                child: _BarraProgresoSenior(
                                  etiqueta: _etiquetas[entrada.key] ?? entrada.key,
                                  valor: entrada.value,
                                ),
                              ),

                            const SizedBox(height: 10),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                // Badge Sílabo
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: curso.silaboListo
                                        ? (isDark ? const Color(0xFF064E3B).withValues(alpha: 0.35) : const Color(0xFF10B981).withValues(alpha: 0.12))
                                        : (isDark ? const Color(0xFF78350F).withValues(alpha: 0.35) : const Color(0xFFF59E0B).withValues(alpha: 0.12)),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    curso.silaboListo ? 'Sílabo OK' : 'Sílabo pendiente',
                                    style: TextStyle(
                                      fontSize: 10.5,
                                      fontWeight: FontWeight.w700,
                                      color: curso.silaboListo
                                          ? (isDark ? const Color(0xFF34D399) : const Color(0xFF0B7A43))
                                          : (isDark ? const Color(0xFFFBBF24) : const Color(0xFFB45309)),
                                    ),
                                  ),
                                ),

                                // Botón Calificar
                                PukllaScaleTap(
                                  onTap: () => onSeleccionarCurso(curso),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(
                                        colors: [AppColors.gold, Color(0xFFD97706)],
                                      ),
                                      borderRadius: BorderRadius.circular(8),
                                      boxShadow: [
                                        BoxShadow(
                                          color: AppColors.gold.withValues(alpha: 0.28),
                                          blurRadius: 6,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                                    ),
                                    child: const Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(Icons.edit_note_rounded, size: 16, color: Colors.white),
                                        SizedBox(width: 6),
                                        Text(
                                          'Calificar este curso',
                                          style: TextStyle(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w800,
                                            color: Colors.white,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

/// Hoja Modal para calificar y revisar evaluaciones de un curso específico
class _ModalCalificarCurso extends ConsumerStatefulWidget {
  const _ModalCalificarCurso({required this.curso});

  final DocenteCurso curso;

  @override
  ConsumerState<_ModalCalificarCurso> createState() => _ModalCalificarCursoState();
}

class _ModalCalificarCursoState extends ConsumerState<_ModalCalificarCurso> {
  final TextEditingController _busquedaController = TextEditingController();
  String _filtroTexto = '';

  @override
  void dispose() {
    _busquedaController.dispose();
    super.dispose();
  }

  Future<void> _abrirRubricaWeb() async {
    final uri = Uri.parse('http://127.0.0.1:8001/docentes');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _contactarPorEmail(String email) async {
    final uri = Uri.parse('mailto:$email');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> _contactarPorTelefono(String telefono) async {
    final uri = Uri.parse('tel:$telefono');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  static const _etiquetas = {
    'parcial_1': 'Parcial 1',
    'parcial_2': 'Parcial 2',
    'desempeno': 'Desempeño',
    'calificado': 'Avance General',
  };

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final curso = widget.curso;
    final alumnosAsync = ref.watch(docenteAlumnosProvider);

    return Container(
      height: MediaQuery.of(context).size.height * 0.88,
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.2),
            blurRadius: 25,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Barra de arrastre
          Center(
            child: Container(
              width: 44,
              height: 4.5,
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkBorder : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Cabecera con datos del curso
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(9),
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: isDark ? 0.22 : 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.edit_note_rounded, color: AppColors.gold, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      curso.nombre,
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: [
                        if (curso.programa != null)
                          _InfoPill(icon: Icons.school_outlined, texto: curso.programa!),
                        if (curso.ciclo != null)
                          _InfoPill(icon: Icons.repeat, texto: 'Ciclo ${curso.ciclo}'),
                        _InfoPill(icon: Icons.people_outline, texto: '${curso.alumnosCount} alumnos'),
                        _InfoPill(icon: Icons.badge_outlined, texto: curso.area.toUpperCase()),
                      ],
                    ),
                  ],
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: Icon(Icons.close, size: 20, color: isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Tarjeta de Registro Oficial Web (Espejo de competencias.calificar en Laravel)
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppColors.teal.withValues(alpha: isDark ? 0.18 : 0.08),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: AppColors.teal.withValues(alpha: isDark ? 0.40 : 0.25),
              ),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.sync_rounded, color: AppColors.teal, size: 20),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Registro de Rúbricas y Competencias',
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w700,
                          color: isDark ? AppColors.darkText : AppColors.tealDark,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Ingresa las notas oficiales y firmas ministeriales en el módulo web.',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                PukllaScaleTap(
                  onTap: _abrirRubricaWeb,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppColors.teal,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.open_in_browser_rounded, size: 15, color: Colors.white),
                        SizedBox(width: 4),
                        Text(
                          'Abrir Web',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w800,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // Avance de evaluaciones del curso
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Avance de Calificaciones',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
                  ),
                ),
                const SizedBox(height: 8),
                for (final entrada in curso.progreso.entries)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 6),
                    child: _BarraProgresoSenior(
                      etiqueta: _etiquetas[entrada.key] ?? entrada.key,
                      valor: entrada.value,
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // Buscador de alumnos en este curso
          Container(
            decoration: BoxDecoration(
              color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
            ),
            child: TextField(
              controller: _busquedaController,
              onChanged: (val) => setState(() => _filtroTexto = val.trim().toLowerCase()),
              style: TextStyle(
                fontSize: 13,
                color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
              ),
              decoration: InputDecoration(
                hintText: 'Buscar alumno en este curso...',
                hintStyle: TextStyle(
                  fontSize: 12,
                  color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8),
                ),
                prefixIcon: const Icon(Icons.search, size: 18, color: AppColors.gold),
                suffixIcon: _filtroTexto.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 16),
                        onPressed: () {
                          _busquedaController.clear();
                          setState(() => _filtroTexto = '');
                        },
                      )
                    : null,
                border: InputBorder.none,
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Listado de estudiantes del curso
          Expanded(
            child: alumnosAsync.when(
              loading: () => const Center(
                child: PukllaPreloader.inline(mensaje: 'Cargando alumnos del curso...'),
              ),
              error: (err, _) => Center(
                child: ErrorState(
                  message: 'No se pudo cargar la lista de alumnos: $err',
                  onRetry: () => ref.refresh(docenteAlumnosProvider),
                ),
              ),
              data: (cursosAlumnos) {
                // Filtrar el curso específico por id o nombre
                DocenteCursoAlumnos? cursoAlumnos;
                for (final ca in cursosAlumnos) {
                  if (ca.cursoId == curso.id ||
                      ca.cursoNombre.trim().toLowerCase() == curso.nombre.trim().toLowerCase()) {
                    cursoAlumnos = ca;
                    break;
                  }
                }

                final alumnos = cursoAlumnos?.alumnos ?? [];
                final alumnosFiltrados = alumnos.where((a) {
                  if (_filtroTexto.isEmpty) return true;
                  final nombre = a.nombreCompleto.toLowerCase();
                  final dni = a.dni?.toLowerCase() ?? '';
                  final email = a.email?.toLowerCase() ?? '';
                  return nombre.contains(_filtroTexto) || dni.contains(_filtroTexto) || email.contains(_filtroTexto);
                }).toList();

                if (alumnosFiltrados.isEmpty) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.person_search_outlined, size: 40, color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8)),
                          const SizedBox(height: 10),
                          Text(
                            _filtroTexto.isEmpty
                                ? 'No se registran alumnos matriculados en este curso.'
                                : 'No se encontraron alumnos para "$_filtroTexto".',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 12.5,
                              color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                return ListView.separated(
                  itemCount: alumnosFiltrados.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (context, idx) {
                    final a = alumnosFiltrados[idx];
                    return Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                      decoration: BoxDecoration(
                        color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isDark ? AppColors.darkBorder.withValues(alpha: 0.5) : const Color(0xFFF1F5F9),
                        ),
                      ),
                      child: Row(
                        children: [
                          CircleAvatar(
                            radius: 16,
                            backgroundColor: AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.15),
                            child: Text(
                              a.nombreCompleto.isNotEmpty ? a.nombreCompleto[0].toUpperCase() : '?',
                              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.teal),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  a.nombreCompleto,
                                  style: TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                    color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  [
                                    if (a.dni != null && a.dni!.isNotEmpty) 'DNI: ${a.dni}',
                                    if (a.email != null && a.email!.isNotEmpty) a.email,
                                  ].join(' · '),
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                                  ),
                                ),
                              ],
                            ),
                          ),

                          // Contacto rápido
                          if (a.telefono != null && a.telefono!.isNotEmpty)
                            IconButton(
                              icon: const Icon(Icons.phone_outlined, size: 17, color: Color(0xFF10B981)),
                              tooltip: 'Llamar a ${a.telefono}',
                              onPressed: () => _contactarPorTelefono(a.telefono!),
                            ),
                          if (a.email != null && a.email!.isNotEmpty)
                            IconButton(
                              icon: const Icon(Icons.email_outlined, size: 17, color: AppColors.teal),
                              tooltip: 'Enviar correo',
                              onPressed: () => _contactarPorEmail(a.email!),
                            ),
                        ],
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
