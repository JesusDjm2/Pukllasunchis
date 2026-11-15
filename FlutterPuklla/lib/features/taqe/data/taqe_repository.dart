import 'dart:typed_data';

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

  /// Guía del estudiante de Investigaciones (PDF), la misma que descarga la web.
  Future<Uint8List> descargarGuiaPdf() async {
    final response = await _dio.get<List<int>>(
      '/taqe/guia-pdf',
      options: Options(responseType: ResponseType.bytes),
    );
    return Uint8List.fromList(response.data!);
  }

  Future<List<TaqeCompanero>> fetchCompaneros(int cursoId) async {
    final response = await _dio.get('/taqe/cursos/$cursoId/alumnos');
    final list = response.data['data'] as List? ?? [];
    return list.map((e) => TaqeCompanero.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<String> crearGrupo({
    required String nombre,
    required int cursoId,
    List<int>? integrantes,
  }) async {
    final response = await _dio.post('/taqe/grupos', data: {
      'nombre': nombre,
      'curso_id': cursoId,
      if (integrantes != null && integrantes.isNotEmpty) 'integrantes': integrantes,
    });
    return response.data['message'] as String? ?? 'Grupo creado exitosamente.';
  }

  Future<String> subirTrabajo({
    required int grupoId,
    required String nombre,
    required String destino, // 'evidencia' | 'final'
    required String modo,    // 'archivo' | 'enlace'
    String? enlace,
  }) async {
    final response = await _dio.post('/taqe/grupos/$grupoId/trabajos', data: {
      'nombre': nombre,
      'destino': destino,
      'modo': modo,
      if (enlace != null && enlace.isNotEmpty) 'enlace': enlace,
    });
    return response.data['message'] as String? ?? 'Trabajo agregado correctamente.';
  }

  Future<String> entregar({required int grupoId}) async {
    final response = await _dio.post('/taqe/grupos/$grupoId/entregar');
    return response.data['message'] as String? ?? 'Trabajo final entregado.';
  }

  Future<String> reabrir({required int grupoId}) async {
    final response = await _dio.post('/taqe/grupos/$grupoId/reabrir');
    return response.data['message'] as String? ?? 'Entrega reabierta.';
  }

  Future<String> agregarIntegrantes({
    required int grupoId,
    required List<int> integrantes,
  }) async {
    final response = await _dio.post('/taqe/grupos/$grupoId/integrantes', data: {
      'integrantes': integrantes,
    });
    return response.data['message'] as String? ?? 'Integrantes invitados con éxito.';
  }

  Future<String> retirarIntegrante({
    required int grupoId,
    required int integranteId,
  }) async {
    final response = await _dio.delete('/taqe/grupos/$grupoId/integrantes/$integranteId');
    return response.data['message'] as String? ?? 'Integrante retirado.';
  }

  Future<String> salirDelGrupo({required int grupoId}) async {
    final response = await _dio.post('/taqe/grupos/$grupoId/salir');
    return response.data['message'] as String? ?? 'Has salido del grupo.';
  }

  Future<String> moverTrabajo({required int trabajoId}) async {
    final response = await _dio.post('/taqe/trabajos/$trabajoId/mover');
    return response.data['message'] as String? ?? 'Trabajo movido.';
  }

  Future<String> eliminarTrabajo({required int trabajoId}) async {
    final response = await _dio.delete('/taqe/trabajos/$trabajoId');
    return response.data['message'] as String? ?? 'Elemento eliminado.';
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
