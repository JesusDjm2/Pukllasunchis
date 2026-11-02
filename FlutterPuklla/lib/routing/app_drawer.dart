import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../core/theme/app_assets.dart';
import '../core/theme/app_colors.dart';
import '../core/theme/puklla_dashboard_styles.dart';
import '../core/theme/theme_mode_provider.dart';
import '../core/widgets/puklla_avatar.dart';
import '../features/auth/application/auth_controller.dart';

class AppDrawer extends ConsumerWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final location = GoRouterState.of(context).matchedLocation;
    final user = ref.watch(authControllerProvider).user;
    final esDocenteOTutor = user != null && user.isDocenteOTutor;
    final tieneAmbosRoles = esDocenteOTutor && user.isAlumno;
    // Si es docente y no está navegando activamente en pantallas exclusivas de alumno, su modo activo es Docente
    final estaEnModoDocente = esDocenteOTutor &&
        !location.startsWith('/home') &&
        !location.startsWith('/ficha-tecnica') &&
        !location.startsWith('/calificaciones');
    final isDark = PukllaDashboardStyles.isDark(context);

    return Drawer(
      backgroundColor: isDark ? AppColors.darkSurface : const Color(0xFFF8FAFC),
      child: SafeArea(
        top: false,
        child: Column(
          children: [
            // ── HEADER PREMIUM CON IDENTIDAD INSTITUCIONAL ──
            Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(20, 48, 20, 20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: isDark
                      ? const [Color(0xFF0F172A), Color(0xFF1E293B)]
                      : const [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                border: Border(
                  bottom: BorderSide(
                    color: isDark ? AppColors.darkBorder : Colors.transparent,
                  ),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Image.asset(AppAssets.logoBlanco, height: 42),
                  const SizedBox(height: 18),
                  Row(
                    children: [
                      PukllaAvatar(
                        fotoUrl: user?.foto,
                        iniciales: (user?.name.isNotEmpty ?? false) ? user!.name[0].toUpperCase() : 'P',
                        size: 44,
                        borderWidth: 2,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              user != null ? '${user.name} ${user.apellidos ?? ''}' : 'Estudiante',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 14.5,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              user?.email ?? 'pukllavirtual.edu.pe',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                color: Colors.white.withValues(alpha: 0.75),
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // ── LISTA DE NAVEGACIÓN MODERNA Y COMPLETA ──
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                children: [
                  // ── Si el usuario tiene ambos roles (Docente y Alumno), selector de vista rápida ──
                  if (tieneAmbosRoles) ...[
                    _BotonConmutarRol(
                      label: estaEnModoDocente ? 'Cambiar a modo Estudiante' : 'Cambiar a modo Docente',
                      subtitulo: estaEnModoDocente ? 'Ver mi progreso, notas y cursos' : 'Gestionar mis cursos y alumnos',
                      icon: estaEnModoDocente ? Icons.school_outlined : Icons.badge_outlined,
                      destino: estaEnModoDocente ? '/home' : '/docente',
                    ),
                    const SizedBox(height: 12),
                  ],

                  if (estaEnModoDocente) ...[
                    const _SectionTitle(title: 'DOCENCIA'),
                    _DrawerItem(
                      icon: Icons.dashboard_customize_outlined,
                      label: 'Mi Panel · Cursos',
                      route: '/docente',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.people_alt_outlined,
                      label: 'Directorio de Alumnos',
                      route: '/docente/alumnos',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.assignment_outlined,
                      label: 'Mis Incidencias',
                      route: '/docente/incidencias',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.school_outlined,
                      label: 'Cursos Asincrónicos',
                      route: '/cursos-especiales',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.local_library_outlined,
                      label: 'Guías de TI & Biblioteca',
                      route: '/recursos',
                      currentLocation: location,
                    ),

                    const SizedBox(height: 14),
                    const _SectionTitle(title: 'FUNCIONES Y SERVICIOS'),
                    _DrawerItem(
                      icon: Icons.work_outline,
                      label: 'Bolsa de Trabajo',
                      route: '/bolsa-trabajo',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.campaign_outlined,
                      label: 'Comunicados Oficiales',
                      route: '/comunicados',
                      currentLocation: location,
                    ),
                  ] else ...[
                    const _SectionTitle(title: 'MI ESPACIO ACADÉMICO'),
                    _DrawerItem(
                      icon: Icons.dashboard_outlined,
                      label: 'Inicio · Dashboard',
                      route: '/home',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.badge_outlined,
                      label: 'Ficha Técnica',
                      route: '/ficha-tecnica',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.grade_outlined,
                      label: 'Calificaciones',
                      route: '/calificaciones',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.school_outlined,
                      label: 'Asincrónicos',
                      route: '/cursos-especiales',
                      currentLocation: location,
                    ),

                    const SizedBox(height: 14),
                    const _SectionTitle(title: 'INVESTIGACIÓN Y DESARROLLO'),
                    _DrawerItem(
                      icon: Icons.science_outlined,
                      label: "Taq'e · Proyectos",
                      route: '/taqe',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.local_library_outlined,
                      label: 'Guías de TI, Tesis & Biblioteca Virtual',
                      route: '/recursos',
                      currentLocation: location,
                    ),

                    const SizedBox(height: 14),
                    const _SectionTitle(title: 'VIDA ESTUDIANTIL & SERVICIOS'),
                    _DrawerItem(
                      icon: Icons.work_outline,
                      label: 'Bolsa de Trabajo',
                      route: '/bolsa-trabajo',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.favorite_outline,
                      label: 'Tutoría y Sugerencias',
                      route: '/tutoria',
                      currentLocation: location,
                    ),
                    _DrawerItem(
                      icon: Icons.campaign_outlined,
                      label: 'Comunicados Oficiales',
                      route: '/comunicados',
                      currentLocation: location,
                    ),
                  ],

                  const SizedBox(height: 14),
                  const _SectionTitle(title: 'SISTEMA'),
                  _DrawerItem(
                    icon: Icons.notifications_outlined,
                    label: 'Notificaciones',
                    route: '/notificaciones',
                    currentLocation: location,
                  ),
                ],
              ),
            ),

            // ── FOOTER: SELECTOR MODO OSCURO + CERRAR SESIÓN ──
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                border: Border(
                  top: BorderSide(
                    color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                  ),
                ),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Toggle interactivo de Tema Claro / Oscuro
                  InkWell(
                    borderRadius: BorderRadius.circular(12),
                    onTap: () => ref.read(themeModeProvider.notifier).toggleTheme(),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      child: Row(
                        children: [
                          Icon(
                            isDark ? Icons.light_mode_rounded : Icons.dark_mode_rounded,
                            color: isDark ? AppColors.goldHover : const Color(0xFF64748B),
                            size: 20,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              isDark ? 'Modo Claro' : 'Modo Oscuro',
                              style: TextStyle(
                                color: isDark ? const Color(0xFFE2E8F0) : const Color(0xFF475569),
                                fontWeight: FontWeight.w600,
                                fontSize: 13,
                              ),
                            ),
                          ),
                          Transform.scale(
                            scale: 0.8,
                            child: Switch.adaptive(
                              value: isDark,
                              activeTrackColor: AppColors.gold,
                              onChanged: (_) => ref.read(themeModeProvider.notifier).toggleTheme(),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),

                  // Cerrar sesión
                  ListTile(
                    dense: true,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 10),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    leading: const Icon(Icons.logout, color: AppColors.danger, size: 20),
                    title: const Text(
                      'Cerrar sesión',
                      style: TextStyle(
                        color: AppColors.danger,
                        fontWeight: FontWeight.w700,
                        fontSize: 13.5,
                      ),
                    ),
                    onTap: () {
                      Navigator.of(context).pop();
                      ref.read(authControllerProvider.notifier).logout();
                    },
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

class _SectionTitle extends StatelessWidget {
  const _SectionTitle({required this.title});
  final String title;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 6),
      child: Text(
        title,
        style: TextStyle(
          color: isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8),
          fontSize: 11,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.8,
        ),
      ),
    );
  }
}

class _DrawerItem extends StatelessWidget {
  const _DrawerItem({
    required this.icon,
    required this.label,
    required this.route,
    required this.currentLocation,
  });

  final IconData icon;
  final String label;
  final String route;
  final String currentLocation;

  @override
  Widget build(BuildContext context) {
    final selected = currentLocation == route;
    final isDark = PukllaDashboardStyles.isDark(context);

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: ListTile(
        dense: true,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        tileColor: selected
            ? (isDark ? AppColors.gold.withValues(alpha: 0.18) : AppColors.gold.withValues(alpha: 0.12))
            : Colors.transparent,
        leading: Icon(
          icon,
          color: selected
              ? AppColors.goldHover
              : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF475569)),
          size: 21,
        ),
        title: Text(
          label,
          style: TextStyle(
            fontWeight: selected ? FontWeight.w800 : FontWeight.w600,
            color: selected
                ? AppColors.goldHover
                : (isDark ? const Color(0xFFF1F5F9) : const Color(0xFF1E293B)),
            fontSize: 13.5,
          ),
        ),
        trailing: selected
            ? Container(
                width: 6,
                height: 6,
                decoration: const BoxDecoration(
                  shape: BoxShape.circle,
                  color: AppColors.gold,
                ),
              )
            : null,
        onTap: () {
          Navigator.of(context).pop();
          if (!selected) context.go(route);
        },
      ),
    );
  }
}

class _BotonConmutarRol extends StatelessWidget {
  const _BotonConmutarRol({
    required this.label,
    required this.subtitulo,
    required this.icon,
    required this.destino,
  });

  final String label;
  final String subtitulo;
  final IconData icon;
  final String destino;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return InkWell(
      onTap: () {
        Navigator.of(context).pop();
        context.go(destino);
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: AppColors.gold.withValues(alpha: isDark ? 0.15 : 0.10),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: AppColors.gold.withValues(alpha: isDark ? 0.35 : 0.25),
          ),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(7),
              decoration: BoxDecoration(
                color: AppColors.gold.withValues(alpha: isDark ? 0.25 : 0.15),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(icon, color: AppColors.gold, size: 18),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w800,
                      color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                    ),
                  ),
                  Text(
                    subtitulo,
                    style: TextStyle(
                      fontSize: 11,
                      color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                    ),
                  ),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios, size: 12, color: AppColors.gold),
          ],
        ),
      ),
    );
  }
}
