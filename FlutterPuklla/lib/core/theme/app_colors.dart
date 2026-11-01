import 'package:flutter/material.dart';

/// Paleta real de la EESP Pukllasunchis, extraída de `public/css/alumno-area.css`
/// (fuente de verdad del área de alumno en la web). No inventar tonos nuevos
/// aquí — si falta un color, revisar ese archivo primero.
class AppColors {
  const AppColors._();

  static const gold = Color(0xFFCD9244);
  static const goldHover = Color(0xFFB9823B);

  static const teal = Color(0xFF33445A);
  static const tealDark = Color(0xFF253449);

  static const background = Color(0xFFF8FAFC);
  static const surface = Color(0xFFFFFFFF);
  static const text = Color(0xFF1E293B);
  static const muted = Color(0xFF64748B);
  static const border = Color(0x140F172A); // rgba(15,23,42,.08)

  // Tokens para Modo Oscuro
  static const darkBackground = Color(0xFF0F172A);
  static const darkSurface = Color(0xFF1E293B);
  static const darkSurfaceElevated = Color(0xFF273549);
  static const darkBorder = Color(0xFF334155);
  static const darkText = Color(0xFFF8FAFC);
  static const darkMuted = Color(0xFF94A3B8);

  static const success = Color(0xFF0F5132);
  static const successBg = Color(0x21198754); // rgba(25,135,84,.13)
  static const danger = Color(0xFF842029);
  static const dangerBg = Color(0x21DC3545); // rgba(220,53,69,.13)
  static const warning = Color(0xFF664D03);
  static const warningBg = Color(0x2EFFC107); // rgba(255,193,7,.18)
}
