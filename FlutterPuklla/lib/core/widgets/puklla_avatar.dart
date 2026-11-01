import 'package:flutter/material.dart';

import '../env/env.dart';
import '../theme/app_colors.dart';

/// Avatar institucional de Pukllasunchis:
/// - Si el estudiante/usuario tiene foto de perfil cargada, la muestra redondeada con su aro institucional.
/// - Si no tiene foto (o falla la carga), muestra sus iniciales con el estilo predeterminado.
class PukllaAvatar extends StatelessWidget {
  const PukllaAvatar({
    super.key,
    required this.fotoUrl,
    required this.iniciales,
    this.size = 54,
    this.borderWidth = 2.0,
    this.borderColor = AppColors.gold,
    this.backgroundColor,
    this.textStyle,
  });

  final String? fotoUrl;
  final String iniciales;
  final double size;
  final double borderWidth;
  final Color borderColor;
  final Color? backgroundColor;
  final TextStyle? textStyle;

  @override
  Widget build(BuildContext context) {
    String? resolvedUrl = fotoUrl?.trim();
    if (resolvedUrl != null && resolvedUrl.isNotEmpty) {
      if (!resolvedUrl.startsWith('http://') && !resolvedUrl.startsWith('https://')) {
        // En caso de que venga ruta relativa como /api/v1/... o /img/...
        final apiBase = Env.apiBaseUrl;
        final origin = apiBase.split('/api/v1').first;
        resolvedUrl = '$origin/${resolvedUrl.startsWith('/') ? resolvedUrl.substring(1) : resolvedUrl}';
      }
    }

    final tieneFoto = resolvedUrl != null && resolvedUrl.isNotEmpty;
    final bg = backgroundColor ?? Colors.white.withValues(alpha: 0.15);

    Widget fallbackWidget = Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: bg,
      ),
      alignment: Alignment.center,
      child: Text(
        iniciales,
        style: textStyle ??
            TextStyle(
              color: Colors.white,
              fontSize: size * 0.38,
              fontWeight: FontWeight.w800,
              letterSpacing: 1.0,
            ),
      ),
    );

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(color: borderColor, width: borderWidth),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.18),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipOval(
        child: tieneFoto
            ? Image.network(
                resolvedUrl,
                width: size,
                height: size,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) => fallbackWidget,
                loadingBuilder: (context, child, loadingProgress) {
                  if (loadingProgress == null) return child;
                  return fallbackWidget;
                },
              )
            : fallbackWidget,
      ),
    );
  }
}
