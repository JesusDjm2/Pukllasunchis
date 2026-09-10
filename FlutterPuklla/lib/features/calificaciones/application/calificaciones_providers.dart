import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/calificacion.dart';
import '../data/calificaciones_repository.dart';
import '../data/matricula.dart';

final matriculasProvider = FutureProvider<List<Matricula>>((ref) {
  return ref.watch(calificacionesRepositoryProvider).fetchMatriculas();
});

final calificacionesProvider = FutureProvider<CalificacionesResponse>((ref) {
  return ref.watch(calificacionesRepositoryProvider).fetchCalificaciones();
});
