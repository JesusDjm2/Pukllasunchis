import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'docente_alumno.dart';
import 'docente_calificar_data.dart';
import 'docente_curso.dart';
import 'docente_incidencia.dart';
import 'docente_resumen.dart';
import 'docente_tutor_dashboard.dart';

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

  Future<DocenteTutorDashboard> fetchTutorDashboard() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/docente/tutor');
      return DocenteTutorDashboard.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<DocenteCalificarData> fetchCalificaciones(int cursoId) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/docente/cursos/$cursoId/calificaciones');
      return DocenteCalificarData.fromJson(response.data!);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<void> guardarCalificacionesFid({
    required int cursoId,
    required String periodo,
    required List<Map<String, dynamic>> alumnos,
  }) async {
    try {
      await _dio.post<Map<String, dynamic>>(
        '/docente/cursos/$cursoId/calificaciones',
        data: {
          'periodo': periodo,
          'alumnos': alumnos,
        },
      );
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<void> guardarCalificacionesPpd({
    required int cursoId,
    required List<Map<String, dynamic>> alumnos,
  }) async {
    try {
      await _dio.post<Map<String, dynamic>>(
        '/docente/cursos/$cursoId/calificaciones',
        data: {
          'periodo': 'ppd',
          'alumnos': alumnos,
        },
      );
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
