import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'calificacion.dart';
import 'matricula.dart';

final calificacionesRepositoryProvider = Provider<CalificacionesRepository>((ref) {
  return CalificacionesRepository(ref.watch(dioProvider));
});

/// Solo lectura: matrículas y calificaciones del alumno autenticado.
class CalificacionesRepository {
  CalificacionesRepository(this._dio);

  final Dio _dio;

  Future<List<Matricula>> fetchMatriculas() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/matriculas');
      final data = response.data!['data'] as List;
      return data.map((json) => Matricula.fromJson(json as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  /// Período actual (con parciales) + históricos agrupados por período,
  /// igual que `AlumnoController@calificaciones` en la web.
  Future<CalificacionesResponse> fetchCalificaciones() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/calificaciones');
      return CalificacionesResponse.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
