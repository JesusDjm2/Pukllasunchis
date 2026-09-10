import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../core/theme/app_assets.dart';
import '../core/theme/app_colors.dart';
import '../features/auth/application/auth_controller.dart';

/// Menú lateral persistente, equivalente al sidebar de
/// `resources/views/layouts/alumno.blade.php` en la web.
class AppDrawer extends ConsumerWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final location = GoRouterState.of(context).matchedLocation;

    return Drawer(
      child: SafeArea(
        child: Column(
          children: [
            Container(
              width: double.infinity,
              color: AppColors.teal,
              padding: const EdgeInsets.symmetric(vertical: 28),
              child: Image.asset(AppAssets.logoBlanco, height: 56),
            ),
            const SizedBox(height: 8),
            _DrawerItem(
              icon: Icons.badge_outlined,
              label: 'Ficha técnica',
              route: '/home',
              currentLocation: location,
            ),
            _DrawerItem(
              icon: Icons.school_outlined,
              label: 'Cursos especiales',
              route: '/cursos-especiales',
              currentLocation: location,
            ),
            _DrawerItem(
              icon: Icons.grade_outlined,
              label: 'Calificaciones y matrícula',
              route: '/calificaciones',
              currentLocation: location,
            ),
            _DrawerItem(
              icon: Icons.campaign_outlined,
              label: 'Comunicados',
              route: '/comunicados',
              currentLocation: location,
            ),
            const Spacer(),
            const Divider(height: 1),
            ListTile(
              leading: const Icon(Icons.logout, color: AppColors.danger),
              title: const Text('Cerrar sesión', style: TextStyle(color: AppColors.danger)),
              onTap: () {
                Navigator.of(context).pop();
                ref.read(authControllerProvider.notifier).logout();
              },
            ),
            const SizedBox(height: 8),
          ],
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

    return ListTile(
      leading: Icon(icon, color: selected ? AppColors.gold : AppColors.teal),
      title: Text(
        label,
        style: TextStyle(fontWeight: selected ? FontWeight.w700 : FontWeight.normal),
      ),
      selected: selected,
      selectedTileColor: AppColors.background,
      onTap: () {
        Navigator.of(context).pop();
        if (!selected) context.go(route);
      },
    );
  }
}
