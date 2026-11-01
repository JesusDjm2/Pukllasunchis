import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app_colors.dart';
import 'theme_mode_provider.dart';

/// Hoja de estilos centralizada y reutilizable para dashboards de Pukllasunchis
/// (Estudiantes, Docentes, Tutores, Coordinadores).
/// Centraliza decoraciones, contrastes, sombras y componentes modulares
/// adaptados dinámicamente tanto para Modo Claro como para Modo Oscuro.
class PukllaDashboardStyles {
  const PukllaDashboardStyles._();

  /// Comprueba si el contexto actual está renderizando en Modo Oscuro
  static bool isDark(BuildContext context) {
    return Theme.of(context).brightness == Brightness.dark;
  }

  /// Color de superficie dinámico para tarjetas
  static Color surfaceColor(BuildContext context) {
    return isDark(context) ? AppColors.darkSurface : Colors.white;
  }

  /// Alias de surfaceColor
  static Color cardColor(BuildContext context) => surfaceColor(context);

  /// Color de fondo de pantalla dinámico
  static Color backgroundColor(BuildContext context) {
    return isDark(context) ? AppColors.darkBackground : const Color(0xFFF8FAFC);
  }

  /// Color de borde sutil para tarjetas y divisores
  static Color borderColor(BuildContext context) {
    return isDark(context) ? AppColors.darkBorder : const Color(0xFFE2E8F0);
  }

  /// Color primario de texto
  static Color textPrimary(BuildContext context) {
    return isDark(context) ? AppColors.darkText : const Color(0xFF0F172A);
  }

  /// Color secundario / atenuado de texto
  static Color textSecondary(BuildContext context) {
    return isDark(context) ? AppColors.darkMuted : const Color(0xFF64748B);
  }

  /// Decoración unificada de tarjeta institucional para cualquier dashboard
  static BoxDecoration cardDecoration(
    BuildContext context, {
    Color? customBorder,
    double radius = 16,
    Color? customBg,
  }) {
    final dark = isDark(context);
    return BoxDecoration(
      color: customBg ?? (dark ? AppColors.darkSurface : Colors.white),
      borderRadius: BorderRadius.circular(radius),
      border: Border.all(
        color: customBorder ?? (dark ? AppColors.darkBorder : const Color(0xFFE2E8F0)),
        width: 1,
      ),
      boxShadow: [
        BoxShadow(
          color: dark
              ? Colors.black.withValues(alpha: 0.25)
              : const Color(0xFF0F172A).withValues(alpha: 0.04),
          blurRadius: 10,
          offset: const Offset(0, 3),
        ),
      ],
    );
  }

  /// Gradiente institucional de la cabecera Hero (Docente o Alumno)
  static BoxDecoration heroHeaderDecoration(BuildContext context) {
    final dark = isDark(context);
    return BoxDecoration(
      gradient: LinearGradient(
        colors: dark
            ? const [Color(0xFF0D1B2A), Color(0xFF1B2A4A)]
            : const [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      ),
      borderRadius: const BorderRadius.vertical(bottom: Radius.circular(28)),
      boxShadow: [
        BoxShadow(
          color: dark ? Colors.black54 : const Color(0x221B2A4A),
          blurRadius: 20,
          offset: const Offset(0, 10),
        ),
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// COMPONENTES MODULARES REUTILIZABLES PARA DASHBOARDS
// ─────────────────────────────────────────────────────────────────────────────

/// Encabezado estándar de sección en dashboards ("Cursos", "Avisos", etc.)
class PukllaSectionHeader extends StatelessWidget {
  const PukllaSectionHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.actionText,
    this.onAction,
  });

  final String title;
  final String? subtitle;
  final String? actionText;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Row(
      children: [
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: TextStyle(
                fontSize: 16.5,
                fontWeight: FontWeight.w800,
                color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                letterSpacing: -0.2,
              ),
            ),
            if (subtitle != null) ...[
              const SizedBox(height: 2),
              Text(
                subtitle!,
                style: TextStyle(
                  fontSize: 12,
                  color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                ),
              ),
            ],
          ],
        ),
        const Spacer(),
        if (actionText != null && onAction != null)
          TextButton(
            onPressed: onAction,
            style: TextButton.styleFrom(
              padding: EdgeInsets.zero,
              minimumSize: Size.zero,
              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
            ),
            child: Row(
              children: [
                Text(
                  actionText!,
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    fontSize: 13,
                    color: AppColors.goldHover,
                  ),
                ),
                const SizedBox(width: 2),
                const Icon(Icons.chevron_right_rounded, size: 16, color: AppColors.goldHover),
              ],
            ),
          ),
      ],
    );
  }
}

/// Tarjeta de métrica estadística modular para dashboards (ej. Cursos, Promedios)
class PukllaStatCard extends StatelessWidget {
  const PukllaStatCard({
    super.key,
    required this.icon,
    required this.titulo,
    required this.valor,
    required this.color,
  });

