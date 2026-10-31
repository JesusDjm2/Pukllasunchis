import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'docente_alumno.dart';
import 'docente_curso.dart';
import 'docente_incidencia.dart';
import 'docente_resumen.dart';

final docenteRepositoryProvider = Provider<DocenteRepository>((ref) {
  return DocenteRepository(ref.watch(dioProvider));
});

/// Endpoints del área docente (`/docente/*` y `/incidencias`).
class DocenteRepository {
  DocenteRepository(this._dio);

  final Dio _dio;

  Future<DocenteResumen> fetchResumen() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/docente/resumen');
      return DocenteResumen.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<List<DocenteCurso>> fetchCursos() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/docente/cursos');
      return (response.data!['data'] as List)
          .map((item) => DocenteCurso.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<DocenteIncidenciasPage> fetchIncidencias({int page = 1}) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/incidencias',
        queryParameters: {'page': page},
      );
      return DocenteIncidenciasPage.fromJson(response.data!);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<List<DocenteCursoAlumnos>> fetchAlumnos() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/docente/alumnos');
      return (response.data!['data'] as List)
          .map((item) => DocenteCursoAlumnos.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
