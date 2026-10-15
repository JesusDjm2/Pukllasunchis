/// Configuración de entorno, inyectada en tiempo de build.
///
/// Uso:
///   flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8001/api/v1
class Env {
  const Env._();

  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8001/api/v1',
  );
}
