import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/curso_especial_repository.dart';
import 'cursos_especiales_providers.dart';

final cursosEspecialesControllerProvider = Provider<CursosEspecialesController>((ref) {
  return CursosEspecialesController(ref);
});

/// Acciones que mutan estado en el server; cada una invalida los providers
/// de lectura afectados para que la UI se refresque sola.
class CursosEspecialesController {
  CursosEspecialesController(this._ref);

  final Ref _ref;

  CursoEspecialRepository get _repository => _ref.read(cursoEspecialRepositoryProvider);

  Future<void> inscribir(int cursoId) async {
    await _repository.inscribir(cursoId);
    _ref.invalidate(cursosEspecialesProvider);
  }

  Future<void> completarLeccion({required int cursoId, required int leccionId}) async {
    await _repository.completarLeccion(leccionId);
    _ref.invalidate(cursoEspecialDetalleProvider(cursoId));
    _ref.invalidate(cursoEspecialProgresoProvider(cursoId));
  }

  /// Devuelve `(correcta, puntaje)`.
  Future<(bool, int)> responderEjercicio({
    required int cursoId,
    required int ejercicioId,
    required String respuesta,
  }) async {
    final resultado = await _repository.responderEjercicio(ejercicioId, respuesta);
    _ref.invalidate(cursoEspecialDetalleProvider(cursoId));
    _ref.invalidate(cursoEspecialProgresoProvider(cursoId));
    return resultado;
  }
}
