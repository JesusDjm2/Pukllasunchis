import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_client.dart';
import 'bolsa_trabajo_oferta.dart';

final bolsaTrabajoRepositoryProvider = Provider<BolsaTrabajoRepository>((ref) {
  return BolsaTrabajoRepository(ref.watch(dioProvider));
});

class BolsaTrabajoRepository {
  const BolsaTrabajoRepository(this._dio);

  final Dio _dio;

  Future<List<BolsaTrabajoOferta>> fetchOfertas() async {
    final response = await _dio.get('/bolsa-trabajo');
    final data = response.data['data'] as List? ?? [];
    return data
        .map((item) => BolsaTrabajoOferta.fromJson(item as Map<String, dynamic>))
        .toList();
  }
}
