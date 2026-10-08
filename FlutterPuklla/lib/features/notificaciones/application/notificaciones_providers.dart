import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/notificaciones_page.dart';
import '../data/notificaciones_repository.dart';

final notificacionesPageIndexProvider = StateProvider<int>((ref) => 1);

final notificacionesProvider = FutureProvider<NotificacionesPage>((ref) {
  final page = ref.watch(notificacionesPageIndexProvider);
  return ref.watch(notificacionesRepositoryProvider).fetchNotificaciones(page: page);
});

/// Para el badge de la campanita — independiente de la lista paginada para
/// no tener que cargar la página 1 completa solo para mostrar un número.
final noLeidasCountProvider = FutureProvider<int>((ref) {
  return ref.watch(notificacionesRepositoryProvider).fetchNoLeidasCount();
});
