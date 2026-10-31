import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'alumno_perfil.dart';

final perfilRepositoryProvider = Provider<PerfilRepository>((ref) {
  return PerfilRepository(ref.watch(dioProvider));
});

class PerfilRepository {
  PerfilRepository(this._dio);

  final Dio _dio;

  Future<AlumnoPerfil> fetchPerfil() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/alumno/perfil');
      return AlumnoPerfil.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<Uint8List> descargarFichaPdf() async {
    try {
      final response = await _dio.get<List<int>>(
        '/alumno/ficha-pdf',
        options: Options(responseType: ResponseType.bytes),
      );
      return Uint8List.fromList(response.data!);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
