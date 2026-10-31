import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'tutoria_data.dart';

final tutoriaRepositoryProvider = Provider<TutoriaRepository>((ref) {
  return TutoriaRepository(ref.watch(dioProvider));
});

class TutoriaRepository {
  const TutoriaRepository(this._dio);

  final Dio _dio;

  Future<TutoriaData> fetchTutoriaData() async {
    final response = await _dio.get('/tutoria');
    final data = response.data['data'] as Map<String, dynamic>;
    return TutoriaData.fromJson(data);
  }

  Future<String> solicitarTutoria({
    required String urgencia,
    int? docenteId,
    String? motivo,
  }) async {
    final response = await _dio.post('/tutoria', data: {
      'urgencia': urgencia,
      if (docenteId != null) 'docente_id': docenteId,
      if (motivo != null && motivo.isNotEmpty) 'motivo': motivo,
    });
    return response.data['message'] as String? ?? 'Solicitud enviada correctamente.';
  }

  Future<String> enviarSugerencia({
    required String mensaje,
  }) async {
    final response = await _dio.post('/sugerencia', data: {
      'mensaje': mensaje,
    });
    return response.data['message'] as String? ?? 'Sugerencia enviada correctamente.';
  }
}
