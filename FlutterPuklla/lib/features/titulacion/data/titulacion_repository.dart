import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/network/dio_client.dart';
import 'solicitud_formulario.dart';
import 'titulacion_data.dart';

final titulacionRepositoryProvider = Provider<TitulacionRepository>((ref) {
  return TitulacionRepository(ref.watch(dioProvider));
});

class TitulacionRepository {
  const TitulacionRepository(this._dio);

  final Dio _dio;

  Future<TitulacionData> fetch() async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/alumno/titulacion');
      return TitulacionData.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  /// Lo que necesita el formulario de una solicitud (valores, opciones, si puede enviar).
  Future<SolicitudFormulario> fetchFormulario(TipoSolicitud tipo) async {
    try {
      final response = await _dio.get<Map<String, dynamic>>('/alumno/titulacion/${tipo.ruta}');
      return SolicitudFormulario.fromJson(response.data!['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throwApiException(e);
    }
  }

  /// Envía la solicitud como multipart. Los campos de archivo que admiten
  /// varios (comprobantes) van como `campo[]`.
  Future<SolicitudEnviada> enviar(
    TipoSolicitud tipo, {
    required Map<String, String> campos,
    required Map<String, List<ArchivoElegido>> archivos,
    Set<String> multiples = const {},
  }) async {
    final form = FormData();
    campos.forEach((k, v) => form.fields.add(MapEntry(k, v)));
    archivos.forEach((campo, lista) {
      for (final a in lista) {
        form.files.add(MapEntry(
          multiples.contains(campo) ? '$campo[]' : campo,
          MultipartFile.fromBytes(a.bytes, filename: a.nombre),
        ));
      }
    });

    try {
      final response = await _dio.post<Map<String, dynamic>>('/alumno/titulacion/${tipo.ruta}', data: form);
      return SolicitudEnviada(mensaje: response.data?['message'] as String? ?? 'Tu solicitud fue enviada.');
    } on DioException catch (e) {
      throwApiException(e);
    }
  }
}
