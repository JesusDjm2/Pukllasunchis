import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/application/auth_controller.dart';
import '../theme/app_colors.dart';
import '../theme/puklla_dashboard_styles.dart';

/// Barra inferior fija para navegación táctil con el pulgar (Thumb Zone).
/// Ofrece los 5 accesos directos requeridos por la institución:
/// 1. Inicio (/home o /docente)
/// 2. Calificaciones (/calificaciones)
/// 3. Tutoría (/tutoria)
/// 4. Bolsa de trabajo (/bolsa-trabajo)
/// 5. Comunicados (/comunicados)
class PukllaBottomNavBar extends ConsumerWidget {
  const PukllaBottomNavBar({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isDark = PukllaDashboardStyles.isDark(context);
    String location = '';
    try {
      location = GoRouterState.of(context).matchedLocation;
    } catch (_) {
      location = ModalRoute.of(context)?.settings.name ?? '';
    }

    final user = ref.watch(authControllerProvider).user;
    final esDocenteOTutor = user != null && user.isDocenteOTutor && !user.isAlumno;
    final homeRoute = esDocenteOTutor ? '/docente' : '/home';

    int? activeIndex;
    if (location == '/home' || location == '/docente') {
      activeIndex = 0;
    } else if (location.startsWith('/calificaciones')) {
      activeIndex = 1;
    } else if (location.startsWith('/tutoria')) {
      activeIndex = 2;
    } else if (location.startsWith('/bolsa-trabajo')) {
      activeIndex = 3;
    } else if (location.startsWith('/comunicados')) {
      activeIndex = 4;
    }

    void navigateTo(String targetRoute) {
      if (location == targetRoute) return;
      try {
        final router = GoRouter.maybeOf(context);
        if (router != null) {
          context.go(targetRoute);
        } else {
          Navigator.of(context).pushNamed(targetRoute);
        }
      } catch (_) {}
    }

    return Container(
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        border: Border(
          top: BorderSide(
            color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
            width: 1,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: isDark
                ? Colors.black.withValues(alpha: 0.35)
                : const Color(0xFF0F172A).withValues(alpha: 0.06),
            blurRadius: 12,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 62,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _NavItem(
                index: 0,
                label: 'Inicio',
                icon: Icons.home_outlined,
                selectedIcon: Icons.home_rounded,
                isSelected: activeIndex == 0,
                isDark: isDark,
                onTap: () => navigateTo(homeRoute),
              ),
              _NavItem(
                index: 1,
                label: 'Notas',
                icon: Icons.grade_outlined,
                selectedIcon: Icons.grade_rounded,
                isSelected: activeIndex == 1,
                isDark: isDark,
                onTap: () => navigateTo('/calificaciones'),
              ),
              _NavItem(
                index: 2,
                label: 'Tutoría',
                icon: Icons.support_agent_outlined,
                selectedIcon: Icons.support_agent_rounded,
                isSelected: activeIndex == 2,
                isDark: isDark,
                onTap: () => navigateTo('/tutoria'),
              ),
              _NavItem(
                index: 3,
                label: 'Bolsa',
                icon: Icons.work_outline_rounded,
                selectedIcon: Icons.work_rounded,
                isSelected: activeIndex == 3,
                isDark: isDark,
                onTap: () => navigateTo('/bolsa-trabajo'),
              ),
              _NavItem(
                index: 4,
                label: 'Comunicados',
                icon: Icons.campaign_outlined,
                selectedIcon: Icons.campaign_rounded,
                isSelected: activeIndex == 4,
                isDark: isDark,
                onTap: () => navigateTo('/comunicados'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  const _NavItem({
    required this.index,
    required this.label,
    required this.icon,
    required this.selectedIcon,
    required this.isSelected,
    required this.onTap,
    this.isDark = false,
  });

  final int index;
  final String label;
  final IconData icon;
  final IconData selectedIcon;
  final bool isSelected;
  final bool isDark;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final activeColor = isDark ? const Color(0xFFF1B768) : AppColors.goldHover;
    final unselectedColor = isDark ? AppColors.darkMuted : const Color(0xFF64748B);
    final activeTextColor = isDark ? const Color(0xFFF1B768) : AppColors.tealDark;

    return Expanded(
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () {
            HapticFeedback.selectionClick();
            onTap();
          },
          splashColor: AppColors.gold.withValues(alpha: 0.12),
          highlightColor: Colors.transparent,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 220),
                curve: Curves.easeOutCubic,
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(
                  color: isSelected
                      ? AppColors.gold.withValues(alpha: isDark ? 0.22 : 0.14)
                      : Colors.transparent,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: AnimatedScale(
                  scale: isSelected ? 1.06 : 1.0,
                  duration: const Duration(milliseconds: 220),
                  curve: Curves.easeOutCubic,
                  child: Icon(
                    isSelected ? selectedIcon : icon,
                    size: 22,
                    color: isSelected ? activeColor : unselectedColor,
                  ),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 10.5,
                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                  color: isSelected ? activeTextColor : unselectedColor,
                  letterSpacing: -0.1,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