  final IconData icon;
  final String titulo;
  final String valor;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 12),
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 4),
          Text(
            valor,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w800,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            titulo,
            style: TextStyle(
              fontSize: 10.5,
              fontWeight: FontWeight.w600,
              color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

/// Item modular de la cuadrícula Bento para accesos directos
class PukllaBentoItem extends StatelessWidget {
  const PukllaBentoItem({
    super.key,
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

    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(14),
          decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
          child: Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: color.withValues(alpha: isDark ? 0.20 : 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: color, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 4),
              Icon(
                Icons.chevron_right_rounded,
                size: 18,
                color: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Botón interactivo para alternar Tema Claro / Modo Oscuro con rotación y morphing
class PukllaThemeToggleButton extends ConsumerWidget {
  const PukllaThemeToggleButton({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final themeMode = ref.watch(themeModeProvider);
    final isDark = themeMode == ThemeMode.dark;

    return IconButton(
      tooltip: isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro',
      icon: AnimatedSwitcher(
        duration: const Duration(milliseconds: 320),
        transitionBuilder: (child, animation) {
          return RotationTransition(
            turns: animation,
            child: ScaleTransition(scale: animation, child: child),
          );
        },
        child: Icon(
          isDark ? Icons.light_mode_rounded : Icons.dark_mode_outlined,
          key: ValueKey<bool>(isDark),
          color: isDark ? const Color(0xFFFDE68A) : Colors.white,
          size: 21,
        ),
      ),
      onPressed: () {
        HapticFeedback.lightImpact();
        ref.read(themeModeProvider.notifier).toggleTheme();
      },
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// ANIMACIONES Y MICRO-INTERACCIONES ERGONÓMICAS (UX / UI BEST PRACTICES)
// ─────────────────────────────────────────────────────────────────────────────

/// Envoltorio interactivo con micro-interacción táctil de pulsación suave (Scale & Haptics)
class PukllaScaleTap extends StatefulWidget {
  const PukllaScaleTap({
    super.key,
    required this.child,
    this.onTap,
    this.scaleFactor = 0.975,
    this.enableHaptics = true,
  });

  final Widget child;
  final VoidCallback? onTap;
  final double scaleFactor;
  final bool enableHaptics;

  @override
  State<PukllaScaleTap> createState() => _PukllaScaleTapState();
}

class _PukllaScaleTapState extends State<PukllaScaleTap> with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 90),
      reverseDuration: const Duration(milliseconds: 140),
    );
    _scaleAnimation = Tween<double>(begin: 1.0, end: widget.scaleFactor).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeOutCubic, reverseCurve: Curves.easeOutBack),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onTapDown(TapDownDetails _) {
    if (widget.onTap != null) {
      if (widget.enableHaptics) {
        HapticFeedback.lightImpact();
      }
      _controller.forward();
    }
  }

  void _onTapUp(TapUpDetails _) {
    _controller.reverse();
  }

  void _onTapCancel() {
    _controller.reverse();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.onTap == null) return widget.child;

    return GestureDetector(
      onTapDown: _onTapDown,
      onTapUp: _onTapUp,
      onTapCancel: _onTapCancel,
      onTap: widget.onTap,
      behavior: HitTestBehavior.opaque,
      child: AnimatedBuilder(
        animation: _scaleAnimation,
        builder: (context, child) => Transform.scale(
          scale: _scaleAnimation.value,
          child: child,
        ),
        child: widget.child,
      ),
    );
  }
}

/// Animación escalonada de entrada fluida (Fade-in + Slide-up) para dashboards
class PukllaFadeInSlide extends StatelessWidget {
  const PukllaFadeInSlide({
    super.key,
    required this.child,
    this.delayMs = 0,
    this.durationMs = 400,
    this.slideOffsetY = 14.0,
  });

  final Widget child;
  final int delayMs;
  final int durationMs;
  final double slideOffsetY;

  @override
  Widget build(BuildContext context) {
    return TweenAnimationBuilder<double>(
      tween: Tween<double>(begin: 0.0, end: 1.0),
      duration: Duration(milliseconds: durationMs + delayMs),
      curve: Curves.easeOutCubic,
      builder: (context, value, childWidget) {
        final totalTime = durationMs + delayMs;
        final elapsed = value * totalTime;
        final progress = (delayMs == 0 || elapsed > delayMs)
            ? ((elapsed - delayMs) / durationMs).clamp(0.0, 1.0)
            : 0.0;

        return Opacity(
          opacity: progress,
          child: Transform.translate(
            offset: Offset(0, (1.0 - progress) * slideOffsetY),
            child: childWidget,
          ),
        );
      },
      child: child,
    );
  }
}

/// Placeholder esquelético con pulso suave para evitar Cumulative Layout Shift (CLS)
class PukllaSkeletonCard extends StatefulWidget {
  const PukllaSkeletonCard({
    super.key,
    this.height = 70,
    this.radius = 16,
    this.margin = const EdgeInsets.only(bottom: 10),
  });

  final double height;
  final double radius;
  final EdgeInsetsGeometry margin;

  @override
  State<PukllaSkeletonCard> createState() => _PukllaSkeletonCardState();
}

class _PukllaSkeletonCardState extends State<PukllaSkeletonCard> with SingleTickerProviderStateMixin {
  late final AnimationController _animController;
  late final Animation<double> _pulse;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);
    _pulse = Tween<double>(begin: 0.35, end: 0.80).animate(
      CurvedAnimation(parent: _animController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final baseColor = isDark ? AppColors.darkSurfaceElevated : const Color(0xFFE2E8F0);

    return Container(
      height: widget.height,
      margin: widget.margin,
      child: AnimatedBuilder(
        animation: _pulse,
        builder: (context, _) => Container(
          decoration: BoxDecoration(
            color: baseColor.withValues(alpha: _pulse.value),
            borderRadius: BorderRadius.circular(widget.radius),
            border: Border.all(
              color: isDark ? AppColors.darkBorder : const Color(0xFFF1F5F9),
            ),
          ),
        ),
      ),
    );
  }
}
