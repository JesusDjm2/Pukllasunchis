import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/solicitud_formulario.dart';
import '../data/titulacion_data.dart';
import '../data/titulacion_repository.dart';

final titulacionProvider = FutureProvider<TitulacionData>((ref) {
  return ref.watch(titulacionRepositoryProvider).fetch();
});

/// Datos del formulario de una solicitud (se recargan al volver a abrirlo).
final solicitudFormularioProvider = FutureProvider.autoDispose.family<SolicitudFormulario, TipoSolicitud>((ref, tipo) {
  return ref.watch(titulacionRepositoryProvider).fetchFormulario(tipo);
});
