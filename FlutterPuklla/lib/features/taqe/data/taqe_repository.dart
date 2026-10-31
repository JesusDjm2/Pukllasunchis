import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'taqe_data.dart';

final taqeRepositoryProvider = Provider<TaqeRepository>((ref) {
  return TaqeRepository(ref.watch(dioProvider));
});

class TaqeRepository {
  const TaqeRepository(this._dio);

  final Dio _dio;

  Future<TaqeData> fetchTaqeData() async {
    final response = await _dio.get('/taqe');
    final data = response.data['data'] as Map<String, dynamic>;
    return TaqeData.fromJson(data);
  }

  Future<String> responderInvitacion({
    required int grupoId,
    required String respuesta, // 'aceptado' | 'rechazado'
  }) async {
    final response = await _dio.post('/taqe/responder', data: {
      'grupo_id': grupoId,
      'respuesta': respuesta,
    });
    return response.data['message'] as String? ?? 'Operación realizada correctamente.';
  }

  Future<String> solicitarAyuda({
    required int grupoId,
    required String motivo,
    String? mensaje,
  }) async {
    final response = await _dio.post('/taqe/solicitar-ayuda', data: {
      'grupo_id': grupoId,
      'motivo': motivo,
      if (mensaje != null && mensaje.isNotEmpty) 'mensaje': mensaje,
    });
    return response.data['message'] as String? ?? 'Solicitud de ayuda enviada.';
  }
}
