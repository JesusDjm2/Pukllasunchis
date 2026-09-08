import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/curso_especial.dart';
import '../data/curso_especial_detalle.dart';
import '../data/curso_especial_progreso.dart';
import '../data/curso_especial_repository.dart';

final cursosEspecialesProvider = FutureProvider<List<CursoEspecial>>((ref) {
  return ref.watch(cursoEspecialRepositoryProvider).fetchCatalogo();
});

final cursoEspecialDetalleProvider =
    FutureProvider.family<CursoEspecialDetalle, int>((ref, cursoId) {
  return ref.watch(cursoEspecialRepositoryProvider).fetchDetalle(cursoId);
});

final cursoEspecialProgresoProvider =
    FutureProvider.family<CursoEspecialProgreso, int>((ref, cursoId) {
  return ref.watch(cursoEspecialRepositoryProvider).fetchProgreso(cursoId);
});
