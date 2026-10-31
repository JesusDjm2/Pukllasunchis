class AppAssets {
  const AppAssets._();

  /// Logo a color, para fondos claros (login, tarjetas blancas).
  static const logoColor = 'assets/images/logo-color.png';

  /// Logo en blanco, para fondos oscuros (drawer/header teal).
  static const logoBlanco = 'assets/images/logo-blanco.png';

  /// Fotos de la escuela que rotan como fondo del login (zoom continuo +
  /// fade entre ellas, ver `LoginScreen`).
  static const fondosEscuela = [
    'assets/images/fondo-escuela-1.png',
    'assets/images/fondo-escuela-2.png',
    'assets/images/fondo-escuela-3.png',
  ];
}
