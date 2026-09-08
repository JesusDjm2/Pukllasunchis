import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'curso.dart';

final cursoRepositoryProvider = Provider<CursoRepository>((ref) {
  return CursoRepository(ref.watch(dioProvider));
});

class CursoRepository {
  CursoRepository(this._dio);

  final Dio _dio;

  /// Cursos del alumno autenticado en el periodo activo (lista vacía si no
  /// hay periodo activo). 404 si el usuario no tiene un perfil de `Alumno`.
  Future<List<Curso>> fetchCursos() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/cursos');
      final data = response.data!['data'] as List;
      return data.map((json) => Curso.fromJson(json as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
