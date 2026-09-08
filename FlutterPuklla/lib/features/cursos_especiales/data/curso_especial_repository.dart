import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'curso_especial.dart';
import 'curso_especial_detalle.dart';
import 'curso_especial_progreso.dart';

final cursoEspecialRepositoryProvider = Provider<CursoEspecialRepository>((ref) {
  return CursoEspecialRepository(ref.watch(dioProvider));
});

class CursoEspecialRepository {
  CursoEspecialRepository(this._dio);

  final Dio _dio;

  Future<List<CursoEspecial>> fetchCatalogo() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/cursos-especiales');
      final data = response.data!['data'] as List;
      return data.map((json) => CursoEspecial.fromJson(json as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<void> inscribir(int cursoId) async {
    try {
      await _dio.post<void>('/cursos-especiales/$cursoId/inscribir');
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  /// 403 (`ApiException.isForbidden`) si el usuario no está inscrito.
  Future<CursoEspecialDetalle> fetchDetalle(int cursoId) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/cursos-especiales/$cursoId');
      return CursoEspecialDetalle.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<CursoEspecialProgreso> fetchProgreso(int cursoId) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/cursos-especiales/$cursoId/progreso');
      return CursoEspecialProgreso.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<void> completarLeccion(int leccionId) async {
    try {
      await _dio.post<void>('/lecciones/$leccionId/completar');
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  /// Devuelve `(correcta, puntaje)`.
  Future<(bool, int)> responderEjercicio(int ejercicioId, String respuesta) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>(
        '/ejercicios/$ejercicioId/responder',
        data: {'respuesta': respuesta},
      );
      final data = response.data!['data'] as Map<String, dynamic>;
      return (data['correcta'] as bool, data['puntaje'] as int);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
