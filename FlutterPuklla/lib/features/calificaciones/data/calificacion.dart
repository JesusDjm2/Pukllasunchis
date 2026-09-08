/// Espejo de `CalificacionResource` (`GET /api/v1/calificaciones`). Todos los
/// campos de valoración/calificación son `varchar(255)` nullable en la BD
/// real (tabla `calificacions` — sin migración, ver patrón de schema drift
/// del proyecto), no numéricos.
class Calificacion {
  const Calificacion({
    required this.id,
    required this.cursoId,
    required this.cursoNombre,
    required this.valoracion1,
    required this.valoracion2,
    required this.valoracion3,
    required this.valoracionCurso,
    required this.calificacionCurso,
    required this.calificacionSistema,
  });

  factory Calificacion.fromJson(Map<String, dynamic> json) {
    return Calificacion(
      id: json['id'] as int,
      cursoId: json['curso_id'] as int,
      cursoNombre: json['curso_nombre'] as String?,
      valoracion1: json['valoracion_1'] as String?,
      valoracion2: json['valoracion_2'] as String?,
      valoracion3: json['valoracion_3'] as String?,
      valoracionCurso: json['valoracion_curso'] as String?,
      calificacionCurso: json['calificacion_curso'] as String?,
      calificacionSistema: json['calificacion_sistema'] as String?,
    );
  }

  final int id;
  final int cursoId;
  final String? cursoNombre;
  final String? valoracion1;
  final String? valoracion2;
  final String? valoracion3;
  final String? valoracionCurso;
  final String? calificacionCurso;
  final String? calificacionSistema;
}
