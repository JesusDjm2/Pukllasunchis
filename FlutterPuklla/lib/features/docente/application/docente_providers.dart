import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/docente_alumno.dart';
import '../data/docente_curso.dart';
import '../data/docente_incidencia.dart';
import '../data/docente_repository.dart';
import '../data/docente_resumen.dart';
import '../data/docente_tutor_dashboard.dart';

final docenteResumenProvider = FutureProvider<DocenteResumen>((ref) {
  return ref.watch(docenteRepositoryProvider).fetchResumen();
});

final docenteCursosProvider = FutureProvider<List<DocenteCurso>>((ref) {
  return ref.watch(docenteRepositoryProvider).fetchCursos();
});

final docenteIncidenciasPageIndexProvider = StateProvider<int>((ref) => 1);

final docenteIncidenciasProvider = FutureProvider<DocenteIncidenciasPage>((ref) {
  final page = ref.watch(docenteIncidenciasPageIndexProvider);
  return ref.watch(docenteRepositoryProvider).fetchIncidencias(page: page);
});

final docenteAlumnosProvider = FutureProvider<List<DocenteCursoAlumnos>>((ref) {
  return ref.watch(docenteRepositoryProvider).fetchAlumnos();
});

final docenteTutorDashboardProvider = FutureProvider<DocenteTutorDashboard>((ref) {
  return ref.watch(docenteRepositoryProvider).fetchTutorDashboard();
});
