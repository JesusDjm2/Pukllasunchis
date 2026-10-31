import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import '../../../core/storage/token_storage.dart';
import 'auth_user.dart';

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(
    dio: ref.watch(dioProvider),
    tokenStorage: ref.watch(tokenStorageProvider),
  );
});

/// Llama a los endpoints de auth de `/api/v1` (ver `_notes/api-v1-endpoints.md`)
/// y mantiene sincronizado el token en [TokenStorage].
class AuthRepository {
  AuthRepository({required Dio dio, required TokenStorage tokenStorage})
      : _dio = dio,
        _tokenStorage = tokenStorage;

  final Dio _dio;
  final TokenStorage _tokenStorage;

  Future<bool> hasStoredToken() async => (await _tokenStorage.readToken()) != null;

  Future<AuthUser> login({required String email, required String password}) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/login',
        data: {
          'email': email,
          'password': password,
          'device_name': 'flutter_puklla',
        },
      );
      final data = response.data!['data'] as Map<String, dynamic>;
      await _tokenStorage.saveToken(data['token'] as String);
      return AuthUser.fromJson(data['user'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<AuthUser> me() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/me');
      return AuthUser.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  /// `silent`: no llama a `/logout` (usado al descartar un token que ya no
  /// sirve, p.ej. tras un 401 tratando de restaurar la sesión).
  Future<void> logout({bool silent = false}) async {
    if (!silent) {
      try {
        await _dio.post<void>('/logout');
      } on DioException {
        // Token ya inválido o sin conexión: igual limpiamos localmente.
      }
    }
    await _tokenStorage.deleteToken();
  }
}
