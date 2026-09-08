import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../env/env.dart';
import '../storage/token_storage.dart';
import 'api_exception.dart';

/// Cliente Dio único de la app: base URL de la API, header Bearer inyectado
/// desde [TokenStorage], y errores normalizados a [ApiException].
final dioProvider = Provider<Dio>((ref) {
  final dio = Dio(
    BaseOptions(
      baseUrl: Env.apiBaseUrl,
      headers: const {'Accept': 'application/json'},
    ),
  );

  final tokenStorage = ref.watch(tokenStorageProvider);

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await tokenStorage.readToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) => handler.reject(_mapError(error)),
    ),
  );

  return dio;
});

DioException _mapError(DioException error) {
  final response = error.response;
  if (response == null) {
    return error.copyWith(
      error: ApiException(statusCode: null, message: error.message ?? 'Error de conexión'),
    );
  }

  final data = response.data;
  final message = data is Map && data['message'] is String
      ? data['message'] as String
      : 'Error inesperado del servidor';

  Map<String, List<String>>? errors;
  if (data is Map && data['errors'] is Map) {
    errors = (data['errors'] as Map).map(
      (key, value) => MapEntry(key as String, List<String>.from(value as List)),
    );
  }

  return error.copyWith(
    error: ApiException(statusCode: response.statusCode, message: message, errors: errors),
  );
}
