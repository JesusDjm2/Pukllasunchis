/// Espejo de `CursoResource` (`GET /api/v1/cursos`). `horas` y `creditos` son
/// `string` en la BD (ver migración `create_cursos_table`), no numéricos.
class Curso {
  const Curso({
    required this.id,
    required this.nombre,
    required this.sumilla,
    required this.cc,
    required this.horas,
    required this.creditos,
    required this.classroom,
  });

  factory Curso.fromJson(Map<String, dynamic> json) {
    return Curso(
      id: json['id'] as int,
      nombre: json['nombre'] as String,
      sumilla: json['sumilla'] as String?,
      cc: json['cc'] as String,
      horas: json['horas'] as String,
      creditos: json['creditos'] as String,
      classroom: json['classroom'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? sumilla;
  final String cc;
  final String horas;
  final String creditos;
  final String? classroom;
}
