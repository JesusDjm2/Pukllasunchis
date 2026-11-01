import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../core/widgets/stat_card.dart';
import '../../../routing/app_drawer.dart';
import '../../auth/application/auth_controller.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';
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

  @override
  Widget build(BuildContext context) {
    final resumenAsync = ref.watch(docenteResumenProvider);
    final cursosAsync = ref.watch(docenteCursosProvider);
    final nombre = ref.watch(authControllerProvider).user?.name ?? 'Docente';

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
                              ),
                            ),
                        ],
                      );
                    },
                    loading: () => const Padding(
                      padding: EdgeInsets.symmetric(vertical: 24),
                      child: Center(child: CircularProgressIndicator()),
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
    final iniciales = nombre.trim().isEmpty
        ? 'D'
        : nombre.trim().split(RegExp(r'\s+')).take(2).map((p) => p[0].toUpperCase()).join();

    final periodoTexto = resumen?.periodoActual != null
        ? 'Período académico ${resumen!.periodoActual}'
        : 'Período académico activo';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 26),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF1E293B), Color(0xFF33445A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: Color(0x240F172A),
            blurRadius: 18,
            offset: Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 26,
                backgroundColor: AppColors.gold.withValues(alpha: 0.22),
                child: Text(
                  iniciales,
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18),
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
                        letterSpacing: 0.2,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 3),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        periodoTexto,
                        style: const TextStyle(color: Color(0xFFE2E8F0), fontSize: 11.5, fontWeight: FontWeight.w600),
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
                _RolPill(label: 'Docente', color: const Color(0xFF38BDF8)),
                if (resumen!.esTutor)
                  _RolPill(label: 'Tutor(a)', color: const Color(0xFF34D399), icon: Icons.support_agent),
                if (resumen!.esInvestigador)
                  _RolPill(label: "Investigador Taq'e", color: const Color(0xFFA78BFA), icon: Icons.science_outlined),
                if (resumen!.esTitulacion)
                  _RolPill(label: 'Titulación', color: const Color(0xFFFBBF24), icon: Icons.school_outlined),
                if (resumen!.esBolsa)
                  _RolPill(label: 'Bolsa de trabajo', color: const Color(0xFF60A5FA), icon: Icons.work_outline),
                if (resumen!.esAdmin)
                  _RolPill(label: 'Administrador', color: const Color(0xFFF87171), icon: Icons.security),
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
        color: color.withValues(alpha: 0.16),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: color.withValues(alpha: 0.35)),
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
  });

  final DocenteResumen resumen;
  final VoidCallback onVerAlumnos;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(Icons.stars_rounded, size: 20, color: AppColors.gold),
            const SizedBox(width: 8),
            const Text(
              'Mis funciones institucionales',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: AppColors.tealDark,
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
            onTap: () => context.go('/tutoria'),
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
            subtitulo: 'Ferias de investigación, asesoría metodológica y revisión de entregables.',
            chips: [
              if (resumen.investigadorData != null && resumen.investigadorData!.solicitudesPendientes > 0)
                _BadgeInfo(
                  texto: '${resumen.investigadorData!.solicitudesPendientes} ayudas pend.',
                  isAlert: true,
                ),
              if (resumen.proyectosACargo.isNotEmpty)
                _BadgeInfo(
                  texto: '${resumen.proyectosACargo.length} proyectos asignados',
                  isAlert: false,
                ),
            ],
            onTap: () => context.go('/taqe'),
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
            onTap: () {},
          ),
          const SizedBox(height: 10),
        ],

        // 4. Directorio de Mis Alumnos
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
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(14),
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
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: iconoBg,
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
                    style: const TextStyle(
                      fontSize: 14.5,
                      fontWeight: FontWeight.w700,
                      color: AppColors.tealDark,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitulo,
                    style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), height: 1.3),
                  ),
                  if (chips.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Wrap(spacing: 6, runSpacing: 4, children: chips),
                  ],
                ],
              ),
            ),
            const Icon(Icons.chevron_right, color: Color(0xFF94A3B8), size: 20),
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
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
      decoration: BoxDecoration(
        color: isAlert ? const Color(0xFFFEF2F2) : const Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: isAlert ? const Color(0xFFFECACA) : const Color(0xFFCBD5E1)),
      ),
      child: Text(
        texto,
        style: TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
          color: isAlert ? const Color(0xFFDC2626) : const Color(0xFF475569),
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
    final fidCount = cursos.where((c) => !c.esPpd).length;
    final ppdCount = cursos.where((c) => c.esPpd).length;

    return Row(
      children: [
        const Icon(Icons.book_outlined, color: AppColors.teal, size: 20),
        const SizedBox(width: 8),
        const Text(
          'Mis cursos',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w800,
            color: AppColors.tealDark,
          ),
        ),
        const Spacer(),
        // Selector tipo píldora FID / PPD
        Container(
          padding: const EdgeInsets.all(3),
          decoration: BoxDecoration(
            color: const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(999),
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
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(999),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 160),
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.teal : Colors.transparent,
          borderRadius: BorderRadius.circular(999),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected ? Colors.white : const Color(0xFF64748B),
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
  });

  final DocenteCurso curso;
  final VoidCallback onVerAlumnos;

  static const _etiquetas = {
    'parcial_1': 'Parcial 1',
    'parcial_2': 'Parcial 2',
    'desempeno': 'Desempeño',
    'calificado': 'Calificado',
  };

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.05),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
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
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: AppColors.tealDark),
                    ),
                    if (curso.ciclo != null || curso.programa != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 3),
                        child: Text(
                          [curso.ciclo, curso.programa].whereType<String>().join(' · '),
                          style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: curso.esPpd ? const Color(0xFFF3E8FF) : const Color(0xFFE0F2FE),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  curso.esPpd ? 'PPD' : 'FID',
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w800,
                    color: curso.esPpd ? const Color(0xFF7E22CE) : const Color(0xFF0369A1),
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
              InkWell(
                onTap: onVerAlumnos,
                borderRadius: BorderRadius.circular(8),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.teal.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
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
                      const Icon(Icons.chevron_right, size: 14, color: AppColors.teal),
                    ],
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
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

          const SizedBox(height: 6),

          // Badge de Sílabo
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
            decoration: BoxDecoration(
              color: (curso.silaboListo ? const Color(0xFF10B981) : const Color(0xFFF59E0B)).withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  curso.silaboListo ? Icons.check_circle_outline : Icons.error_outline,
                  size: 14,
                  color: curso.silaboListo ? const Color(0xFF0B7A43) : const Color(0xFFB45309),
                ),
                const SizedBox(width: 5),
                Text(
                  curso.silaboListo ? 'Sílabo registrado' : 'Sílabo pendiente',
                  style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                    color: curso.silaboListo ? const Color(0xFF0B7A43) : const Color(0xFFB45309),
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

class _InfoPill extends StatelessWidget {
  const _InfoPill({required this.icon, required this.texto});

  final IconData icon;
  final String texto;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: const Color(0xFF64748B)),
          const SizedBox(width: 4),
          Text(
            texto,
            style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w600, color: Color(0xFF475569)),
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
    return Row(
      children: [
        SizedBox(
          width: 82,
          child: Text(
            etiqueta,
            style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
          ),
        ),
        Expanded(
          child: ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: (valor / 100).clamp(0, 1),
              minHeight: 7,
              backgroundColor: const Color(0xFFF1F5F9),
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
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF334155)),
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
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 1.5,
      children: List.generate(
        4,
        (_) => Card(
          child: Container(margin: const EdgeInsets.all(16), color: AppColors.background),
        ),
      ),
    );
  }
}

