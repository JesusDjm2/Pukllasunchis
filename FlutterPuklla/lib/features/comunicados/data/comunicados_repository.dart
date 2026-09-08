import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'comunicados_page.dart';

final comunicadosRepositoryProvider = Provider<ComunicadosRepository>((ref) {
  return ComunicadosRepository(ref.watch(dioProvider));
});

class ComunicadosRepository {
  ComunicadosRepository(this._dio);

  final Dio _dio;

  Future<ComunicadosPage> fetchComunicados({int page = 1}) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/comunicados',
        queryParameters: {'page': page},
      );
      return ComunicadosPage.fromJson(response.data!);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
