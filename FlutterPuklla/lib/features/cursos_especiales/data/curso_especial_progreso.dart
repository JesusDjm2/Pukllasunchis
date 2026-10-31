/// Espejo de `GET /api/v1/cursos-especiales/{curso}/progreso`.
class CursoEspecialProgreso {
  const CursoEspecialProgreso({
    required this.cursoId,
    required this.totalLecciones,
    required this.leccionesCompletadas,
    required this.totalEjercicios,
    required this.ejerciciosCompletados,
    required this.porcentaje,
  });

  factory CursoEspecialProgreso.fromJson(Map<String, dynamic> json) {
    return CursoEspecialProgreso(
      cursoId: json['curso_id'] as int,
      totalLecciones: json['total_lecciones'] as int,
      leccionesCompletadas: json['lecciones_completadas'] as int,
      totalEjercicios: json['total_ejercicios'] as int,
      ejerciciosCompletados: json['ejercicios_completados'] as int,
      porcentaje: json['porcentaje'] as int,
    );
  }

  final int cursoId;
  final int totalLecciones;
  final int leccionesCompletadas;
  final int totalEjercicios;
  final int ejerciciosCompletados;
  final int porcentaje;
}