/// Hoja Modal para explorar los Alumnos de los cursos del Docente
class _ModalAlumnosDocente extends ConsumerWidget {
  const _ModalAlumnosDocente({this.cursoIdFiltro});

  final int? cursoIdFiltro;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final alumnosAsync = ref.watch(docenteAlumnosProvider);

    return Container(
      height: MediaQuery.of(context).size.height * 0.82,
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 44,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.teal.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.people_alt, color: AppColors.teal, size: 22),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  'Mis Alumnos Matriculados',
                  style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800, color: AppColors.tealDark),
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.close, size: 20),
              ),
            ],
          ),
          const SizedBox(height: 12),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 8),

          Expanded(
            child: alumnosAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (err, _) => Center(child: Text('Error al cargar alumnos: $err')),
              data: (cursos) {
                final filtrados = cursoIdFiltro != null
                    ? cursos.where((c) => c.cursoId == cursoIdFiltro).toList()
                    : cursos;

                if (filtrados.isEmpty || filtrados.every((c) => c.alumnos.isEmpty)) {
                  return const Center(
                    child: Text('No hay alumnos registrados en este curso.', style: TextStyle(color: Color(0xFF64748B))),
                  );
                }

                return ListView.builder(
                  itemCount: filtrados.length,
                  itemBuilder: (context, idx) {
                    final c = filtrados[idx];
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          margin: const EdgeInsets.symmetric(vertical: 8),
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Row(
                            children: [
                              Text(
                                c.cursoNombre,
                                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: AppColors.tealDark),
                              ),
                              const Spacer(),
                              Text(
                                '${c.total} ${c.total == 1 ? "alumno" : "alumnos"}',
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF64748B)),
                              ),
                            ],
                          ),
                        ),
                        ...c.alumnos.map(
                          (a) => ListTile(
                            dense: true,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 6),
                            leading: CircleAvatar(
                              radius: 14,
                              backgroundColor: AppColors.teal.withValues(alpha: 0.15),
                              child: Text(
                                a.nombreCompleto.isNotEmpty ? a.nombreCompleto[0].toUpperCase() : '?',
                                style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.teal),
                              ),
                            ),
                            title: Text(a.nombreCompleto, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                            subtitle: Text(
                              [a.email, a.telefono].whereType<String>().where((s) => s.isNotEmpty).join(' · '),
                              style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                            ),
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
