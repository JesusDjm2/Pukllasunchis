import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'periodo_actual.dart';

final periodoActualRepositoryProvider = Provider<PeriodoActualRepository>((ref) {
  return PeriodoActualRepository(ref.watch(dioProvider));
});

class PeriodoActualRepository {
  PeriodoActualRepository(this._dio);

  final Dio _dio;

  /// `null` si no hay ningún periodo marcado como activo (404 esperado del
  /// backend, no un error real).
  Future<PeriodoActual?> fetch() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/periodo-actual');
      return PeriodoActual.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) return null;
      throwApiException(e);
    }
  }
}
