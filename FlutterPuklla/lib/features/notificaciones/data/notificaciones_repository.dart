import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'notificacion.dart';
import 'notificaciones_page.dart';

final notificacionesRepositoryProvider = Provider<NotificacionesRepository>((ref) {
  return NotificacionesRepository(ref.watch(dioProvider));
});

class NotificacionesRepository {
  NotificacionesRepository(this._dio);

  final Dio _dio;

  Future<NotificacionesPage> fetchNotificaciones({int page = 1}) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>(
        '/notificaciones',
        queryParameters: {'page': page},
      );
      return NotificacionesPage.fromJson(response.data!);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<int> fetchNoLeidasCount() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/notificaciones/no-leidas-count');
      return (response.data!['data'] as Map<String, dynamic>)['count'] as int;
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<Notificacion> marcarLeida(String id) async {
    try {
      final response = await _dio.post<Map<String, dynamic>>('/notificaciones/$id/leer');
      return Notificacion.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  Future<void> marcarTodasLeidas() async {
    try {
      await _dio.post<void>('/notificaciones/marcar-todas-leidas');
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
