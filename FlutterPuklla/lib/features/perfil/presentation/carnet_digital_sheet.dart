import 'package:flutter/material.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../data/alumno_perfil.dart';

/// Modal interactivo de alta fidelidad que reproduce los carnets oficiales:
/// 1. Carnet del Estudiante (ID Oficial)
/// 2. Carnet de Biblioteca (Préstamo bibliográfico y acceso físico)
class CarnetDigitalSheet extends StatefulWidget {
  const CarnetDigitalSheet({super.key, required this.perfil});

  final AlumnoPerfil perfil;

  static Future<void> show(BuildContext context, AlumnoPerfil perfil) {
    return showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => CarnetDigitalSheet(perfil: perfil),
    );
  }

  @override
  State<CarnetDigitalSheet> createState() => _CarnetDigitalSheetState();
}

class _CarnetDigitalSheetState extends State<CarnetDigitalSheet> {
  int _tabSeleccionado = 0; // 0: Estudiante, 1: Biblioteca

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final size = MediaQuery.of(context).size;

    return Container(
      constraints: BoxConstraints(maxHeight: size.height * 0.90),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(26)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.25),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag handle
          Container(
            width: 44,
            height: 4.5,
            margin: const EdgeInsets.only(top: 12, bottom: 8),
            decoration: BoxDecoration(
              color: isDark ? Colors.white24 : const Color(0xFFCBD5E1),
              borderRadius: BorderRadius.circular(3),
            ),
          ),

