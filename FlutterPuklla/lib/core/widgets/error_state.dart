import 'package:flutter/material.dart';

import '../theme/app_colors.dart';

/// Banda de error consistente con botón de reintento, para reemplazar los
/// mensajes de error ad-hoc repetidos en cada pantalla.
class ErrorState extends StatelessWidget {
  const ErrorState({super.key, required this.message, this.onRetry});

  final String message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.dangerBg,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          const Icon(Icons.wifi_off_rounded, color: AppColors.danger, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(message, style: const TextStyle(color: AppColors.danger, fontSize: 13)),
          ),
          if (onRetry != null)
            TextButton(
              onPressed: onRetry,
              style: TextButton.styleFrom(foregroundColor: AppColors.danger),
              child: const Text('Reintentar'),
            ),
        ],
      ),
    );
  }
}
