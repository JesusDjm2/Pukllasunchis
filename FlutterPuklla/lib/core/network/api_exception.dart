import 'package:dio/dio.dart';

/// Extrae el [ApiException] adjuntado por el interceptor de `dio_client.dart`
/// y lo relanza; si el error no pasó por ese interceptor (p.ej. no hay
/// conexión antes de llegar a un `Response`), arma uno genérico.
Never throwApiException(DioException error) {
  final wrapped = error.error;
  if (wrapped is ApiException) throw wrapped;
  throw ApiException(
    statusCode: error.response?.statusCode,
    message: error.message ?? 'Error de conexión',
  );
}

/// Error de la API ya normalizado, a partir del formato de error de Laravel:
/// `{"message": "...", "errors": {"campo": ["..."]}}`.
class ApiException implements Exception {
  ApiException({
    required this.statusCode,
    required this.message,
    this.errors,
  });

  final int? statusCode;
  final String message;
  final Map<String, List<String>>? errors;

  bool get isUnauthorized => statusCode == 401;
  bool get isForbidden => statusCode == 403;
  bool get isNotFound => statusCode == 404;
  bool get isValidationError => statusCode == 422;

  @override
  String toString() => 'ApiException($statusCode, $message)';
}
