import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/storage/token_storage.dart';
import '../../../core/theme/app_assets.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../application/auth_controller.dart';
import '../application/auth_state.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _submitting = false;
  bool _obscurePassword = true;
  bool _rememberUser = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadSavedUsername();
  }

  Future<void> _loadSavedUsername() async {
    final saved = await ref.read(tokenStorageProvider).readSavedUsername();
    if (saved != null && saved.isNotEmpty && mounted) {
      setState(() {
        _emailController.text = saved;
        _rememberUser = true;
      });
    }
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _submitting = true;
      _errorMessage = null;
    });

    final username = _emailController.text.trim();
    if (_rememberUser) {
      await ref.read(tokenStorageProvider).saveUsername(username);
    } else {
      await ref.read(tokenStorageProvider).deleteSavedUsername();
    }

    try {
      await ref.read(authControllerProvider.notifier).login(
            email: username,
            password: _passwordController.text,
          );
      // El redirect de go_router (ver routing/app_router.dart) se encarga de
      // navegar a /home o /docente apenas el estado pase a authenticated.
    } on ApiException catch (e) {
      setState(() => _errorMessage = e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _mostrarAyuda(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? AppColors.darkSurface : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.gold.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.help_outline_rounded, color: AppColors.goldHover, size: 22),
            ),
            const SizedBox(width: 10),
            Text(
              'Ayuda de Acceso',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.w800,
                color: PukllaDashboardStyles.textPrimary(ctx),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Si olvidaste tu contraseña o tienes problemas para ingresar con tu cuenta institucional, por favor comunícate con la Secretaría Académica de la EESP Pukllasunchis:',
              style: TextStyle(
                fontSize: 13,
                height: 1.45,
                color: PukllaDashboardStyles.textSecondary(ctx),
              ),
            ),
            const SizedBox(height: 14),
            _buildInfoRow(ctx, Icons.email_outlined, 'secretaria@pukllasunchis.org'),
            const SizedBox(height: 8),
            _buildInfoRow(ctx, Icons.phone_outlined, '(084) 237-918 / Anexo 102'),
            const SizedBox(height: 8),
            _buildInfoRow(ctx, Icons.access_time_rounded, 'Lunes a Viernes: 8:00 am - 1:00 pm'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Entendido', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.goldHover)),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(BuildContext context, IconData icon, String text) {
    return Row(
      children: [
        Icon(icon, size: 16, color: AppColors.teal),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            text,
            style: TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w600,
              color: PukllaDashboardStyles.textPrimary(context),
            ),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    // Mientras se restaura una sesión previa desde el token guardado:
    // Evita el parpadeo blanco mostrando el Preloader institucional oficial
    if (ref.watch(authControllerProvider).status == AuthStatus.unknown) {
      return const PukllaPreloader(mensaje: 'Verificando sesión institucional...');
    }

    return Scaffold(
      body: Stack(
        fit: StackFit.expand,
        children: [
          // ── FONDO DINÁMICO CON FOTOS DE LA ESCUELA (PROTAGONISTA VISUAL) ──
          const _BreathingBackground(),

          // ── OVERLAY GRADIENTE LIGERO (GARANTIZA LEGIBILIDAD SIN OPACAR LAS FOTOS) ──
          DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.black.withValues(alpha: 0.28),
                  Colors.black.withValues(alpha: 0.58),
                ],
              ),
            ),
          ),

          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 24),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 400),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Logo institucional oficial en blanco
                        Center(
                          child: Image.asset(
                            AppAssets.logoBlanco,
                            height: 84,
                            fit: BoxFit.contain,
                          ),
                        ),
                        const SizedBox(height: 26),

                        // Título de bienvenida
                        Text(
                          'Bienvenido(a)',
                          textAlign: TextAlign.center,
                          style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.3,
                                shadows: const [
                                  Shadow(color: Colors.black54, blurRadius: 10, offset: Offset(0, 2)),
                                ],
                              ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Ingresa con tu cuenta institucional',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.88),
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            shadows: const [
                              Shadow(color: Colors.black54, blurRadius: 6, offset: Offset(0, 1)),
                            ],
                          ),
                        ),
                        const SizedBox(height: 28),

                        // Campo Usuario / Correo institucional
                        TextFormField(
                          controller: _emailController,
                          keyboardType: TextInputType.emailAddress,
                          autofillHints: const [AutofillHints.email],
                          style: const TextStyle(
                            color: Color(0xFF0F172A),
                            fontWeight: FontWeight.w600,
                            fontSize: 14.5,
                          ),
                          decoration: InputDecoration(
                            hintText: 'Usuario o Correo institucional',
                            hintStyle: const TextStyle(
                              color: Color(0xFF94A3B8),
                              fontWeight: FontWeight.normal,
                              fontSize: 14,
                            ),
                            prefixIcon: const Icon(Icons.person_outline_rounded, color: AppColors.goldHover),
                            filled: true,
                            fillColor: Colors.white.withValues(alpha: 0.95),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.8), width: 1),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: AppColors.gold, width: 2),
                            ),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          ),
                          validator: (value) => (value == null || value.isEmpty) ? 'Ingresa tu usuario' : null,
                        ),
                        const SizedBox(height: 16),

                        // Campo Contraseña
                        TextFormField(
                          controller: _passwordController,
                          obscureText: _obscurePassword,
                          autofillHints: const [AutofillHints.password],
                          style: const TextStyle(
                            color: Color(0xFF0F172A),
                            fontWeight: FontWeight.w600,
                            fontSize: 14.5,
                          ),
                          decoration: InputDecoration(
                            hintText: 'Contraseña',
                            hintStyle: const TextStyle(
                              color: Color(0xFF94A3B8),
                              fontWeight: FontWeight.normal,
                              fontSize: 14,
                            ),
                            prefixIcon: const Icon(Icons.lock_outline_rounded, color: AppColors.goldHover),
                            suffixIcon: IconButton(
                              icon: Icon(
                                _obscurePassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                color: const Color(0xFF64748B),
                              ),
                              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                            ),
                            filled: true,
                            fillColor: Colors.white.withValues(alpha: 0.95),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.8), width: 1),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(color: AppColors.gold, width: 2),
                            ),
                            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          ),
                          validator: (value) => (value == null || value.isEmpty) ? 'Ingresa tu contraseña' : null,
                          onFieldSubmitted: (_) => _submit(),
                        ),

                        const SizedBox(height: 14),

                        // Checkbox "Recordar mi cuenta" sobre el fondo
                        Row(
                          children: [
                            SizedBox(
                              height: 22,
                              width: 22,
                              child: Checkbox(
                                value: _rememberUser,
                                activeColor: AppColors.gold,
                                checkColor: Colors.white,
                                side: const BorderSide(color: Colors.white, width: 1.5),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                onChanged: (val) => setState(() => _rememberUser = val ?? false),
                              ),
                            ),
                            const SizedBox(width: 8),
                            GestureDetector(
                              onTap: () => setState(() => _rememberUser = !_rememberUser),
                              child: const Text(
                                'Recordar mi cuenta',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  shadows: [
                                    Shadow(color: Colors.black54, blurRadius: 4, offset: Offset(0, 1)),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),

                        // Banner de Error semántico
                        if (_errorMessage != null) ...[
                          const SizedBox(height: 14),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFEF2F2).withValues(alpha: 0.96),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFEF4444)),
                              boxShadow: const [
                                BoxShadow(color: Colors.black26, blurRadius: 6, offset: Offset(0, 2)),
                              ],
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.error_outline_rounded, size: 18, color: Color(0xFFDC2626)),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    _errorMessage!,
                                    style: const TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.w600,
                                      color: Color(0xFF991B1B),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],

                        const SizedBox(height: 22),

                        // Botón Ingresar Dorado con Gradiente y PukllaScaleTap
                        PukllaScaleTap(
                          onTap: _submitting ? null : _submit,
                          child: Container(
                            height: 50,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFFD59D52), Color(0xFFCD9244)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(12),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.35),
                                  blurRadius: 14,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: _submitting
                                ? const SizedBox(
                                    width: 22,
                                    height: 22,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2.2,
                                      color: Colors.white,
                                    ),
                                  )
                                : const Text(
                                    'Ingresar',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w700,
                                      fontSize: 16,
                                      letterSpacing: 0.3,
                                    ),
                                  ),
                          ),
                        ),

                        const SizedBox(height: 14),

                        // Enlace de Ayuda / Soporte
                        Center(
                          child: TextButton(
                            onPressed: () => _mostrarAyuda(context),
                            style: TextButton.styleFrom(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            ),
                            child: const Text(
                              '¿Necesitas ayuda institucional?',
                              style: TextStyle(
                                fontSize: 13,
                                color: Color(0xFFFDE68A),
                                fontWeight: FontWeight.w700,
                                shadows: [
                                  Shadow(color: Colors.black54, blurRadius: 4, offset: Offset(0, 1)),
                                ],
                              ),
                            ),
                          ),
                        ),

                        const SizedBox(height: 36),

                        // Eslogan institucional inclinado (-12°) con subrayado de marcador
                        Transform.rotate(
                          angle: -12 * math.pi / 180,
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Text(
                                'Formando docentes\npara un mejor mañana',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  color: Colors.white,
                                  fontStyle: FontStyle.italic,
                                  fontWeight: FontWeight.w600,
                                  fontSize: 16,
                                  shadows: [
                                    Shadow(color: Colors.black54, blurRadius: 8, offset: Offset(0, 2)),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 6),
                              CustomPaint(
                                size: const Size(190, 12),
                                painter: const _MarkerUnderlinePainter(),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Fondo del login: zoom continuo de ida y vuelta ("respira") sobre la foto
/// actual, y cada cierto tiempo cambia a la siguiente con un fundido.
class _BreathingBackground extends StatefulWidget {
  const _BreathingBackground();

  @override
  State<_BreathingBackground> createState() => _BreathingBackgroundState();
}

class _BreathingBackgroundState extends State<_BreathingBackground>
    with SingleTickerProviderStateMixin {
  late final AnimationController _zoomController;
  Timer? _switchTimer;
  int _index = 0;

  @override
  void initState() {
    super.initState();
    _zoomController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 9),
    )..repeat(reverse: true);

    _switchTimer = Timer.periodic(const Duration(seconds: 8), (_) {
      if (!mounted) return;
      setState(() => _index = (_index + 1) % AppAssets.fondosEscuela.length);
    });
  }

  @override
  void dispose() {
    _zoomController.dispose();
    _switchTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ClipRect(
      child: AnimatedBuilder(
        animation: _zoomController,
        builder: (context, child) {
          final scale = 1.0 + (_zoomController.value * 0.1);
          return Transform.scale(scale: scale, child: child);
        },
        child: AnimatedSwitcher(
          duration: const Duration(milliseconds: 1800),
          child: Image.asset(
            AppAssets.fondosEscuela[_index],
            key: ValueKey(_index),
            fit: BoxFit.cover,
            width: double.infinity,
            height: double.infinity,
          ),
        ),
      ),
    );
  }
}

/// Línea suelta, como trazada con plumón, debajo del eslogan.
class _MarkerUnderlinePainter extends CustomPainter {
  const _MarkerUnderlinePainter();

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = AppColors.gold.withValues(alpha: 0.9)
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.round
      ..style = PaintingStyle.stroke;

    final path = Path()
      ..moveTo(0, size.height * 0.65)
      ..quadraticBezierTo(size.width * 0.28, size.height * 0.05, size.width * 0.52, size.height * 0.55)
      ..quadraticBezierTo(size.width * 0.76, size.height * 1.0, size.width, size.height * 0.4);

    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant _MarkerUnderlinePainter oldDelegate) => false;
}
