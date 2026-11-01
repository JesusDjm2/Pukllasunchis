import 'dart:math' as math;
import 'package:flutter/material.dart';

import '../theme/app_assets.dart';
import '../theme/app_colors.dart';
import '../theme/puklla_dashboard_styles.dart';

/// Preloader institucional idéntico al preloader web del panel de administración
/// (`public/admin/css/estilos.css` y `layouts/admin.blade.php`), adaptado fielmente
/// a Flutter con animaciones fluidas a 60fps:
/// - Fondo institucional en gradiente cálido oscuro (#181006 -> #2D1E0C -> #181006).
/// - Logo flotante continuo (`pl-float`) con aura dorada institucional (#CD9244).
/// - Tipografía oficial con tracking espaciado: "PUKLLASUNCHIS" + "SISTEMA DE GESTIÓN".
/// - Tres puntos dorados con rebote desfasado (`pl-dots` / `pl-bounce`).
/// - Barra inferior con barrido continuo de gradiente dorado (`pl-bar` / `pl-sweep`).
class PukllaPreloader extends StatefulWidget {
  const PukllaPreloader({
    super.key,
    this.mensaje,
    this.isFullScreen = true,
    this.useThemeBackground = false,
  });

  /// Constructor alternativo para estados de carga embebidos dentro de tarjetas o listas
  const PukllaPreloader.inline({
    super.key,
    this.mensaje,
  })  : isFullScreen = false,
        useThemeBackground = true;

  final String? mensaje;
  final bool isFullScreen;
  final bool useThemeBackground;

  @override
  State<PukllaPreloader> createState() => _PukllaPreloaderState();
}

