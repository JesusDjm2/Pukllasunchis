import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/notificaciones_repository.dart';
import 'notificaciones_providers.dart';

final notificacionesControllerProvider = Provider<NotificacionesController>((ref) {
  return NotificacionesController(ref);
});

class NotificacionesController {
  NotificacionesController(this._ref);

  final Ref _ref;

  NotificacionesRepository get _repository => _ref.read(notificacionesRepositoryProvider);

  Future<void> marcarLeida(String id) async {
    await _repository.marcarLeida(id);
    _ref.invalidate(notificacionesProvider);
    _ref.invalidate(noLeidasCountProvider);
  }

  Future<void> marcarTodasLeidas() async {
    await _repository.marcarTodasLeidas();
    _ref.invalidate(notificacionesProvider);
    _ref.invalidate(noLeidasCountProvider);
  }
}
