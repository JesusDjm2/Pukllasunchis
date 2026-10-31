import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../calificaciones/application/calificaciones_providers.dart';
import '../../home/application/home_providers.dart';
import '../data/alumno_perfil.dart';
import '../data/perfil_repository.dart';

final perfilProvider = FutureProvider<AlumnoPerfil>((ref) {
  return ref.watch(perfilRepositoryProvider).fetchPerfil();
});

/// `true` si ya existe una matrícula del alumno para el periodo activo —
/// misma señal que `$yaMatriculado` en `AlumnoController@index` (web),
/// computada aquí en vez de exponer un endpoint nuevo solo para esto.
final yaMatriculadoProvider = FutureProvider<bool>((ref) async {
  final periodoActual = await ref.watch(periodoActualProvider.future);
  if (periodoActual == null) return false;

  final matriculas = await ref.watch(matriculasProvider.future);
  return matriculas.any((m) => m.periodoActualId == periodoActual.id);
});