class _PukllaPreloaderState extends State<PukllaPreloader>
    with TickerProviderStateMixin {
  late final AnimationController _floatController;
  late final AnimationController _dotsController;
  late final AnimationController _sweepController;

  @override
  void initState() {
    super.initState();

    // 1. Animación de levitación suave del logo (3.2s ida y vuelta)
    _floatController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 3200),
    )..repeat(reverse: true);

    // 2. Animación de rebote rítmico de los 3 puntos (1.5s en ciclo continuo)
    _dotsController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    )..repeat();

    // 3. Barrido de gradiente de la barra inferior (1.9s cíclico)
    _sweepController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1900),
    )..repeat();
  }

  @override
  void dispose() {
    _floatController.dispose();
    _dotsController.dispose();
    _sweepController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    // Fondo: Gradiente oficial del preloader web (#181006 -> #2d1e0c -> #181006)
    // o adaptable a la superficie en caso de modo inline.
    final BoxDecoration backgroundDecoration = widget.isFullScreen
        ? const BoxDecoration(
            gradient: LinearGradient(
              colors: [
                Color(0xFF181006),
                Color(0xFF2D1E0C),
                Color(0xFF181006),
              ],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          )
        : BoxDecoration(
            color: widget.useThemeBackground
                ? (isDark ? AppColors.darkSurface : Colors.white)
                : Colors.transparent,
            borderRadius: BorderRadius.circular(18),
          );

    final double logoSize = widget.isFullScreen ? 82.0 : 44.0;
    final double nameFontSize = widget.isFullScreen ? 12.5 : 10.5;
    final double taglineFontSize = widget.isFullScreen ? 9.0 : 8.0;

    final content = Center(
      child: FittedBox(
        fit: BoxFit.scaleDown,
        child: Padding(
          padding: EdgeInsets.symmetric(
            horizontal: widget.isFullScreen ? 24 : 12,
            vertical: widget.isFullScreen ? 20 : 8,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // ── LOGO INSTITUCIONAL FLOTANTE CON AURA DORADA ──
              AnimatedBuilder(
                animation: _floatController,
                builder: (context, child) {
                  final floatOffset = math.sin(_floatController.value * math.pi) * (widget.isFullScreen ? -7.5 : -4.0);
                  return Transform.translate(
                    offset: Offset(0, floatOffset),
                    child: child,
                  );
                },
                child: Container(
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFFCD9244).withValues(alpha: 0.45),
                        blurRadius: widget.isFullScreen ? 26 : 14,
                        spreadRadius: 1.5,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Image.asset(
                    AppAssets.logoBlanco,
                    width: logoSize,
                    height: logoSize,
                    fit: BoxFit.contain,
                  ),
                ),
              ),
              SizedBox(height: widget.isFullScreen ? 20 : 10),

              // ── NOMBRE INSTITUCIONAL CON TRACKING EXPANDIDO ──
              Text(
                'PUKLLASUNCHIS',
                style: TextStyle(
                  fontFamily: 'Segoe UI',
                  color: const Color(0xFFCD9244),
                  fontSize: nameFontSize,
                  fontWeight: FontWeight.w800,
                  letterSpacing: widget.isFullScreen ? 5.0 : 3.5,
                ),
              ),
              const SizedBox(height: 3),

              // ── TAGLINE ──
              Text(
                'SISTEMA DE GESTIÓN',
                style: TextStyle(
                  fontFamily: 'Segoe UI',
                  color: widget.isFullScreen
                      ? Colors.white.withValues(alpha: 0.45)
                      : (isDark ? Colors.white60 : const Color(0xFF64748B)),
                  fontSize: taglineFontSize,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 2.2,
                ),
              ),
              SizedBox(height: widget.isFullScreen ? 24 : 12),

              // ── TRES PUNTOS CON REBOTE DESFASADO (PL-DOTS) ──
              AnimatedBuilder(
                animation: _dotsController,
                builder: (context, _) {
                  return Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      _buildDot(0.0),
                      const SizedBox(width: 8),
                      _buildDot(0.18 / 1.5),
                      const SizedBox(width: 8),
                      _buildDot(0.36 / 1.5),
                    ],
                  );
                },
              ),

              if (widget.mensaje != null) ...[
                SizedBox(height: widget.isFullScreen ? 16 : 8),
                Text(
                  widget.mensaje!,
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: widget.isFullScreen
                        ? Colors.white.withValues(alpha: 0.70)
                        : (isDark ? Colors.white70 : const Color(0xFF475569)),
                    fontSize: widget.isFullScreen ? 12 : 11,
                    fontWeight: FontWeight.w500,
                    letterSpacing: 0.3,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );

    if (!widget.isFullScreen) {
      return Container(
        decoration: backgroundDecoration,
        child: content,
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFF181006),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: backgroundDecoration,
        child: Stack(
          children: [
            content,

            // ── BARRIDO DORADO INFERIOR CONTINUO (PL-BAR) ──
            Positioned(
              left: 0,
              right: 0,
              bottom: 0,
              child: _SweepProgressBar(animation: _sweepController),
            ),
          ],
        ),
      ),
    );
  }

  /// Calcula la posición vertical y opacidad de cada punto según su retardo
  Widget _buildDot(double phaseOffset) {
    final double rawPhase = (_dotsController.value - phaseOffset) % 1.0;
    final double phase = rawPhase < 0 ? rawPhase + 1.0 : rawPhase;

    double yOffset = 0.0;
    double opacity = 0.40;

    // Curva idéntica a @keyframes pl-bounce: sube en el primer 40% del ciclo
    if (phase >= 0.0 && phase <= 0.40) {
      final t = phase / 0.40;
      final progress = math.sin(t * math.pi);
      yOffset = (widget.isFullScreen ? -9.0 : -6.0) * progress;
      opacity = 0.40 + (0.60 * progress);
    } else {
      yOffset = 0.0;
      opacity = 0.40;
    }

    final double dotSize = widget.isFullScreen ? 7.5 : 6.0;

    return Transform.translate(
      offset: Offset(0, yOffset),
      child: Opacity(
        opacity: opacity.clamp(0.0, 1.0),
        child: Container(
          width: dotSize,
          height: dotSize,
          decoration: const BoxDecoration(
            color: Color(0xFFCD9244),
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: Color(0x66CD9244),
                blurRadius: 4,
                spreadRadius: 1,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Barra inferior con barrido continuo de gradiente dorado (@keyframes pl-sweep)
class _SweepProgressBar extends StatelessWidget {
  const _SweepProgressBar({required this.animation});

  final Animation<double> animation;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 2.5,
      color: Colors.white.withValues(alpha: 0.08),
      child: AnimatedBuilder(
        animation: animation,
        builder: (context, _) {
          return CustomPaint(
            painter: _SweepPainter(progress: animation.value),
            size: const Size(double.infinity, 2.5),
          );
        },
      ),
    );
  }
}

class _SweepPainter extends CustomPainter {
  _SweepPainter({required this.progress});

  final double progress;

  @override
  void paint(Canvas canvas, Size size) {
    // El haz dorado mide el 55% del ancho de pantalla y recorre de -55% a 100%
    final sweepWidth = size.width * 0.55;
    final startX = (size.width + sweepWidth) * progress - sweepWidth;
    final endX = startX + sweepWidth;

    final paint = Paint()
      ..shader = LinearGradient(
        colors: const [
          Colors.transparent,
          Color(0xFFB27835),
          Color(0xFFE3AC66),
          Colors.transparent,
        ],
        stops: const [0.0, 0.4, 0.7, 1.0],
      ).createShader(Rect.fromLTRB(startX, 0, endX, size.height));

    canvas.drawRect(Rect.fromLTRB(startX, 0, endX, size.height), paint);
  }

  @override
  bool shouldRepaint(covariant _SweepPainter oldDelegate) {
    return oldDelegate.progress != progress;
  }
}
