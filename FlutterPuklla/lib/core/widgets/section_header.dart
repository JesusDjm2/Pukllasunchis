import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

/// Encabezado de sección (icono + título + acción opcional a la derecha),
/// reutilizado en todas las pantallas de alumno y docente para que las listas
/// de contenido no empiecen "en seco".
class SectionHeader extends StatelessWidget {
  const SectionHeader({
    super.key,
    required this.icon,
    required this.title,
    this.trailing,
  });

  final IconData icon;
  final String title;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Container(
            width: 30,
            height: 30,
            decoration: BoxDecoration(
              color: AppColors.teal.withValues(alpha: 0.08),
              borderRadius: BorderRadius.circular(9),
            ),
            child: Icon(icon, size: 16, color: AppColors.teal),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              title,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.text),
            ),
          ),
          if (trailing != null) trailing!,
        ],
      ),
    );
  }
}