          // Header con título y botón de cierre
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 4, 12, 8),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Credencial Oficial Digital',
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          color: isDark ? AppColors.goldHover : AppColors.teal,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Carnet del Estudiante',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w800,
                          color: PukllaDashboardStyles.textPrimary(context),
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close_rounded),
                  color: PukllaDashboardStyles.textSecondary(context),
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),
          ),

          // Selector de pestaña: Estudiante vs. Biblioteca
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
            child: Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: isDark ? AppColors.darkCard : const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                ),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: _TabButton(
                      label: 'Carnet Estudiante',
                      icon: Icons.badge_outlined,
                      active: _tabSeleccionado == 0,
                      activeColor: const Color(0xFFB96328),
                      onTap: () => setState(() => _tabSeleccionado = 0),
                    ),
                  ),
                  Expanded(
                    child: _TabButton(
                      label: 'Carnet Biblioteca',
                      icon: Icons.local_library_outlined,
                      active: _tabSeleccionado == 1,
                      activeColor: const Color(0xFF1F6F8B),
                      onTap: () => setState(() => _tabSeleccionado = 1),
                    ),
                  ),
                ],
              ),
            ),
          ),

          const Divider(height: 16),

          // Credencial física renderizada en pantalla
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
              child: Column(
                children: [
                  _CarnetCredencialView(
                    perfil: widget.perfil,
                    esBiblioteca: _tabSeleccionado == 1,
                  ),
                  const SizedBox(height: 16),
                  // Nota institucional de validez
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: isDark ? AppColors.darkCard : const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                      ),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.verified_outlined, size: 18, color: AppColors.teal),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Credencial válida para identificación en campus, bibliotecas y eventos de la EESP Pukllasunchis.',
                            style: TextStyle(
                              fontSize: 11.5,
                              color: PukllaDashboardStyles.textSecondary(context),
                              height: 1.3,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _TabButton extends StatelessWidget {
  const _TabButton({
    required this.label,
    required this.icon,
    required this.active,
    required this.activeColor,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final bool active;
  final Color activeColor;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(9),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 9),
        decoration: BoxDecoration(
          color: active ? activeColor : Colors.transparent,
          borderRadius: BorderRadius.circular(9),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 16,
              color: active ? Colors.white : PukllaDashboardStyles.textSecondary(context),
            ),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                fontSize: 12.5,
                fontWeight: active ? FontWeight.w700 : FontWeight.w600,
                color: active ? Colors.white : PukllaDashboardStyles.textSecondary(context),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CarnetCredencialView extends StatelessWidget {
  const _CarnetCredencialView({
    required this.perfil,
    required this.esBiblioteca,
  });

  final AlumnoPerfil perfil;
  final bool esBiblioteca;

  @override
  Widget build(BuildContext context) {
    // Configuración de paleta exacta de `carnet-estudiante-inner.blade.php`:
    // Estudiante: primary #B96328 (terracota), outer #E8DCC8 (beige)
    // Biblioteca: primary #1F6F8B (azul petróleo), outer #DCEEF2 (celeste suave)
    final primaryColor = esBiblioteca ? const Color(0xFF1F6F8B) : const Color(0xFFB96328);
    final outerColor = esBiblioteca ? const Color(0xFFDCEEF2) : const Color(0xFFE8DCC8);
    final labelColor = esBiblioteca ? const Color(0xFF20ADE9) : const Color(0xFFC41E3A);
    final titulo = esBiblioteca ? 'Carnet Biblioteca' : 'ID Estudiante';

    final email = perfil.email ?? '';
    final match = RegExp(r'20\d{2}').firstMatch(email);
    final anioIngreso = match?.group(0) ?? DateTime.now().year.toString();

    return Center(
      child: Container(
        width: 310,
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(
          color: outerColor,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.18),
              blurRadius: 18,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Container(
          decoration: BoxDecoration(
            color: primaryColor,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // ── CABECERA INSTITUCIONAL DEL CARNET ──
              Padding(
                padding: const EdgeInsets.fromLTRB(14, 14, 14, 10),
                child: Row(
                  children: [
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'EESP PUKLLASUNCHIS',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.8,
                            ),
                          ),
                          Text(
                            'Educación Superior Pedagógica',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 9,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(width: 1, height: 26, color: Colors.white38),
                    const SizedBox(width: 10),
                    Text(
                      titulo,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),

              // ── CUERPO BLANCO DEL CARNET ──
              Container(
                margin: const EdgeInsets.fromLTRB(8, 0, 8, 8),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Column(
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Datos del estudiante
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Programa
                              Text(
                                'PROGRAMA',
                                style: TextStyle(
                                  fontSize: 8.5,
                                  fontWeight: FontWeight.w800,
                                  color: labelColor,
                                  letterSpacing: 0.5,
                                ),
                              ),
                              Text(
                                (perfil.programaNombre ?? 'EESP Pukllasunchis').toUpperCase(),
                                style: const TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF1E293B),
                                  height: 1.2,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 8),

                              // Año de Ingreso y Ciclo
                              Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'AÑO INGRESO',
                                          style: TextStyle(
                                            fontSize: 8,
                                            fontWeight: FontWeight.w800,
                                            color: labelColor,
                                          ),
                                        ),
                                        Text(
                                          anioIngreso,
                                          style: const TextStyle(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w800,
                                            color: Color(0xFF1E293B),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'CICLO',
                                          style: TextStyle(
                                            fontSize: 8,
                                            fontWeight: FontWeight.w800,
                                            color: labelColor,
                                          ),
                                        ),
                                        Text(
                                          perfil.cicloNombre ?? 'I',
                                          style: const TextStyle(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w800,
                                            color: Color(0xFF1E293B),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),

                              // DNI
                              Text(
                                'DOCUMENTO (DNI)',
                                style: TextStyle(
                                  fontSize: 8,
                                  fontWeight: FontWeight.w800,
                                  color: labelColor,
                                ),
                              ),
                              Text(
                                perfil.dni,
                                style: const TextStyle(
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 0.5,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 10),

                        // Fotografía oficial del carnet
                        Column(
                          children: [
                            Container(
                              width: 82,
                              height: 98,
                              decoration: BoxDecoration(
                                color: const Color(0xFFE2E8F0),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(color: primaryColor, width: 1.5),
                                image: perfil.foto != null
                                    ? DecorationImage(
                                        image: NetworkImage(perfil.foto!),
                                        fit: BoxFit.cover,
                                      )
                                    : null,
                              ),
                              child: perfil.foto == null
                                  ? const Icon(Icons.person, size: 48, color: Colors.grey)
                                  : null,
                            ),
                          ],
                        ),
                      ],
                    ),

                    const SizedBox(height: 10),

                    // Nombre Completo abajo destacado
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 8),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'APELLIDOS Y NOMBRES',
                            style: TextStyle(
                              fontSize: 7.5,
                              fontWeight: FontWeight.w800,
                              color: labelColor,
                            ),
                          ),
                          Text(
                            perfil.nombreCompleto.toUpperCase(),
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF0F172A),
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 10),

                    // Código de barras simulado / QR representativo del carnet
                    _BarcodePainterView(
                      codigo: '${perfil.dni}-${perfil.id}',
                      color: primaryColor,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _BarcodePainterView extends StatelessWidget {
  const _BarcodePainterView({required this.codigo, required this.color});

  final String codigo;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        CustomPaint(
          size: const Size(220, 28),
          painter: _BarcodePainter(color: Colors.black87),
        ),
        const SizedBox(height: 2),
        Text(
          '* $codigo *',
          style: const TextStyle(
            fontSize: 9.5,
            letterSpacing: 2,
            fontFamily: 'monospace',
            fontWeight: FontWeight.w700,
            color: Color(0xFF64748B),
          ),
        ),
      ],
    );
  }
}

class _BarcodePainter extends CustomPainter {
  _BarcodePainter({required this.color});

  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeCap = StrokeCap.square;

    final widths = [
      2.0, 1.0, 3.0, 1.5, 1.0, 2.5, 1.0, 4.0, 1.0, 2.0, 1.5, 3.0, 1.0, 2.0, 1.0,
      3.0, 1.5, 2.0, 1.0, 4.0, 1.5, 2.0, 1.0, 3.0, 2.0, 1.0, 1.5, 3.0, 1.0, 2.0,
      1.5, 4.0, 1.0, 2.5, 1.0, 3.0, 1.5, 1.0, 2.0, 3.5, 1.0, 2.0, 1.5, 1.0, 3.0
    ];

    double currentX = 0;
    final totalPatternWidth = widths.reduce((a, b) => a + b) + (widths.length * 1.5);
    final scale = size.width / totalPatternWidth;

    for (int i = 0; i < widths.length; i++) {
      final w = widths[i] * scale;
      if (i % 2 == 0) {
        paint.strokeWidth = w;
        canvas.drawLine(
          Offset(currentX + (w / 2), 0),
          Offset(currentX + (w / 2), size.height),
          paint,
        );
      }
      currentX += w + (1.5 * scale);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
