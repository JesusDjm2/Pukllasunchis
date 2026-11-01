import 'package:flutter/material.dart';

import 'app_colors.dart';

/// Tema único de la app, calcado del branding real de la web (ver
/// [AppColors]) en vez del Material azul por defecto.
class AppTheme {
  const AppTheme._();

  static ThemeData get light {
    const colorScheme = ColorScheme.light(
      primary: AppColors.gold,
      onPrimary: Colors.white,
      secondary: AppColors.teal,
      onSecondary: Colors.white,
      surface: AppColors.surface,
      onSurface: AppColors.text,
      error: AppColors.danger,
      onError: Colors.white,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: AppColors.background,
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.teal,
        foregroundColor: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        centerTitle: false,
        titleTextStyle: TextStyle(
          color: Colors.white,
          fontSize: 20,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.1,
        ),
      ),
      cardTheme: CardThemeData(
        color: AppColors.surface,
        elevation: 3,
        shadowColor: AppColors.tealDark.withValues(alpha: 0.10),
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        margin: EdgeInsets.zero,
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: AppColors.gold,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          textStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
        ).copyWith(
          overlayColor: WidgetStateProperty.all(AppColors.goldHover.withValues(alpha: 0.15)),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(foregroundColor: AppColors.teal),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.surface,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.gold, width: 1.5),
        ),
        labelStyle: const TextStyle(color: AppColors.muted),
      ),
      progressIndicatorTheme: const ProgressIndicatorThemeData(color: AppColors.gold),
      chipTheme: ChipThemeData(
        backgroundColor: AppColors.background,
        labelStyle: const TextStyle(color: AppColors.text, fontWeight: FontWeight.w500),
        side: BorderSide.none,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
      badgeTheme: const BadgeThemeData(
        backgroundColor: AppColors.gold,
        textColor: Colors.white,
      ),
      tabBarTheme: const TabBarThemeData(
        labelColor: AppColors.gold,
        unselectedLabelColor: Color(0xFFE2E8F0),
        indicatorColor: AppColors.gold,
        indicatorSize: TabBarIndicatorSize.tab,
        dividerColor: Colors.transparent,
        labelStyle: TextStyle(
          color: AppColors.gold,
          fontWeight: FontWeight.w700,
          fontSize: 14,
        ),
        unselectedLabelStyle: TextStyle(
          color: Color(0xFFE2E8F0),
          fontWeight: FontWeight.w600,
          fontSize: 14,
        ),
      ),
      dividerTheme: const DividerThemeData(color: AppColors.border, space: 1),
      textTheme: const TextTheme(
        headlineMedium: TextStyle(color: AppColors.text, fontWeight: FontWeight.w700),
        titleLarge: TextStyle(color: AppColors.text, fontWeight: FontWeight.w700),
        titleMedium: TextStyle(color: AppColors.text, fontWeight: FontWeight.w600),
        bodyLarge: TextStyle(color: AppColors.text),
        bodyMedium: TextStyle(color: AppColors.text),
        bodySmall: TextStyle(color: AppColors.muted),
      ),
      listTileTheme: const ListTileThemeData(
        iconColor: AppColors.teal,
        textColor: AppColors.text,
      ),
    );
  }

  static ThemeData get dark {
    const colorScheme = ColorScheme.dark(
      primary: AppColors.gold,
      onPrimary: Colors.white,
      secondary: AppColors.goldHover,
      onSecondary: Colors.white,
      surface: AppColors.darkSurface,
      onSurface: AppColors.darkText,
      error: AppColors.danger,
      onError: Colors.white,
    );

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: AppColors.darkBackground,
      appBarTheme: const AppBarTheme(
        backgroundColor: Color(0xFF141F33),
        foregroundColor: Colors.white,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        centerTitle: false,
        titleTextStyle: TextStyle(
          color: Colors.white,
          fontSize: 20,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.1,
        ),
      ),
      cardTheme: CardThemeData(
        color: AppColors.darkSurface,
        elevation: 2,
        shadowColor: Colors.black.withValues(alpha: 0.3),
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(18),
          side: const BorderSide(color: AppColors.darkBorder, width: 1),
        ),
        margin: EdgeInsets.zero,
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: AppColors.gold,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          textStyle: const TextStyle(fontWeight: FontWeight.w600, fontSize: 15),
        ),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(foregroundColor: AppColors.gold),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.darkSurfaceElevated,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.darkBorder),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.darkBorder),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: AppColors.gold, width: 1.5),
        ),
        labelStyle: const TextStyle(color: AppColors.darkMuted),
      ),
      progressIndicatorTheme: const ProgressIndicatorThemeData(color: AppColors.gold),
      chipTheme: ChipThemeData(
        backgroundColor: AppColors.darkSurfaceElevated,
        labelStyle: const TextStyle(color: AppColors.darkText, fontWeight: FontWeight.w500),
        side: const BorderSide(color: AppColors.darkBorder),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
      badgeTheme: const BadgeThemeData(
        backgroundColor: AppColors.gold,
        textColor: Colors.white,
      ),
      tabBarTheme: const TabBarThemeData(
        labelColor: AppColors.gold,
        unselectedLabelColor: Color(0xFF94A3B8),
        indicatorColor: AppColors.gold,
        indicatorSize: TabBarIndicatorSize.tab,
        dividerColor: Colors.transparent,
        labelStyle: TextStyle(
          color: AppColors.gold,
          fontWeight: FontWeight.w700,
          fontSize: 14,
        ),
        unselectedLabelStyle: TextStyle(
          color: Color(0xFF94A3B8),
          fontWeight: FontWeight.w600,
          fontSize: 14,
        ),
      ),
      dividerTheme: const DividerThemeData(color: AppColors.darkBorder, space: 1),
      textTheme: const TextTheme(
        headlineMedium: TextStyle(color: AppColors.darkText, fontWeight: FontWeight.w700),
        titleLarge: TextStyle(color: AppColors.darkText, fontWeight: FontWeight.w700),
        titleMedium: TextStyle(color: AppColors.darkText, fontWeight: FontWeight.w600),
        bodyLarge: TextStyle(color: AppColors.darkText),
        bodyMedium: TextStyle(color: AppColors.darkText),
        bodySmall: TextStyle(color: AppColors.darkMuted),
      ),
      listTileTheme: const ListTileThemeData(
        iconColor: AppColors.gold,
        textColor: AppColors.darkText,
      ),
    );
  }
}
