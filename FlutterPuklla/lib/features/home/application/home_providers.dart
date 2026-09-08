import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/curso.dart';
import '../data/curso_repository.dart';
import '../data/periodo_actual.dart';
import '../data/periodo_actual_repository.dart';

final periodoActualProvider = FutureProvider<PeriodoActual?>((ref) {
  return ref.watch(periodoActualRepositoryProvider).fetch();
});

final cursosProvider = FutureProvider<List<Curso>>((ref) {
  return ref.watch(cursoRepositoryProvider).fetchCursos();
});
