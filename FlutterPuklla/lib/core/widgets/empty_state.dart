import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

/// Estado vacío consistente (icono en círculo suave + mensaje), para
/// reemplazar los `Text` sueltos repetidos en cada pantalla.
class EmptyState extends StatelessWidget {
  const EmptyState({super.key, required this.icon, required this.message});

  final IconData icon;
  final String message;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(color: AppColors.background, shape: BoxShape.circle),
              child: Icon(icon, size: 32, color: AppColors.muted),
            ),
            const SizedBox(height: 16),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.muted, fontSize: 14, height: 1.4),
            ),
          ],
        ),
      ),
    );
  }
}
